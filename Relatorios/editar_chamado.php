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
    $id = $_POST['id_maquina'] ?? '';
    $id_maquina = $_POST['id_maquina'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $data_abertura = $_POST['data_abertura']?? '';
    $data_fechamento = $_POST['data_fechamento'] ?? '';
    $problema = $_POST['problema'] ?? '';
    $solucao = $_POST['solucao'] ?? '';
    $urgencia = $_POST['urgencia'] ?? '';


    $stmt = $conn->prepare("UPDATE chamado SET id_maquina = ?, categoria = ?, data_abertura = ?, data_fechamento = ?, problema = ?, solucao = ?, urgencia = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $id_maquina, $categoria, $data_abertura, $data_fechamento, $problema, $solucao, $urgencia, $id);

    if ($stmt->execute()) {
        header("Location: listar_chamados.php");
        exit;
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
} else {

    $stmt = $conn->prepare("SELECT id_maquina, categoria, data_abertura, data_fechamento, problema, solucao, urgencia FROM chamado WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($id_maquina, $categoria, $data_abertura, $data_fechamento, $problema, $solucao, $urgencia);
    $stmt->fetch();
    $stmt->close();
}

$maquinas = [];
$sql_maquinas = "SELECT id, nome_maquina FROM maquina";
$result = $conn->query($sql_maquinas);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
}

$categoria_chamado = [];
$sql_categoria = "SELECT id, categoria FROM categoria_chamado";
$result = $conn->query($sql_categoria);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categoria_chamado[] = $row;
        }
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
    <input type="hidden" name="id_maquina" value="<?= $id ?>">
    
    <label for="maquina">Nome da Máquina:</label>
    <select id="maquina" name="id_maquina" required>
        <option value="">-- Selecione uma máquina --</option>
        <?php foreach ($maquinas as $maquina): ?>
            <option value="<?= htmlspecialchars($maquina['id']) ?>" 
                <?= ($maquina['id'] == $id_maquina) ? 'selected' : '' ?>>
                <?= htmlspecialchars($maquina['nome_maquina']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="categoria">Categoria:</label>
    <select id="categoria" name="categoria" required>
        <option value="">-- Selecione uma categoria --</option>
        <?php foreach ($categoria_chamado as $cat): ?>
            <option value="<?= htmlspecialchars($cat['id']) ?>" 
                <?= ($cat['id'] == $categoria) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['categoria']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="data_abertura">Data_abertura:</label>
    <input type="date" id="data_abertura" name="data_abertura" value="<?= htmlspecialchars($data_abertura) ?>" required><br>

    <label for="data_fechamento">Data_fechamento:</label>
    <input type="date" id="data_fechamento" name="data_fechamento" value="<?= htmlspecialchars($data_fechamento) ?>" required><br>

    <label for="problema">Problema:</label>
    <input type="text" id="problema" name="problema" value="<?= htmlspecialchars($problema) ?>" required><br>

    <label for="solucao">Solução:</label>
    <input type="text" id="solucao" name="solucao" value="<?= htmlspecialchars($solucao) ?>" required><br>

    <label for="urgencia">Urgência:</label>
    <select name="urgencia" id="urgencia" required>
        <option value="">-- Selecione uma urgência --</option>
        <option value="Baixa">Baixa</option>
        <option value="Normal">Normal</option>
        <option value="Alta">Alta</option>
        <option value="Urgente">Urgente</option>
    </select>

    <button type="submit">Salvar</button><br>
    <button type="button" onclick="window.location.href='listar_chamados.php'">Cancelar</button><br>
</form>

</body>
</html>