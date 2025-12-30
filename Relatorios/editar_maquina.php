<?php

// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
// Caso não esteja, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consulta o nível de acesso do usuário
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Impede acesso de usuários sem permissão
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>
            alert('Você não tem permissão para acessar essa página!');
            window.location.href='../pagina_principal.php';
          </script>";
    exit;
}

// Verifica se o ID da máquina foi informado
if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

// Converte o ID para inteiro por segurança
$id = intval($_GET['id']);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera os dados enviados pelo formulário
    $nome  = $_POST['nome']  ?? '';
    $setor = $_POST['setor'] ?? '';

    // Prepara a atualização dos dados da máquina
    $stmt = $conn->prepare(
        "UPDATE maquina 
         SET nome_maquina = ?, setor = ? 
         WHERE id = ?"
    );
    $stmt->bind_param("ssi", $nome, $setor, $id);

    // Executa a atualização
    if ($stmt->execute()) {
        // Redireciona para a listagem após salvar
        header("Location: listar_maquinas.php");
        exit;
    } else {
        // Exibe erro em caso de falha
        echo "Erro ao atualizar: " . $conn->error;
    }

} else {

    // Caso não seja POST, busca os dados atuais da máquina
    $stmt = $conn->prepare(
        "SELECT nome_maquina, setor 
         FROM maquina 
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nome_maquina, $setor);
    $stmt->fetch();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <!-- Ajuste de escala para dispositivos menores -->
    <meta name="viewport" content="width=device-width, initial-scale=0.9">

    <!-- Estilos da página -->
    <link rel="stylesheet" href="../assets/css/cadastros.css">

    <title>Editar Máquina</title>
</head>
<body>

<h1>Editar Máquina</h1>

<!-- Formulário para edição da máquina -->
<form class="form-container" action="" method="post">

    <label for="nome">Nome da Máquina:</label>
    <input
        type="text"
        id="nome"
        name="nome"
        value="<?= htmlspecialchars($nome_maquina) ?>"
        required
    >

    <label for="setor">Setor:</label>
    <input
        type="text"
        id="setor"
        name="setor"
        value="<?= htmlspecialchars($setor) ?>"
        required
    ><br>

    <!-- Ações do formulário -->
    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_maquinas.php'">
        Cancelar
    </button><br>

</form>

</body>
</html>
