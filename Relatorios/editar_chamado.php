<?php

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_maquina'] ?? '';
    $id_maquina = $_POST['id_maquina'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $data_abertura = $_POST['data_abertura']?? '';
    $data_fechamento = $_POST['data_fechamento'] ?? '';
    $problema = $_POST['problema'] ?? '';
    $solucao = $_POST['solucao'] ?? '';

    $stmt = $conn->prepare("UPDATE chamado SET id_maquina = ?, categoria = ?, data_abertura = ?, data_fechamento = ?, problema = ?, solucao = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $id_maquina, $categoria, $data_abertura, $data_fechamento, $problema, $solucao, $id);

    if ($stmt->execute()) {
        header("Location: listar_chamados.php");
        exit;
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
} else {
    // Busca os dados para preencher o formulário
    $stmt = $conn->prepare("SELECT id_maquina, categoria, data_abertura, data_fechamento, problema, solucao FROM chamado WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($id_maquina, $categoria, $data_abertura, $data_fechamento, $problema, $solucao);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <meta charset="UTF-8">
    <title>Editar Chamado</title>
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
        input[type="date"], 
        select{
            padding: 10px;
            border: 1px solid #253236;
            border-radius: 5px;
            font-size: 14px;
            color: #003366;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"],
        input[type="date"], :focus {
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

<h1>Editar Chamado</h1>

<form class="form-container" action="" method="post">
    <label for="id_maquina">ID Máquina:</label>
    <input type="text" id="id_maquina" name="id_maquina" value="<?= htmlspecialchars($id_maquina) ?>" required><br>

    <label for="categoria">Categoria:</label>
    <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($categoria) ?>" required><br>

    <label for="data_abertura">Data_abertura:</label>
    <input type="date" id="data_abertura" name="data_abertura" value="<?= htmlspecialchars($data_abertura) ?>" required><br>

    <label for="data_fechamento">Data_fechamento:</label>
    <input type="date" id="data_fechamento" name="data_fechamento" value="<?= htmlspecialchars($data_fechamento) ?>" required><br>

    <label for="problema">Problema:</label>
    <input type="text" id="problema" name="problema" value="<?= htmlspecialchars($problema) ?>" required><br>

    <label for="solucao">Solução:</label>
    <input type="text" id="solucao" name="solucao" value="<?= htmlspecialchars($solucao) ?>" required><br>

    <button type="submit">Salvar</button><br>
    <button type="button" onclick="window.location.href='listar_chamados.php'">Cancelar</button><br>
</form>

</body>
</html>
