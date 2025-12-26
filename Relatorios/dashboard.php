<?php
// Inicia sessão e conexão com o banco
session_start();
include '../BD/conexao.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Busca o nível de acesso do usuário logado
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Bloqueia acesso para usuários sem permissão
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>alert('Você não tem permissão para acessar essa página!'); window.location.href='../pagina_principal.php';</script>";
}

// Define filtros de período e setor (com valores padrão)
$periodo = isset($_GET['periodo']) ? intval($_GET['periodo']) : 30;
$setor = isset($_GET['setor']) ? $_GET['setor'] : '';

// Valida períodos permitidos
$periodos_permitidos = [7, 30, 90, 365];
if (!in_array($periodo, $periodos_permitidos)) {
    $periodo = 30;
}

// Define condição base para consultas por data
$condicoes = "c.data_abertura >= DATE_SUB(NOW(), INTERVAL $periodo DAY)";
$where_condicoes = "WHERE " . $condicoes;
if (!empty($setor)) {
    $setor_escape = $conn->real_escape_string($setor);
    $where_condicoes .= " AND m.setor = '$setor_escape'";
}

// Inicializa arrays de dados dos gráficos
$machineData = [];
$categoryData = [];

// Consulta chamados por máquina
$query = "
    SELECT m.nome_maquina, COUNT(c.id) as total 
    FROM chamado c 
    JOIN maquina m ON c.id_maquina = m.id 
    WHERE $condicoes" . (!empty($setor) ? " AND m.setor = '" . $conn->real_escape_string($setor) . "'" : "") . "
    GROUP BY m.id, m.nome_maquina
    ORDER BY total DESC
";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $machineData[] = $row;
    }
    $stmt->close();
}

// Consulta chamados por categoria
$query = "
    SELECT cat.categoria, COUNT(c.id) as total 
    FROM chamado c 
    JOIN categoria_chamado cat ON c.categoria = cat.id 
    JOIN maquina m ON c.id_maquina = m.id 
    WHERE $condicoes" . (!empty($setor) ? " AND m.setor = '" . $conn->real_escape_string($setor) . "'" : "") . "
    GROUP BY cat.id, cat.categoria
    ORDER BY total DESC
";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categoryData[] = $row;
    }
    $stmt->close();
}

// Consulta evolução temporal de chamados
$timelineData = [];
$query = "
    SELECT 
        DATE(c.data_abertura) as data, 
        COUNT(*) as total_abertos,
        SUM(CASE WHEN c.data_fechamento IS NOT NULL THEN 1 ELSE 0 END) as total_fechados
    FROM chamado c
    JOIN maquina m ON c.id_maquina = m.id
    WHERE $condicoes" . (!empty($setor) ? " AND m.setor = '" . $conn->real_escape_string($setor) . "'" : "") . "
    GROUP BY DATE(c.data_abertura)
    ORDER BY data
";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $timelineData[] = $row;
}

// Consulta tempo médio de resolução
$resolutionData = [];
$query = "
    SELECT 
        m.nome_maquina,
        AVG(TIMESTAMPDIFF(DAY, c.data_abertura, c.data_fechamento)) as tempo_medio_dias
    FROM chamado c
    JOIN maquina m ON c.id_maquina = m.id
    WHERE c.data_fechamento IS NOT NULL
    AND c.data_abertura >= DATE_SUB(NOW(), INTERVAL $periodo DAY)
    " . (!empty($setor) ? " AND m.setor = '" . $conn->real_escape_string($setor) . "'" : "") . "
    GROUP BY m.id, m.nome_maquina
    ORDER BY tempo_medio_dias DESC
    LIMIT 5
";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $resolutionData[] = $row;
}

// Estatísticas gerais do dashboard
$totalChamados = 0;
$chamadosAbertos = 0;
$maquinaMaisChamados = '';
$categoriaPrincipal = '';

// Total de chamados
$query = "SELECT COUNT(*) as total FROM chamado c JOIN maquina m ON c.id_maquina = m.id WHERE $condicoes" . (!empty($setor) ? " AND m.setor = '" . $conn->real_escape_string($setor) . "'" : "");
$result = $conn->query($query);
$totalChamados = $result->fetch_assoc()['total'] ?? 0;

// Total de chamados abertos
$query = "SELECT COUNT(*) as abertos FROM chamado c JOIN maquina m ON c.id_maquina = m.id WHERE c.data_fechamento IS NULL AND $condicoes" . (!empty($setor) ? " AND m.setor = '" . $conn->real_escape_string($setor) . "'" : "");
$result = $conn->query($query);
$chamadosAbertos = $result->fetch_assoc()['abertos'] ?? 0;

// Define destaques principais
$maquinaMaisChamados = $machineData[0]['nome_maquina'] ?? '';
$categoriaPrincipal = $categoryData[0]['categoria'] ?? '';

// Busca setores disponíveis para filtro
$setores = [];
$query = "SELECT DISTINCT setor FROM maquina WHERE setor IS NOT NULL AND setor != '' ORDER BY setor";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $setores[] = $row['setor'];
}

// Exportação do relatório em CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_chamados.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Relatório de Chamados - Período: ' . $periodo . ' dias'], ';');
    fputcsv($output, []);
    fputcsv($output, ['Máquina', 'Total de Chamados'], ';');

    foreach ($machineData as $maquina) {
        fputcsv($output, [$maquina['nome_maquina'], $maquina['total']], ';');
    }

    fputcsv($output, []);
    fputcsv($output, ['Categoria', 'Total de Chamados'], ';');

    foreach ($categoryData as $categoria) {
        fputcsv($output, [$categoria['categoria'], $categoria['total']], ';');
    }

    fclose($output);
    exit;
}

