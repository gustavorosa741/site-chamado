<?php
include '../BD/conexao.php';

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
    // Busca os dados para preencher o formulário
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
    <meta charset="UTF-8">
    <title>Editar Máquina</title>
    <style>
        /* Reutilize seu estilo ou adicione aqui */
    </style>
</head>
<body>

<h1>Editar Máquina</h1>

<form action="" method="post">
    <label for="nome">Nome da Máquina:</label><br>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome_maquina) ?>" required><br><br>

    <label for="setor">Setor:</label><br>
    <input type="text" id="setor" name="setor" value="<?= htmlspecialchars($setor) ?>" required><br><br>

    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_maquinas.php'">Cancelar</button>
</form>

</body>
</html>