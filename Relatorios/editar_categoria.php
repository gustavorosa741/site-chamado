<?php
include '../BD/conexao.php';

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
    // Busca os dados para preencher o formulário
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
    <meta charset="UTF-8">
    <title>Editar Categoria</title>
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

<h1>Editar Categoria</h1>

<form class="form-container" action="" method="post">
    <label for="categoria">Categoria:</label>
    <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($categoria) ?>" required><br>

    <button type="submit">Salvar</button><br>
    <button type="button" onclick="window.location.href='listar_categoria.php'">Cancelar</button><br>
</form>

</body>
</html>
