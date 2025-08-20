<?php
// dashboard.php
session_start();
include '../BD/conexao.php';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Buscar dados do dashboard
$machineData = [];
$categoryData = [];

// Chamados por máquina
$query = "
    SELECT m.nome_maquina, COUNT(c.id) as total 
    FROM chamado c 
    JOIN maquina m ON c.id_maquina = m.id 
    WHERE c.data_abertura >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY m.id 
    ORDER BY total DESC
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $machineData[] = $row;
}
$stmt->close();

// Chamados por categoria
$query = "
    SELECT cat.categoria, COUNT(c.id) as total 
    FROM chamado c 
    JOIN categoria_chamado cat ON c.categoria = cat.id 
    WHERE c.data_abertura >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY cat.id 
    ORDER BY total DESC
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categoryData[] = $row;
}
$stmt->close();

// Buscar estatísticas totais
$totalChamados = 0;
$chamadosAbertos = 0;
$maquinaMaisChamados = '';
$categoriaPrincipal = '';

$query = "SELECT COUNT(*) as total FROM chamado WHERE data_abertura >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = $conn->query($query);
if ($row = $result->fetch_assoc()) {
    $totalChamados = $row['total'];
}

$query = "SELECT COUNT(*) as abertos FROM chamado WHERE data_fechamento IS NULL";
$result = $conn->query($query);
if ($row = $result->fetch_assoc()) {
    $chamadosAbertos = $row['abertos'];
}

if (!empty($machineData)) {
    $maquinaMaisChamados = $machineData[0]['nome_maquina'];
}

if (!empty($categoryData)) {
    $categoriaPrincipal = $categoryData[0]['categoria'];
}

