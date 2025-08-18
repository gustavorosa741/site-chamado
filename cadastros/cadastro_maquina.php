<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../BD/conexao.php';

    $nome = $_POST['nome'];
    $setor = $_POST['setor'];

    $sql = "INSERT INTO maquina (nome_maquina, setor) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nome, $setor);

    if ($stmt->execute()) {
        echo "<script>alert('Máquina cadastrada com sucesso!'); window.location.href='../pagina_principal.php';</script>";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Máquina</title>
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

    <h1>Cadastro de Máquina</h1>

    <form class="form-container" action="" method="post">
        <label for="nome">Nome da Máquina:</label>
        <input type="text" id="nome" name="nome"required><br>

        <label for="setor">Setor:</label>
        <input type="text" id="setor" name="setor" required><br>

        <button type="submit">Cadastrar</button><br>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </form>

</body>
</html>
