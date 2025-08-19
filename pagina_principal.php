<?php

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'BD/conexao.php';


$chamados = [
    'Aberto' => [],
    'Em andamento' => [],
    'Espera' => [],
    'Concluido' => []
];

$sql = "SELECT c.*, m.nome_maquina, m.setor, a.categoria, u.nome
        FROM chamado c
        LEFT JOIN maquina m ON c.id_maquina = m.id
        LEFT JOIN categoria_chamado a ON c.categoria = a.id
        LEFT JOIN usuario u ON c.id_funcionario = u.id
        ORDER BY c.data_abertura ";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chamados[$row['progresso']][] = $row;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <meta charset="UTF-8">
    <title>Chamados</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            color: #003366;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #003366;
        }

        .menu-container {
            background-color: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }

        .menu-item {
            flex: 1;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .status-columns {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 20px;   
        }

        .status-column {
            flex: 1;
            min-width: 300px;
            background-color: #f9f9f9;
            border-radius: 5px;
            
        }

        .status-title {
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
            color: white;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }

        .aberto-title {
            background-color: #FF6B6B;
        }

        .andamento-title {
            background-color: #4ECDC4;
        }

        .espera-title {
            background-color: #FFD166;
        }

        .concluido-title {
            background-color: #06D6A0;
        }

        .chamados-container {
            padding: 15px;
            min-height: 200px;
        }

        .chamado-card {
            background-color: white;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }

        .chamado-card.aberto {
            border-left-color: #FF6B6B;
            
        }

        .chamado-card.andamento {
            border-left-color: #4ECDC4;
        }

        .chamado-card.espera {
            border-left-color: #FFD166;
        }

        .chamado-card.concluido {
            border-left-color: #06D6A0;
        }

        .chamado-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .chamado-id {
            color: #666;
        }

        .chamado-data {
            font-size: 0.9em;
            color: #666;
        }

        .chamado-body {
            margin: 10px 0;
        }

        .chamado-info {
            display: flex;
            margin-bottom: 5px;
        }

        .chamado-label {
            font-weight: bold;
            min-width: 100px;
        }

        .chamado-mover-direita {
            float: right;
        }

        .button {
        background-color: #4ECDC4;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        }
        .button:hover {
            background-color: #3aa89f;
        }

        @media (max-width: 1200px) {
            .status-columns {
                flex-wrap: wrap;
            }
            
            .status-column {
                min-width: calc(50% - 15px);
            }
        }

        @media (max-width: 768px) {
            .menu-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .status-column {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️Chamados Manutenção🛠️</h1>

        <div class="menu-container">
            <div class="menu-item">
                <label for="cadastros">Cadastros:</label>
                <select id="cadastros" onchange="navegarCadastro(this.value)">
                    <option value="">Selecione</option>
                    <option value="cadastros/cadastro_chamado.php">Cadastrar Chamado</option>
                    <option value="cadastros/cadastro_maquina.php">Cadastrar Máquina</option>
                    <option value="cadastros/cadastro_usuario.php">Cadastrar Usuário</option>
                    <option value="cadastros/cadastro_categoria.php">Cadastrar Categoria</option>
                </select>
            </div>

            <div class="menu-item">
                <label for="relatorios">Relatórios:</label>
                <select id="relatorios" onchange="navegarCadastro(this.value)">
                    <option value="">Selecione</option>
                    <option value="Relatorios/listar_chamados.php">Chamados</option>
                    <option value="Relatorios/listar_maquinas.php">Máquinas</option>
                    <option value="Relatorios/listar_usuarios.php">Usuários</option>
                    <option value="Relatorios/listar_categoria.php">Categoria</option>
                </select>
            </div>
        </div>

        <!-- Colunas lado a lado -->
        <div class="status-columns">

            <!-- Coluna de Chamados Abertos -->
            <div class="status-column">
                <div class="status-title aberto-title">Abertos</div>
                <div class="chamados-container" id="abertos-container">
                    <?php foreach ($chamados['Aberto'] as $chamado): ?>
                        <div class="chamado-card aberto">
                            <div class="chamado-header">
                                <span class="chamado-id">#ID-<?= str_pad($chamado['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                <span class="chamado-data"><?= date('d/m/Y', strtotime($chamado['data_abertura'])) ?></span>
                            </div>
                            <div class="chamado-body">
                                <div class="chamado-info">
                                    <span class="chamado-label">Máquina:</span>
                                    <span><?= htmlspecialchars($chamado['nome_maquina']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Categoria:</span>
                                    <span><?= htmlspecialchars($chamado['categoria']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Solicitante:</span>
                                    <span><?= htmlspecialchars($chamado['nome']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class = "chamado-label">Problema:</span>
                                    <span><?= htmlspecialchars($chamado['problema'])?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Data Abertura:</span>
                                    <span><?= date('d/m/Y', strtotime($chamado['data_abertura']))?></span>
                                </div>
                                <br>
                                <div class="chamado-mover_direita">
                                    <a class="button" href="comandos_chamados/mover_andamento.php?id=<?= $chamado['id'] ?>">>>></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Coluna de Chamados em Andamento -->
            <div class="status-column">
                <div class="status-title andamento-title">Em Andamento</div>
                <div class="chamados-container" id="andamento-container">
                    <?php foreach ($chamados['Em andamento'] as $chamado): ?>
                        <div class="chamado-card andamento">    
                            <div class="chamado-header">
                                <span class="chamado-id">#ID-<?= str_pad($chamado['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                <span class="chamado-data"><?= date('d/m/Y', strtotime($chamado['data_abertura'])) ?></span>
                            </div>
                            <div class="chamado-body">
                                <div class="chamado-info">
                                    <span class="chamado-label">Máquina:</span>
                                    <span><?= htmlspecialchars($chamado['nome_maquina']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Categoria:</span>
                                    <span><?= htmlspecialchars($chamado['categoria']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Solicitante:</span>
                                    <span><?= htmlspecialchars($chamado['nome']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class = "chamado-label">Problema:</span>
                                    <span><?= htmlspecialchars($chamado['problema'])?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Data Abertura:</span>
                                    <span><?= date('d/m/Y', strtotime($chamado['data_abertura']))?></span>
                                </div>
                                <br>
                                <div class="chamado-mover_direita">
                                    <a class="button" href="comandos_chamados/mover_aberto.php?id=<?= $chamado['id'] ?>"><<<</a>
                                    <a class="button" href="comandos_chamados/mover_espera.php?id=<?= $chamado['id'] ?>">>>></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Coluna de Chamados em Espera -->
            <div class="status-column">
                <div class="status-title espera-title">Aguardando Peças</div>
                <div class="chamados-container" id="espera-container">
                    <?php foreach ($chamados['Espera'] as $chamado): ?>
                        <div class="chamado-card espera">    
                            <div class="chamado-header">
                                <span class="chamado-id">#ID-<?= str_pad($chamado['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                <span class="chamado-data"><?= date('d/m/Y', strtotime($chamado['data_abertura'])) ?></span>
                            </div>
                            <div class="chamado-body">
                                <div class="chamado-info">
                                    <span class="chamado-label">Máquina:</span>
                                    <span><?= htmlspecialchars($chamado['nome_maquina']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Categoria:</span>
                                    <span><?= htmlspecialchars($chamado['categoria']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Solicitante:</span>
                                    <span><?= htmlspecialchars($chamado['nome']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class = "chamado-label">Problema:</span>
                                    <span><?= htmlspecialchars($chamado['problema'])?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Data Abertura:</span>
                                    <span><?= date('d/m/Y', strtotime($chamado['data_abertura']))?></span>
                                </div>
                                <br>
                                <div class="chamado-mover_direita">
                                    <a class="button" href="comandos_chamados/mover_andamento.php?id=<?= $chamado['id'] ?>"><<<</a>
                                    <a class="button" href="comandos_chamados/mover_concluido.php?id=<?= $chamado['id'] ?>">>>></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Coluna de Chamados Concluídos -->
            <div class="status-column">
                <div class="status-title concluido-title">Concluídos</div>
                <div class="chamados-container" id="concluido-container">
                    <?php foreach ($chamados['Concluido'] as $chamado): ?>
                        <div class="chamado-card concluido">    
                            <div class="chamado-header">
                                <span class="chamado-id">#ID-<?= str_pad($chamado['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                <span class="chamado-data"><?= date('d/m/Y', strtotime($chamado['data_abertura'])) ?></span>
                            </div>
                            <div class="chamado-body">
                                <div class="chamado-info">
                                    <span class="chamado-label">Máquina:</span>
                                    <span><?= htmlspecialchars($chamado['nome_maquina']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Categoria:</span>
                                    <span><?= htmlspecialchars($chamado['categoria']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Solicitante:</span>
                                    <span><?= htmlspecialchars($chamado['nome']) ?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class = "chamado-label">Problema:</span>
                                    <span><?= htmlspecialchars($chamado['problema'])?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Data Abertura:</span>
                                    <span><?= date('d/m/Y', strtotime($chamado['data_abertura']))?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class = "chamado-label">Solução:</span>
                                    <span><?= htmlspecialchars($chamado['solucao'])?></span>
                                </div>
                                <div class="chamado-info">
                                    <span class="chamado-label">Data Fechamento:</span>
                                    <span><?= date('d/m/Y', strtotime($chamado['data_fechamento']))?></span>
                                </div>
                                <br>
                                <div class="chamado-mover_direita">
                                    <a class="button" href="comandos_chamados/mover_espera.php?id=<?= $chamado['id'] ?>"><<<</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

    <script>
        function navegarCadastro(url) {
            if (url) {
                window.location.href = url;
            }
        }

        /*// Carrega os chamados via AJAX
        function carregarChamados() {
            fetch('api/chamados.php')
                .then(response => response.json())
                .then(data => {
                    // Limpa os containers
                    document.querySelectorAll('.chamados-container').forEach(container => {
                        container.innerHTML = '';
                    });
                    
                    // Preenche com os dados recebidos
                    preencherChamados(data);
                })
                .catch(error => console.error('Erro:', error));
        }

        // Função para preencher os chamados nos containers
        function preencherChamados(data) {
            data.forEach(chamado => {
                const containerId = `${chamado.progresso.toLowerCase().replace(' ', '-')}-container`;
                const container = document.getElementById(containerId);
                
                if (container) {
                    const card = criarCardChamado(chamado);
                    container.appendChild(card);
                }
            });
        }

        // Função para criar o HTML de um card de chamado
        function criarCardChamado(chamado) {
            const card = document.createElement('div');
            card.className = `chamado-card ${chamado.progresso.toLowerCase().replace(' ', '-')}`;
            
            // ... (código para criar a estrutura do card similar ao PHP)
            
            return card;
        }

        // Carrega os chamados quando a página é carregada
        document.addEventListener('DOMContentLoaded', carregarChamados);*/
        </script>
</body>
</html>