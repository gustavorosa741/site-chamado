<?php

// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Obtém o nível de acesso do usuário logado
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Restringe o acesso a usuários sem permissão
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>alert('Você não tem permissão para acessar essa página!'); window.location.href='../pagina_principal.php';</script>";
}

// Processa o cadastro da máquina quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST['nome']);
    $setor = trim($_POST['setor']);

    // Verifica se a máquina já está cadastrada no mesmo setor
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

    // Insere a nova máquina no banco de dados
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
    <!-- Configurações básicas da página -->
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <link rel="stylesheet" href="../assets/css/cadastros.css">
    <meta charset="UTF-8">
    <title>Cadastro de Máquina</title>
</head>

<body>

    <h1>Cadastro de Máquina</h1>

    <!-- Formulário para cadastro de uma nova máquina -->
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
    // Formata o texto removendo espaços e convertendo para maiúsculas
    function formatarEmTempoReal(campo) {
        let valor = campo.value.replace(/\s/g, '');
        valor = valor.toUpperCase();
        campo.value = valor;
        campo.setSelectionRange(valor.length, valor.length);
    }
</script>

</html>