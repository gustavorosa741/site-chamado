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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $permissao = $_POST['nivel_acesso'];

    $consulta = "SELECT usuario FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($consulta);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();
    
    if ($senha != $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!'); window.location.href='cadastro_usuario.php'</script>";
        exit;

    } else if (strlen($senha) < 8) {
        echo "<script>alert('A senha deve ter no mínimo 8 caracteres!'); window.location.href='cadastro_usuario.php'</script>";
        exit;

    } else if (strlen($nome) < 3) {
        echo "<script>alert('O nome deve ter no mínimo 3 caracteres!'); window.location.href='cadastro_usuario.php'</script>";
        exit;

    } else if ($stmt->num_rows > 0) {
        echo "<script>alert('O usuário já existe!'); window.location.href='cadastro_usuario.php'</script>"; 
        exit;

    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (nome, usuario, senha, nivel_acesso) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $usuario, $senha_hash, $permissao);

        if ($stmt->execute()) {
            echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='../pagina_principal.php';</script>";
        } else {
            echo "Erro ao cadastrar: " . $stmt->error;
        }
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <link rel="stylesheet" href="../assets/css/cadastros.css">
    <meta charset="UTF-8">
    <title>Cadastro de Usuario</title>

</head>
<body>
    <h1>Cadastro de Usuario </h1>
        <form class="form-container" action="" method="post">

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required oninput="formatarEmTempoReal(this)" >

        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <label>Confirmar Senha:</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>

        <label>Nível de acesso</label>
        <select id="nivel_acesso" name="nivel_acesso" required>
            <option value="">Selecione uma opção</option>
            <option value="1">Administrador</option>
            <option value="2">Manutenção</option>
            <option value="3">Usuário</option>
            </select>

        <button type="submit">Cadastrar</button>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </form>
</body>
<script>
    function formatarEmTempoReal(campo) {
            let valor = campo.value.replace();
            valor = valor.toUpperCase();
            campo.value = valor;
            campo.setSelectionRange(valor.length, valor.length);
    }
</script>
</html>