<?php
include '../BD/conexao.php';

if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome =$_POST['nome'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if ($senha !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!');</script>";
    } else{
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuario SET nome=?, usuario = ?, senha = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $usuario, $senha_hash, $id);

        if ($stmt->execute()) {
            header("Location: listar_usuarios.php");
            exit;
        } else {
            echo "Erro ao atualizar: " . $conn->error;
        }
    }} else {
    // Busca os dados para preencher o formulário
    $stmt = $conn->prepare("SELECT nome, usuario FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nome, $usuario);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
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

        input[type="text"] {
            padding: 10px;
            border: 1px solid #253236;
            border-radius: 5px;
            font-size: 14px;
            color: #003366;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus {
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
    <input type="text" id="senha" name="senha" required><br>

    <label for="confirmar_senha">Confirmar senha:</label>
    <input type="text" id="confirmar_senha" name="confirmar_senha" required><br>

    <button type="submit">Salvar</button><br>
    <button type="button" onclick="window.location.href='listar_usuarios.php'">Cancelar</button><br>
</form>

</body>
</html>
