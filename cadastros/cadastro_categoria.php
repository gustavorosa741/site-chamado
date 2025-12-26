<?php

// Inicia a sessão e permite o controle de autenticação do usuário
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

// Restringe o acesso de usuários sem permissão
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>alert('Você não tem permissão para acessar essa página!'); window.location.href='../pagina_principal.php';</script>";
}

// Processa o cadastro da categoria quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $categoria = $_POST['categoria'];

    $sql = "INSERT INTO categoria_chamado (categoria) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);

    // Executa a inserção da categoria no banco de dados
    if ($stmt->execute()) {
        echo "<script>alert('Categoria cadastrada com sucesso!'); window.location.href='../pagina_principal.php';</script>";
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
    <title>Cadastro de Categoria</title>
</head>

<body>

    <h1>Cadastro de Categoria</h1>

    <!-- Formulário para cadastro de nova categoria -->
    <form class="form-container" action="" method="post">
        <label for="categoria">Categoria:</label>
        <input type="text" id="categoria" name="categoria" required oninput="formatarEmTempoReal(this)">
        <br>
        <button type="submit">Cadastrar</button>
        <button type="button" onclick="window.location.href='../pagina_principal.php'">Voltar</button>
    </form>

</body>

<script>
    // Formata o texto digitado removendo espaços e convertendo para maiúsculas
    function formatarEmTempoReal(campo) {
        let valor = campo.value.replace(/\s/g, '');
        valor = valor.toUpperCase();
        campo.value = valor;
        campo.setSelectionRange(valor.length, valor.length);
    }
</script>

</html>