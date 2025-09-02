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
    $nome =$_POST['nome'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $permissao = $_POST['nivel_acesso'] ?? '';

    if ($senha !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!');</script>";

    } else if (strlen($senha) < 8) {
        echo "<script>alert('A senha deve ter no mínimo 8 caracteres!');</script>";
    

    } else if (strlen($usuario) < 3) {
        echo "<script>alert('O nome deve ter no mínimo 3 caracteres!');</script>";
        

    } else{
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuario SET nome=?, usuario = ?, senha = ?, nivel_acesso = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nome, $usuario, $senha_hash, $permissao,     $id);

        if ($stmt->execute()) {
            header("Location: listar_usuarios.php");
            exit;
        } else {
            echo "Erro ao atualizar: " . $conn->error;
        }
    }} else {
        
    $stmt = $conn->prepare("SELECT nome, usuario, nivel_acesso FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nome, $usuario, $permissao);
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
    <title>Editar Usuário</title>
    
</head>
<body>

<h1>Editar Usuário</h1>

<form class="form-container" action="" method="post">

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

    <label for="usuario">Usuario:</label>
    <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario) ?>" required>

    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="senha" required>

    <label for="confirmar_senha">Confirmar senha:</label>
    <input type="password" id="confirmar_senha" name="confirmar_senha" required>

    <label>Nível de acesso</label>
        <select id="nivel_acesso" name="nivel_acesso" required>
            <option value="">Selecione uma opção</option>
            <option value="1">Administrador</option>
            <option value="2">Manutenção</option>
            <option value="3">Usuário</option>
            </select>
    <br>

    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_usuarios.php'">Cancelar</button><br>
</form>

</body>
</html>
