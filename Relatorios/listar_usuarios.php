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
// CONSULTA DOS USUÁRIOS
// ==============================

// Busca todos os usuários cadastrados
$sql = "SELECT id, nome, usuario, senha, nivel_acesso FROM usuario ORDER BY nome ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Configurações básicas da página -->
    <meta name="viewport" content="width=device-width, initial-scale=0.4">
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>

    <!-- CSS da página de listagem -->
    <link rel="stylesheet" href="../assets/css/listas.css">
</head>

<body>

<h1>Usuários Cadastrados</h1>

<!-- Tabela de usuários -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Usuário</th>
            <th>Permissões</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        <!-- Verifica se existem usuários cadastrados -->
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                // Converte nível de acesso para texto legível
                if ($row['nivel_acesso'] == 1) {
                    $row['nivel_acesso'] = 'Administrador';
                } elseif ($row['nivel_acesso'] == 2) {
                    $row['nivel_acesso'] = 'Manutenção';
                } else {
                    $row['nivel_acesso'] = 'Usuário';
                }
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><?= $row['nivel_acesso'] ?></td>
                    <td>
                        <!-- Ações disponíveis -->
                        <a class="button" href="editar_usuario.php?id=<?= $row['id'] ?>">Editar</a>
                        <a class="button delete"
                           href="excluir_usuario.php?id=<?= $row['id'] ?>"
                           onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                            Excluir
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Mensagem caso não existam registros -->
            <tr>
                <td colspan="5">Nenhum usuário cadastrado.</td>
            </tr>
        <?php endif; ?>

        <!-- Botão de retorno -->
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>
    </tbody>
</table>

</body>
</html>
