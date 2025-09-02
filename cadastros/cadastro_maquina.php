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

    $nome = trim($_POST['nome']);
    $setor = trim($_POST['setor']);

    $sql_check = "SELECT id FROM maquina WHERE nome_maquina = ? AND setor = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $nome, $setor);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Erro: Esta máquina já está cadastrada neste setor!'); window.location.href='../pagina_principal.php';</script>";
        $stmt_check->close();
        $conn->close();
        exit;
    }
    
    $stmt_check->close();

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
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <link rel="stylesheet" href="../assets/css/cadastros.css">
    <meta charset="UTF-8">
    <title>Cadastro de Máquina</title>
    
</head>
<body>

    <h1>Cadastro de Máquina</h1>

    <form class="form-container" action="" method="post">
        <label for="nome">Nome da Máquina:</label>
        <input type="text" id="nome" name="nome" required oninput="formatarEmTempoReal(this)">

        <label for="setor">Setor:</label>
        <input type="text" id="setor" name="setor" required oninput="formatarEmTempoReal(this)">


        <br>
        <button type="submit">Cadastrar</button>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </form>

</body>
<script>
    function formatarEmTempoReal(campo) {
            let valor = campo.value.replace(/\s/g, '');
            valor = valor.toUpperCase();
            campo.value = valor;
            campo.setSelectionRange(valor.length, valor.length);
    }
</script>
</html>
