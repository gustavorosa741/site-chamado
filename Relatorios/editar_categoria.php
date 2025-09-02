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

if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'] ?? '';

    $stmt = $conn->prepare("UPDATE categoria_chamado SET categoria = ? WHERE id = ?");
    $stmt->bind_param("si", $categoria, $id);

    if ($stmt->execute()) {
        header("Location: listar_categoria.php");
        exit;
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
} else {

    $stmt = $conn->prepare("SELECT categoria FROM categoria_chamado WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($categoria);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <link rel="stylesheet" href="../assets/css/cadastros.css">
    <meta charset="UTF-8">
    <title>Editar Categoria</title>

</head>
<body>

<h1>Editar Categoria</h1>

<form class="form-container" action="" method="post">
    <label for="categoria">Categoria:</label>
    <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($categoria) ?>" required><br>

    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_categoria.php'">Cancelar</button><br>
</form>

</body>
</html>
