<?php
session_start();
include 'BD/conexao.php';

$consulta_usuario = "SELECT * FROM usuario";
$resultado_usuario = $conn->query($consulta_usuario);

if ($resultado_usuario->num_rows == 0) {
    $nome = 'admin';
    $usuario = 'admin';
    $senha = '1234';
    $permissao = '1';

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuario (nome, usuario, senha, nivel_acesso) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $usuario, $senha_hash, $permissao);
    
    if ($stmt->execute()) {
        echo "<script>console.log('Usuário admin criado automaticamente');</script>";
    } else {
        echo "<script>console.error('Erro ao criar usuário admin: " . $conn->error . "');</script>";
    }
    
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, senha, nivel_acesso FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $nome, $senha_hash, $nivel_acesso);
        $stmt->fetch();

        if (password_verify($senha, $senha_hash)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['nivel_acesso'] = $nivel_acesso;
            
            header("Location: ../pagina_principal.php");
            exit;
        } else {
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <link rel="stylesheet" href="./assets/css/login.css">
    <meta charset="UTF-8">
    <title>Login</title>

</head>
<body>
    <img src="imagens/logo.jpg" alt="Logo" style="height: 100px;">
    <h1>Login</h1>

    <form class="form-container" action="" method="post">

        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Logar</button>

    </form>

</body>
</html>

