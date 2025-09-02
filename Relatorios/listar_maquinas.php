<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario['nivel_acesso'] > 2) {
    echo "<script>alert('Você não tem permissão para acessar essa página!'); window.location.href='../pagina_principal.php';</script>";    
}

$sql = "SELECT id, nome_maquina, setor FROM maquina ORDER BY nome_maquina ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.6">
    <link rel="stylesheet" href="../assets/css/listas.css">
    <meta charset="UTF-8">
    <title>Lista de Máquinas</title>
    
</head>
<body>

<h1>Máquinas Cadastradas</h1>

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
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome_maquina']) ?></td>
                <td><?= htmlspecialchars($row['setor']) ?></td>
                <td>
                    <a class="button" href="editar_maquina.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete" href="excluir_maquina.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta máquina?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhuma máquina cadastrada.</td></tr>
        <?php endif; ?>
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>
    </tbody>
</table>

</body>
</html>
