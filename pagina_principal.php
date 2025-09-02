<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'BD/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$sql_usuario = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql_usuario);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_usuario = $stmt->get_result();

if ($result_usuario->num_rows > 0) {
    $usuario = $result_usuario->fetch_assoc();
    $nivel_acesso = $usuario['nivel_acesso'];
} else {
    $nivel_acesso = 3;
}

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
    <link rel="stylesheet" href="./assets/css/principal.css">
    <meta charset="UTF-8">
    <title>Chamados</title>
    
</head>
<body>
    <div class="container">
        <img class="logo" src="./assets/imagens/logo.jpg" alt="Logo" style="height: 60px;">
        <button class="button-voltar" onclick="window.location.href='../logout.php'">Sair</button>
        <h1>🛠️Chamados Manutenção🛠️</h1>
        
        <div class="menu-container">
            <div class="menu-item">
                <label for="cadastros">Cadastros:</label>
                <select id="cadastros" onchange="navegarCadastro(this.value)">
                    <option value="">Selecione</option>
                    <option value="cadastros/cadastro_chamado.php">Cadastrar Chamado</option>
                    <?php if ($nivel_acesso <= 2): ?>
                        <option value="cadastros/cadastro_maquina.php">Cadastrar Máquina</option>
                        <option value="cadastros/cadastro_usuario.php">Cadastrar Usuário</option>
                        <option value="cadastros/cadastro_categoria.php">Cadastrar Categoria</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="menu-item">
                <label for="relatorios">Relatórios:</label>
                <select id="relatorios" onchange="navegarCadastro(this.value)">
                    <option value="">Selecione</option>
                    <option value="Relatorios/listar_chamados.php">Chamados</option>
                    <?php if ($nivel_acesso <= 2): ?>
                        <option value="Relatorios/listar_maquinas.php">Máquinas</option>
                        <option value="Relatorios/listar_usuarios.php">Usuários</option>
                        <option value="Relatorios/listar_categoria.php">Categoria</option>
                        <option value="Relatorios/dashboard.php">Dashboard</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <?php if ($nivel_acesso <= 2): ?>
                        
        <div class="status-columns">

            <div class="status-column">
                <div class="status-title aberto-title">Abertos</div>
                <div class="chamados-container" id="abertos-container">
                    <?php foreach ($chamados['Aberto'] as $chamado): ?>
                        <div class="chamado-card aberto <?= strtolower(str_replace(' ', '_', $chamado['urgencia'])) ?>">
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
                                <div>
                                    <span class="chamado-label">Urgência:</span>
                                    <span><?= htmlspecialchars($chamado['urgencia'])?></span>
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

            <div class="status-column">
                <div class="status-title andamento-title">Em Andamento</div>
                <div class="chamados-container" id="andamento-container">
                    <?php foreach ($chamados['Em andamento'] as $chamado): ?>
                        <div class="chamado-card andamento <?= strtolower(str_replace(' ', '_', $chamado['urgencia'])) ?>">    
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
                                <div>
                                    <span class="chamado-label">Urgência:</span>
                                    <span><?= htmlspecialchars($chamado['urgencia'])?></span>
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

            <div class="status-column">
                <div class="status-title espera-title">Aguardando Peças</div>
                <div class="chamados-container" id="espera-container">
                    <?php foreach ($chamados['Espera'] as $chamado): ?>
                        <div class="chamado-card espera <?= strtolower(str_replace(' ', '_', $chamado['urgencia'])) ?>">   
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
                                <div>
                                    <span class="chamado-label">Urgência:</span>
                                    <span><?= htmlspecialchars($chamado['urgencia'])?></span>
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
                                <div>
                                    <span class="chamado-label">Urgência:</span>
                                    <span><?= htmlspecialchars($chamado['urgencia'])?></span>
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
            <?php endif; ?>

    <script>
        function navegarCadastro(url) {
            if (url) {
                window.location.href = url;
            }
        }

        </script>
</body>
</html>