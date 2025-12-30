<?php

// Inicia a sessão para controle de login
session_start();

// Verifica se o usuário está autenticado
// Caso não esteja, redireciona para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Busca o nível de acesso do usuário
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verifica permissão de acesso à página
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>
            alert('Você não tem permissão para acessar essa página!');
            window.location.href='../pagina_principal.php';
          </script>";
    exit;
}

// Verifica se o ID do usuário a ser editado foi informado
if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

// Converte o ID para inteiro (segurança)
$id = intval($_GET['id']);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera os dados enviados pelo formulário
    $nome              = $_POST['nome'] ?? '';
    $usuario           = $_POST['usuario'] ?? '';
    $senha             = $_POST['senha'] ?? '';
    $confirmar_senha   = $_POST['confirmar_senha'] ?? '';
    $permissao         = $_POST['nivel_acesso'] ?? '';

    // Valida se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!');</script>";

    // Valida tamanho mínimo da senha
    } else if (strlen($senha) < 8) {
        echo "<script>alert('A senha deve ter no mínimo 8 caracteres!');</script>";

    // Valida tamanho mínimo do nome de usuário
    } else if (strlen($usuario) < 3) {
        echo "<script>alert('O nome deve ter no mínimo 3 caracteres!');</script>";

    } else {

        // Criptografa a senha antes de salvar
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Prepara a atualização dos dados do usuário
        $stmt = $conn->prepare(
            "UPDATE usuario 
             SET nome = ?, usuario = ?, senha = ?, nivel_acesso = ? 
             WHERE id = ?"
        );
        $stmt->bind_param(
            "ssssi",
            $nome,
            $usuario,
            $senha_hash,
            $permissao,
            $id
        );

        // Executa a atualização
        if ($stmt->execute()) {
            // Redireciona para a listagem após salvar
            header("Location: listar_usuarios.php");
            exit;
        } else {
            // Exibe erro em caso de falha
            echo "Erro ao atualizar: " . $conn->error;
        }
    }

} else {

    // Caso não seja POST, busca os dados atuais do usuário
    $stmt = $conn->prepare(
        "SELECT nome, usuario, nivel_acesso 
         FROM usuario 
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nome, $usuario, $permissao);
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

    <title>Editar Usuário</title>
</head>
<body>

<h1>Editar Usuário</h1>

<!-- Formulário de edição do usuário -->
<form class="form-container" action="" method="post">

    <label for="nome">Nome:</label>
    <input
        type="text"
        id="nome"
        name="nome"
        value="<?= htmlspecialchars($nome) ?>"
        required
    >

    <label for="usuario">Usuário:</label>
    <input
        type="text"
        id="usuario"
        name="usuario"
        value="<?= htmlspecialchars($usuario) ?>"
        required
    >

    <label for="senha">Senha:</label>
    <input
        type="password"
        id="senha"
        name="senha"
        required
    >

    <label for="confirmar_senha">Confirmar senha:</label>
    <input
        type="password"
        id="confirmar_senha"
        name="confirmar_senha"
        required
    >

    <label for="nivel_acesso">Nível de acesso:</label>
    <select id="nivel_acesso" name="nivel_acesso" required>
        <option value="">Selecione uma opção</option>
        <option value="1">Administrador</option>
        <option value="2">Manutenção</option>
        <option value="3">Usuário</option>
    </select>
    <br>

    <!-- Ações do formulário -->
    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_usuarios.php'">
        Cancelar
    </button><br>

</form>

</body>
</html>
