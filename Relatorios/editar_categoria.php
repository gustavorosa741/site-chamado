<?php

// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
// Caso não esteja, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui o arquivo de conexão com o banco de dados
include '../BD/conexao.php';

// Recupera o ID do usuário logado a partir da sessão
$usuario_id = $_SESSION['usuario_id'];

// Busca o nível de acesso do usuário
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verifica se o usuário possui permissão para acessar esta página
// Usuários com nível maior que 2 não têm acesso
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>
            alert('Você não tem permissão para acessar essa página!');
            window.location.href='../pagina_principal.php';
          </script>";
    exit;
}

// Verifica se o ID da categoria foi enviado via GET
if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

// Converte o ID recebido para inteiro por segurança
$id = intval($_GET['id']);

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe o nome da categoria enviado pelo formulário
    $categoria = $_POST['categoria'] ?? '';

    // Prepara a query para atualizar a categoria no banco de dados
    $stmt = $conn->prepare(
        "UPDATE categoria_chamado SET categoria = ? WHERE id = ?"
    );
    $stmt->bind_param("si", $categoria, $id);

    // Executa a atualização
    if ($stmt->execute()) {
        // Redireciona para a listagem após salvar
        header("Location: listar_categoria.php");
        exit;
    } else {
        // Exibe mensagem de erro caso a atualização falhe
        echo "Erro ao atualizar: " . $conn->error;
    }

} else {

    // Caso não seja POST, busca os dados da categoria para preencher o formulário
    $stmt = $conn->prepare(
        "SELECT categoria FROM categoria_chamado WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Armazena o valor da categoria na variável
    $stmt->bind_result($categoria);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <!-- Ajuste de escala para melhor visualização em telas menores -->
    <meta name="viewport" content="width=device-width, initial-scale=0.9">

    <!-- Arquivo de estilos da página -->
    <link rel="stylesheet" href="../assets/css/cadastros.css">

    <title>Editar Categoria</title>
</head>
<body>

<h1>Editar Categoria</h1>

<!-- Formulário para edição da categoria -->
<form class="form-container" action="" method="post">

    <label for="categoria">Categoria:</label>

    <!-- htmlspecialchars evita problemas de segurança (XSS) -->
    <input
        type="text"
        id="categoria"
        name="categoria"
        value="<?= htmlspecialchars($categoria) ?>"
        required
    ><br>

    <!-- Botão para salvar as alterações -->
    <button type="submit">Salvar</button>

    <!-- Botão para cancelar e voltar à listagem -->
    <button
        type="button"
        onclick="window.location.href='listar_categoria.php'"
    >
        Cancelar
    </button><br>

</form>

</body>
</html>
