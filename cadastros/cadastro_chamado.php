<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $maquina_id = $_POST['maquina'];
    $data = $_POST['data'];
    $problema = $_POST['problema'];
    $categoria = $_POST['categoria'];
    $progresso = 'Aberto';
    $id_funcionario = $_SESSION['usuario_id'];
    $urgencia = $_POST['urgencia'];


    $sql = "INSERT INTO chamado (id_funcionario, id_maquina, data_abertura, problema, categoria, progresso, urgencia) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssss",$id_funcionario, $maquina_id, $data, $problema, $categoria, $progresso, $urgencia);

    if ($stmt->execute()) {
        echo "<script>alert('Chamado cadastrado com sucesso!'); window.location.href='../pagina_principal.php';</script>";
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.9">]
    <link rel="stylesheet" href="../assets/css/cadastros.css">
    <meta charset="UTF-8">
    <title>Cadastro de Chamado</title>
    
</head>
<body>

    <h1>Cadastro de Chamado</h1>

    <form class="form-container" action="" method="post">
        <label for="maquina">Nome da Máquina:</label>
        <select id="maquina" name="maquina" required>
            <option value="">Selecione uma máquina</option>
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

        <label for="categoria">Categoria:</label>
        <select id="categoria" name="categoria" required>
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categoria_chamado as $categoria): ?>
                <option value="<?= htmlspecialchars($categoria['id']) ?>">
                    <?= htmlspecialchars($categoria['categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="urgencia">Urgência:</label>        
        <select name="urgencia" id="urgencia">
            <option value="">Selecione uma urgência</option>
            <option value="Baixa">Baixa</option>
            <option value="Normal">Normal</option>
            <option value="Alta">Alta</option>
            <option value="Urgente">Urgente</option>
        </select>
                
        <button type="submit">Cadastrar</button>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </form>

</body>
</html>

