<?php
// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// ==============================
// VERIFICAÇÃO DE PERMISSÃO
// ==============================

// Obtém o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consulta o nível de acesso do usuário
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Bloqueia acesso para usuários sem permissão
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>
            alert('Você não tem permissão para acessar essa página!');
            window.location.href='../pagina_principal.php';
          </script>";
}

// ==============================
// CONSULTA DAS MÁQUINAS
// ==============================

// Busca todas as máquinas cadastradas
$sql = "SELECT id, nome_maquina, setor FROM maquina ORDER BY nome_maquina ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Configurações básicas da página -->
    <meta name="viewport" content="width=device-width, initial-scale=0.6">
    <meta charset="UTF-8">
    <title>Lista de Máquinas</title>

    <!-- CSS da página de listagem -->
    <link rel="stylesheet" href="../assets/css/listas.css">
</head>

<body>

<h1>Máquinas Cadastradas</h1>

<!-- Tabela de máquinas -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome da Máquina</th>
            <th>Setor</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        <!-- Verifica se existem máquinas cadastradas -->
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome_maquina']) ?></td>
                <td><?= htmlspecialchars($row['setor']) ?></td>
                <td>
                    <!-- Ações disponíveis -->
                    <a class="button" href="editar_maquina.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete"
                       href="excluir_maquina.php?id=<?= $row['id'] ?>"
                       onclick="return confirm('Tem certeza que deseja excluir esta máquina?')">
                        Excluir
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Mensagem caso não existam registros -->
            <tr>
                <td colspan="4">Nenhuma máquina cadastrada.</td>
            </tr>
        <?php endif; ?>

        <!-- Botão de retorno -->
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>
    </tbody>
</table>

</body>
</html>
