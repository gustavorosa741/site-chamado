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
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            color: #003366;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        h1 {
            margin-top: 40px;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .form-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 350px;
            display: flex;
            flex-direction:column;
            text-align: left;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"],
        select {
            padding: 10px;
            border: 1px solid #253236;
            border-radius: 5px;
            font-size: 14px;
            color: #003366;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"],
        input[type="password"], 
        select, :focus {
            border-color: #3399ff;
            box-shadow: 0 0 5px rgba(51, 153, 255, 0.5);
            outline: none;
        }

        button {
            padding: 12px;
            background-color: #3399ff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #267acc;
        }
    </style>
</head>
<body>

<h1>Editar Usuário</h1>

<form class="form-container" action="" method="post">

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required><br>

    <label for="usuario">Usuario:</label>
    <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario) ?>" required><br>

    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="senha" required><br>

    <label for="confirmar_senha">Confirmar senha:</label>
    <input type="password" id="confirmar_senha" name="confirmar_senha" required><br>

    <label>Nível de acesso</label>
        <select id="nivel_acesso" name="nivel_acesso" required>
            <option value="">Selecione uma opção</option>
            <option value="1">Administrador</option>
            <option value="2">Manutenção</option>
            <option value="3">Usuário</option>
            </select>
    <br>

    <button type="submit">Salvar</button><br>
    <button type="button" onclick="window.location.href='listar_usuarios.php'">Cancelar</button><br>
</form>

</body>
</html>
