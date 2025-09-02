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
    $nome = $_POST['nome'] ?? '';
    $setor = $_POST['setor'] ?? '';

    $stmt = $conn->prepare("UPDATE maquina SET nome_maquina = ?, setor = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $setor, $id);

    if ($stmt->execute()) {
        header("Location: listar_maquinas.php");
        exit;
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
} else {

    $stmt = $conn->prepare("SELECT nome_maquina, setor FROM maquina WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nome_maquina, $setor);
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
    <title>Editar Máquina</title>

</head>
<body>

<h1>Editar Máquina</h1>

<form class="form-container" action="" method="post">
    <label for="nome">Nome da Máquina:</label>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome_maquina) ?>" required>

    <label for="setor">Setor:</label>
    <input type="text" id="setor" name="setor" value="<?= htmlspecialchars($setor) ?>" required><br>

    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_maquinas.php'">Cancelar</button><br>
</form>

</body>
</html>
