<?php
// Inicia a sessão para controle de login
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Obtém o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Busca o nível de acesso do usuário
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verifica permissão de acesso (somente níveis 1 e 2)
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>
            alert('Você não tem permissão para acessar essa página!');
            window.location.href='../pagina_principal.php';
          </script>";
}

// Consulta para listar todas as categorias cadastradas
$sql = "SELECT id, categoria FROM categoria_chamado ORDER BY categoria ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Configurações básicas da página -->
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <meta charset="UTF-8">
    <title>Lista de Categorias</title>

    <!-- CSS específico para páginas de listagem -->
    <link rel="stylesheet" href="../assets/css/listas.css">
</head>
<body>

<h1>Categorias Cadastradas</h1>

<!-- Tabela de categorias -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Categoria</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>

        <!-- Verifica se existem categorias cadastradas -->
        <?php if ($result->num_rows > 0): ?>

            <!-- Loop para exibir cada categoria -->
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>

                    <!-- htmlspecialchars evita XSS -->
                    <td><?= htmlspecialchars($row['categoria']) ?></td>

                    <td>
                        <!-- Botão para editar categoria -->
                        <a class="button" href="editar_categoria.php?id=<?= $row['id'] ?>">
                            Editar
                        </a>

                        <!-- Botão para excluir com confirmação -->
                        <a class="button delete"
                           href="excluir_categoria.php?id=<?= $row['id'] ?>"
                           onclick="return confirm('Tem certeza que deseja excluir esta Categoria?')">
                            Excluir
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        <?php else: ?>
            <!-- Caso não exista nenhuma categoria -->
            <tr>
                <td colspan="3">Nenhuma Categoria cadastrada.</td>
            </tr>
        <?php endif; ?>

        <!-- Botão para voltar à página principal -->
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>

    </tbody>
</table>

</body>
</html>