// Converter dados para JSON para uso no JavaScript
$machineDataJson = json_encode($machineData);
$categoryDataJson = json_encode($categoryData);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Chamados</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --text-color: #333;
            --card-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--text-color);
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        select, button {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            font-size: 14px;
        }
        
        button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 10px 0;
        }
        
        .stat-label {
            color: #777;
            font-size: 14px;
        }
        
        .icon {
            font-size: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .controls {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><span class="icon">📊</span> Dashboard de Chamados</h1>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </header>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total de Chamados</div>
            <div class="stat-value"><?php echo $totalChamados; ?></div>
            <div class="stat-label">30 dias</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Chamados Abertos</div>
            <div class="stat-value"><?php echo $chamadosAbertos; ?></div>
            <div class="stat-label">Em andamento</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Máquina com Mais Chamados</div>
            <div class="stat-value"><?php echo htmlspecialchars($maquinaMaisChamados); ?></div>
            <div class="stat-label">
                <?php 
                if (!empty($machineData)) {
                    echo $machineData[0]['total'] . ' chamados';
                }
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Categoria Principal</div>
            <div class="stat-value"><?php echo htmlspecialchars($categoriaPrincipal); ?></div>
            <div class="stat-label">
                <?php 
                if (!empty($categoryData)) {
                    $percent = round(($categoryData[0]['total'] / $totalChamados) * 100);
                    echo $percent . '% dos chamados';
                }
                ?>
            </div>
        </div>
    </div>
    
    <div class="controls">
        <select id="time-range">
            <option value="7">Últimos 7 dias</option>
            <option value="30" selected>Últimos 30 dias</option>
            <option value="90">Últimos 3 meses</option>
            <option value="365">Último ano</option>
        </select>
        
        <select id="setor-filter">
            <option value="">Todos os setores</option>
            <option value="producao">Produção</option>
            <option value="embalagem">Embalagem</option>
            <option value="manutencao">Manutenção</option>
        </select>
        
        <button id="btn-apply">Aplicar Filtros</button>
        <button id="btn-export">Exportar Relatório</button>
    </div>
    
    <div class="dashboard">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">🔧</span> Chamados por Máquina</h2>
            </div>
            <div class="chart-container">
                <canvas id="machineChart"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">📁</span> Chamados por Categoria</h2>
            </div>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">📈</span> Evolução de Chamados</h2>
            </div>
            <div class="chart-container">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">⏱️</span> Tempo Médio de Resolução</h2>
            </div>
            <div class="chart-container">
                <canvas id="resolutionChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Dados do PHP convertidos para JavaScript
        const machineDataFromPHP = <?php echo $machineDataJson; ?>;
        const categoryDataFromPHP = <?php echo $categoryDataJson; ?>;

        // Preparar dados para os gráficos
        const machineData = {
            labels: machineDataFromPHP.map(item => item.nome_maquina),
            datasets: [{
                label: 'Número de Chamados',
                data: machineDataFromPHP.map(item => item.total),
                backgroundColor: [
                    'rgba(231, 76, 60, 0.8)',
                    'rgba(241, 196, 15, 0.8)',
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(155, 89, 182, 0.8)',
                    'rgba(230, 126, 34, 0.8)',
                    'rgba(26, 188, 156, 0.8)'
                ],
                borderColor: [
                    'rgba(231, 76, 60, 1)',
                    'rgba(241, 196, 15, 1)',
                    'rgba(52, 152, 219, 1)',
                    'rgba(46, 204, 113, 1)',
                    'rgba(155, 89, 182, 1)',
                    'rgba(230, 126, 34, 1)',
                    'rgba(26, 188, 156, 1)'
                ],
                borderWidth: 1
            }]
        };

        const categoryData = {
            labels: categoryDataFromPHP.map(item => item.categoria),
            datasets: [{
                label: 'Chamados por Categoria',
                data: categoryDataFromPHP.map(item => item.total),
                backgroundColor: [
                    'rgba(52, 152, 219, 0.7)',
                    'rgba(46, 204, 113, 0.7)',
                    'rgba(155, 89, 182, 0.7)',
                    'rgba(241, 196, 15, 0.7)',
                    'rgba(230, 126, 34, 0.7)',
                    'rgba(231, 76, 60, 0.7)',
                    'rgba(26, 188, 156, 0.7)'
                ],
                borderColor: [
                    'rgba(52, 152, 219, 1)',
                    'rgba(46, 204, 113, 1)',
                    'rgba(155, 89, 182, 1)',
                    'rgba(241, 196, 15, 1)',
                    'rgba(230, 126, 34, 1)',
                    'rgba(231, 76, 60, 1)',
                    'rgba(26, 188, 156, 1)'
                ],
                borderWidth: 1
            }]
        };

        // Dados simulados (seriam buscados do banco em uma versão completa)
        const timelineData = {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                label: 'Chamados Abertos',
                data: [12, 19, 15, 17, 14, 16, 18, 17, 20, 18, 22, 24],
                borderColor: 'rgba(231, 76, 60, 1)',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Chamados Resolvidos',
                data: [10, 16, 13, 15, 14, 15, 16, 17, 18, 19, 20, 22],
                borderColor: 'rgba(46, 204, 113, 1)',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        const resolutionData = {
            labels: machineDataFromPHP.map(item => item.nome_maquina).slice(0, 5),
            datasets: [{
                label: 'Tempo Médio (dias)',
                data: [4.5, 3.2, 5.7, 2.8, 6.2],
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            }]
        };

        // Inicializar os gráficos
        let machineChart, categoryChart, timelineChart, resolutionChart;

        window.onload = function() {
            const machineCtx = document.getElementById('machineChart').getContext('2d');
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const timelineCtx = document.getElementById('timelineChart').getContext('2d');
            const resolutionCtx = document.getElementById('resolutionChart').getContext('2d');
            
            machineChart = new Chart(machineCtx, {
                type: 'bar',
                data: machineData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Chamados por Máquina (Últimos 30 dias)'
                        }
                    }
                }
            });
            
            categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: categoryData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribuição por Categoria'
                        }
                    }
                }
            });
            
            timelineChart = new Chart(timelineCtx, {
                type: 'line',
                data: timelineData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Evolução Mensal de Chamados'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            resolutionChart = new Chart(resolutionCtx, {
                type: 'bar',
                data: resolutionData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Tempo Médio de Resolução (dias)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Dias'
                            }
                        }
                    }
                }
            });
        };

        // Simular filtros
        document.getElementById('btn-apply').addEventListener('click', function() {
            alert('Filtros aplicados! (Esta funcionalidade conectaria ao backend em uma implementação real)');
        });

        document.getElementById('btn-export').addEventListener('click', function() {
            alert('Relatório exportado! (Esta funcionalidade geraria um PDF/Excel em uma implementação real)');
        });
    </script>
</body>
</html>