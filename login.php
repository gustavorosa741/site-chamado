<?php
session_start();
include 'BD/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $sql = "SELECT id, senha FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $senha_hash);
        $stmt->fetch();

        // Verifica senha
        if (password_verify($senha, $senha_hash)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nome'] = $usuario;
            header("Location: ../index.html");
            exit;
        } else {
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        /* mesmo layout do chamado */
    </style>
</head>
<body>
    <h1>Login</h1>
    <form class="form-container" action="" method="post">
        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
        <button type="button" onclick="window.location.href='../index.html'">Voltar</button>
    </form>
</body>
</html>
