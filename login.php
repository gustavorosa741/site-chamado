<?php
session_start(); // Inicia a sessão para permitir login e armazenamento de dados do usuário
include 'BD/conexao.php'; // Inclui o arquivo de conexão com o banco de dados

// Consulta para buscar todos os usuários cadastrados
$consulta_usuario = "SELECT * FROM usuario";
$resultado_usuario = $conn->query($consulta_usuario);

// Caso não existam usuários cadastrados, cria automaticamente o usuário padrão "admin"
if ($resultado_usuario->num_rows == 0) {
    $nome = 'admin';
    $usuario = 'admin';
    $senha = '1234';
    $permissao = '1';

    // Criptografa a senha padrão
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Prepara e executa o comando para inserir o usuário admin
    $sql = "INSERT INTO usuario (nome, usuario, senha, nivel_acesso) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $usuario, $senha_hash, $permissao);

    // Verifica se o cadastro do admin foi bem-sucedido
    if ($stmt->execute()) {
        echo "<script>console.log('Usuário admin criado automaticamente');</script>";
    } else {
        echo "<script>console.error('Erro ao criar usuário admin: " . $conn->error . "');</script>";
    }

    $stmt->close();
}

// Verifica se o formulário foi enviado via método POST (tentativa de login)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario']; // Recebe o usuário digitado
    $senha = $_POST['senha'];     // Recebe a senha digitada

    // Busca o usuário no banco pelo campo "usuario"
    $sql = "SELECT id, nome, senha, nivel_acesso FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    // Verifica se encontrou exatamente um usuário com esse nome
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $nome, $senha_hash, $nivel_acesso);
        $stmt->fetch();

        // Verifica se a senha digitada confere com a senha armazenada (hash)
        if (password_verify($senha, $senha_hash)) {

            // Armazena dados do usuário na sessão
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['nivel_acesso'] = $nivel_acesso;

            // Redireciona para a página principal se o login for válido
            header("Location: ../pagina_principal.php");
            exit;

        } else {
            // Usuário encontrado, mas senha incorreta
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        // Nenhum usuário com esse nome encontrado
        echo "<script>alert('Usuário não encontrado!');</script>";
    }

    $stmt->close();
}

$conn->close(); // Encerra a conexão com o banco de dados
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.7"> <!-- Ajuste responsivo -->
    <link rel="stylesheet" href="./assets/css/login.css"> <!-- Arquivo de estilo -->
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>
    <img src="imagens/logo.jpg" alt="Logo" style="height: 100px;"> <!-- Logo da página -->
    <h1>Login</h1>

    <!-- Formulário de login -->
    <form class="form-container" action="" method="post">

        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Logar</button>

    </form>

</body>
</html>