<?php
// Conexão com o banco de dados
include '../BD/conexao.php';

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $maquina_id = $_POST['maquina'];
    $data = $_POST['data'];
    $problema = $_POST['problema'];

    $sql = "INSERT INTO chamado (id_maquina, data_abertura, problema) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $maquina_id, $data, $problema);

    if ($stmt->execute()) {
        echo "<script>alert('Chamado cadastrado com sucesso!'); window.location.href='../cadastros/cadastro_chamado.php';</script>";
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
}

// Busca todas as máquinas para o dropdown
$maquinas = [];
$sql_maquinas = "SELECT id, nome_maquina FROM maquina";
$result = $conn->query($sql_maquinas);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Chamado</title>
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
            flex-direction: column;
            text-align: left;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="date"],
        select {
            padding: 10px;
            border: 1px solid #253236;
            border-radius: 5px;
            font-size: 14px;
            color: #003366;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 15px;
        }

        input:focus,
        select:focus {
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
            margin-top: 10px;
        }

        button:hover {
            background-color: #267acc;
        }
    </style>
</head>
<body>

    <h1>Cadastro de Chamado</h1>

    <form class="form-container" action="" method="post">
        <label for="maquina">Nome da Máquina:</label>
        <select id="maquina" name="maquina" required>
            <option value="">-- Selecione uma máquina --</option>
            <?php foreach ($maquinas as $maquina): ?>
                <option value="<?= htmlspecialchars($maquina['id']) ?>">
                    <?= htmlspecialchars($maquina['nome_maquina']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data">Data:</label>
        <input type="date" id="data" name="data" required>

        <label for="problema">Problema:</label>
        <input type="text" id="problema" name="problema" required>

        <button type="submit">Cadastrar</button>
        <button type="button" onclick="window.location.href='../index.html'">Voltar</button>
    </form>

</body>
</html>