// Converte dados para JSON (uso no JavaScript)
$machineDataJson = json_encode($machineData);
$categoryDataJson = json_encode($categoryData);
$timelineDataJson = json_encode($timelineData);
$resolutionDataJson = json_encode($resolutionData);
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Metadados e recursos do dashboard -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>Dashboard de Chamados</title>

    <!-- Biblioteca Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Cabeçalho do dashboard -->
    <header>
        <h1><span class="icon">📊</span> Dashboard de Chamados</h1>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </header>

    <!-- Cards de estatísticas gerais -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total de Chamados</div>
            <div class="stat-value"><?php echo $totalChamados; ?></div>
            <div class="stat-label"><?php echo $periodo; ?> dias</div>
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
                if (!empty($categoryData) && $totalChamados > 0) {
                    $percent = round(($categoryData[0]['total'] / $totalChamados) * 100);
                    echo $percent . '% dos chamados';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Formulário de filtros do dashboard -->
    <form method="GET" action="">
        <div class="controls">
            <!-- Filtro de período -->
            <select id="time-range" name="periodo">
                <option value="7" <?php echo $periodo == 7 ? 'selected' : ''; ?>>Últimos 7 dias</option>
                <option value="30" <?php echo $periodo == 30 ? 'selected' : ''; ?>>Últimos 30 dias</option>
                <option value="90" <?php echo $periodo == 90 ? 'selected' : ''; ?>>Últimos 3 meses</option>
                <option value="365" <?php echo $periodo == 365 ? 'selected' : ''; ?>>Último ano</option>
            </select>

            <!-- Filtro de setor -->
            <select id="setor-filter" name="setor">
                <option value="">Todos os setores</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $setor == $s ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Ações -->
            <button type="submit" id="btn-apply">Aplicar Filtros</button>
            <button type="button" id="btn-export" onclick="exportRelatorio()">Exportar Relatório</button>
        </div>
    </form>

    <!-- Área principal dos gráficos -->
    <div class="dashboard">
        <!-- Gráfico: Chamados por Máquina -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">🔧</span> Chamados por Máquina</h2>
            </div>
            <div class="chart-container">
                <canvas id="machineChart"></canvas>
            </div>
        </div>

        <!-- Gráfico: Chamados por Categoria -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">📁</span> Chamados por Categoria</h2>
            </div>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Gráfico: Evolução temporal -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><span class="icon">📈</span> Evolução de Chamados</h2>
            </div>
            <div class="chart-container">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Gráfico: Tempo médio de resolução -->
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
        /* ===========================
           Dados recebidos do PHP
        ============================ */
        const machineDataFromPHP = <?php echo $machineDataJson; ?>;
        const categoryDataFromPHP = <?php echo $categoryDataJson; ?>;
        const timelineDataFromPHP = <?php echo $timelineDataJson; ?>;
        const resolutionDataFromPHP = <?php echo $resolutionDataJson; ?>;

        /* ===========================
           Configuração dos datasets
        ============================ */
        const machineData = {
            labels: machineDataFromPHP.map(item => item.nome_maquina),
            datasets: [{
                label: 'Número de Chamados',
                data: machineDataFromPHP.map(item => item.total),
                borderWidth: 1
            }]
        };

        const categoryData = {
            labels: categoryDataFromPHP.map(item => item.categoria),
            datasets: [{
                label: 'Chamados por Categoria',
                data: categoryDataFromPHP.map(item => item.total),
                borderWidth: 1
            }]
        };

        const timelineLabels = timelineDataFromPHP.map(item =>
            new Date(item.data).toLocaleDateString('pt-BR')
        );

        const timelineData = {
            labels: timelineLabels,
            datasets: [
                {
                    label: 'Chamados Abertos',
                    data: timelineDataFromPHP.map(item => item.total_abertos),
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Chamados Resolvidos',
                    data: timelineDataFromPHP.map(item => item.total_fechados),
                    tension: 0.4,
                    fill: true
                }
            ]
        };

        const resolutionData = {
            labels: resolutionDataFromPHP.map(item => item.nome_maquina),
            datasets: [{
                label: 'Tempo Médio (dias)',
                data: resolutionDataFromPHP.map(item =>
                    parseFloat(item.tempo_medio_dias).toFixed(1)
                ),
                borderWidth: 1
            }]
        };

        /* ===========================
           Inicialização dos gráficos
        ============================ */
        window.onload = function () {
            new Chart(document.getElementById('machineChart'), { type: 'bar', data: machineData });
            new Chart(document.getElementById('categoryChart'), { type: 'doughnut', data: categoryData });
            new Chart(document.getElementById('timelineChart'), { type: 'line', data: timelineData });
            new Chart(document.getElementById('resolutionChart'), { type: 'bar', data: resolutionData });
        };

        /* ===========================
           Exportação de relatório
        ============================ */
        function exportRelatorio() {
            const periodo = document.getElementById('time-range').value;
            const setor = document.getElementById('setor-filter').value;

            let url = './exportar_relatorio.php?export=excel&periodo=' + periodo;
            if (setor) {
                url += '&setor=' + encodeURIComponent(setor);
            }
            window.location.href = url;
        }
    </script>
</body>

</html>