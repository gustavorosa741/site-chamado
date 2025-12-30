<?php
// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include 'BD/conexao.php';

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Busca o nível de acesso do usuário
$sql_usuario = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql_usuario);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_usuario = $stmt->get_result();

// Define o nível de acesso do usuário
if ($result_usuario->num_rows > 0) {
    $usuario = $result_usuario->fetch_assoc();
    $nivel_acesso = $usuario['nivel_acesso'];
} else {
    // Define nível padrão caso não encontre o usuário
    $nivel_acesso = 3;
}

// Inicializa os chamados organizados por status
$chamados = [
    'Aberto' => [],
    'Em andamento' => [],
    'Espera' => [],
    'Concluido' => []
];

// Consulta todos os chamados com seus relacionamentos
$sql = "SELECT c.*, m.nome_maquina, m.setor, a.categoria, u.nome
        FROM chamado c
        LEFT JOIN maquina m ON c.id_maquina = m.id
        LEFT JOIN categoria_chamado a ON c.categoria = a.id
        LEFT JOIN usuario u ON c.id_funcionario = u.id
        ORDER BY c.data_abertura";

// Executa a consulta
$result = $conn->query($sql);

// Organiza os chamados de acordo com o status (progresso)
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chamados[$row['progresso']][] = $row;
    }
}

// Encerra a conexão com o banco de dados
$conn->close();
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Configuração de responsividade para dispositivos menores -->
    <meta name="viewport" content="width=device-width, initial-scale=0.7">

    <!-- Importa o CSS principal da página -->
    <link rel="stylesheet" href="./assets/css/principal.css">

    <!-- Define o conjunto de caracteres -->
    <meta charset="UTF-8">

    <!-- Título da aba do navegador -->
    <title>Chamados</title>
</head>

<body>

    <!-- Container principal da página -->
    <div class="container">

        <!-- Logo do sistema -->
        <img class="logo" src="./assets/imagens/logo.jpg" alt="Logo" style="height: 60px;">

        <!-- Botão para abrir/fechar o menu lateral -->
        <button class="button-voltar" onclick="Menu()">&#9776</button>

        <!-- Menu vertical do usuário -->
        <ul class="vertical-menu" id="id-menu-vertical">
            <li><a href="">Alterar senha</a></li>
            <li><a href="logout.php">Sair</a></li>
        </ul>

        <!-- Título principal da página -->
        <h1>🛠️Chamados Manutenção🛠️</h1>

        <!-- Área de menus suspensos -->
        <div class="menu-container">

            <!-- Menu de cadastros -->
            <div class="menu-item">
                <label for="cadastros">Cadastros:</label>
                <select id="cadastros" onchange="navegarCadastro(this.value)">
                    <option value="">Selecione</option>
                    <option value="cadastros/cadastro_chamado.php">Cadastrar Chamado</option>

                    <!-- Opções visíveis apenas para administradores/manutenção -->
                    <?php if ($nivel_acesso <= 2): ?>
                        <option value="cadastros/cadastro_maquina.php">Cadastrar Máquina</option>
                        <option value="cadastros/cadastro_usuario.php">Cadastrar Usuário</option>
                        <option value="cadastros/cadastro_categoria.php">Cadastrar Categoria</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Menu de relatórios -->
            <div class="menu-item">
                <label for="relatorios">Relatórios:</label>
                <select id="relatorios" onchange="navegarCadastro(this.value)">
                    <option value="">Selecione</option>
                    <option value="Relatorios/listar_chamados.php">Chamados</option>

                    <!-- Relatórios restritos por nível de acesso -->
                    <?php if ($nivel_acesso <= 2): ?>
                        <option value="Relatorios/listar_maquinas.php">Máquinas</option>
                        <option value="Relatorios/listar_usuarios.php">Usuários</option>
                        <option value="Relatorios/listar_categoria.php">Categoria</option>
                        <option value="Relatorios/dashboard.php">Dashboard</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Exibição do painel Kanban apenas para usuários autorizados -->
        <?php if ($nivel_acesso <= 2): ?>

            <!-- Container das colunas de status -->
            <div class="status-columns">

                <!-- Coluna de chamados abertos -->
                <div class="status-column">
                    <div class="status-title aberto-title">Abertos</div>
                    <div class="chamados-container" id="abertos-container">

                        <!-- Loop dos chamados abertos -->
                        <?php foreach ($chamados['Aberto'] as $chamado): ?>
                            <div class="chamado-card aberto <?= strtolower(str_replace(' ', '_', $chamado['urgencia'])) ?>">

                                <!-- Cabeçalho do chamado -->
                                <div class="chamado-header">
                                    <span class="chamado-id">
                                        #ID-<?= str_pad($chamado['id'], 3, '0', STR_PAD_LEFT) ?>
                                    </span>
                                    <span class="chamado-data">
                                        <?= date('d/m/Y', strtotime($chamado['data_abertura'])) ?>
                                    </span>
                                </div>

                                <!-- Corpo do chamado -->
                                <div class="chamado-body">

                                    <!-- Informações do chamado -->
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
                                        <span class="chamado-label">Problema:</span>
                                        <span><?= htmlspecialchars($chamado['problema']) ?></span>
                                    </div>

                                    <div class="chamado-info">
                                        <span class="chamado-label">Data Abertura:</span>
                                        <span><?= date('d/m/Y', strtotime($chamado['data_abertura'])) ?></span>
                                    </div>

                                    <div>
                                        <span class="chamado-label">Urgência:</span>
                                        <span><?= htmlspecialchars($chamado['urgencia']) ?></span>
                                    </div>

                                    <!-- Botão para mover o chamado -->
                                    <div class="chamado-mover_direita">
                                        <a class="button"
                                            href="comandos_chamados/mover_andamento.php?id=<?= $chamado['id'] ?>">>>></a>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- As demais colunas (Em andamento, Espera e Concluído) seguem a mesma lógica -->
                <!-- Apenas mudam o status exibido e as ações disponíveis -->

            </div>
        <?php endif; ?>

    </div>

    <!-- Scripts JavaScript -->
    <script>
        // Redireciona para a página selecionada no menu
        function navegarCadastro(url) {
            if (url) {
                window.location.href = url;
            }
        }

        // Abre ou fecha o menu lateral
        function Menu() {
            var menu = document.getElementById("id-menu-vertical");
            menu.style.display = (menu.style.display === "block") ? "none" : "block";
        }

        // Fecha o menu ao clicar fora dele
        document.addEventListener('click', function (event) {
            var menu = document.getElementById('id-menu-vertical');
            var button = document.querySelector('.button-voltar');

            if (menu.style.display === 'block' &&
                !menu.contains(event.target) &&
                event.target !== button) {
                menu.style.display = 'none';
            }
        });

        // Impede o fechamento do menu ao clicar dentro dele
        document.getElementById('id-menu-vertical').addEventListener('click', function (event) {
            event.stopPropagation();
        });
    </script>

</body>

</html>