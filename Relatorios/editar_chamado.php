<?php

// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
// Caso não esteja, redireciona para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui o arquivo de conexão com o banco de dados
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

// Bloqueia acesso caso o usuário não tenha permissão
if ($usuario['nivel_acesso'] > 2) {
    echo "<script>
            alert('Você não tem permissão para acessar essa página!');
            window.location.href='listar_chamados.php';
          </script>";
    exit;
}

// Verifica se o ID do chamado foi informado
if (!isset($_GET['id'])) {
    die("ID não fornecido.");
}

// Converte o ID para inteiro por segurança
$id = intval($_GET['id']);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera os dados enviados pelo formulário
    $id_maquina      = $_POST['id_maquina'] ?? '';
    $categoria       = $_POST['categoria'] ?? '';
    $data_abertura   = $_POST['data_abertura'] ?? '';
    $data_fechamento = $_POST['data_fechamento'] ?? '';
    $problema        = $_POST['problema'] ?? '';
    $solucao         = $_POST['solucao'] ?? '';
    $urgencia        = $_POST['urgencia'] ?? '';

    // Prepara a atualização dos dados do chamado
    $stmt = $conn->prepare(
        "UPDATE chamado 
         SET id_maquina = ?, categoria = ?, data_abertura = ?, data_fechamento = ?, 
             problema = ?, solucao = ?, urgencia = ? 
         WHERE id = ?"
    );
    $stmt->bind_param(
        "sssssssi",
        $id_maquina,
        $categoria,
        $data_abertura,
        $data_fechamento,
        $problema,
        $solucao,
        $urgencia,
        $id
    );

    // Executa a atualização
    if ($stmt->execute()) {
        // Redireciona para a listagem após salvar
        header("Location: listar_chamados.php");
        exit;
    } else {
        // Exibe erro em caso de falha
        echo "Erro ao atualizar: " . $conn->error;
    }

} else {

    // Caso não seja POST, busca os dados atuais do chamado
    $stmt = $conn->prepare(
        "SELECT id_maquina, categoria, data_abertura, data_fechamento, problema, solucao, urgencia 
         FROM chamado 
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result(
        $id_maquina,
        $categoria,
        $data_abertura,
        $data_fechamento,
        $problema,
        $solucao,
        $urgencia
    );
    $stmt->fetch();
    $stmt->close();
}

// Busca todas as máquinas para preencher o select
$maquinas = [];
$sql_maquinas = "SELECT id, nome_maquina FROM maquina";
$result = $conn->query($sql_maquinas);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
}

// Busca todas as categorias para preencher o select
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
    <meta charset="UTF-8">

    <!-- Ajuste de escala para dispositivos menores -->
    <meta name="viewport" content="width=device-width, initial-scale=0.9">

    <!-- Estilos da página -->
    <link rel="stylesheet" href="../assets/css/cadastros.css">

    <title>Editar Chamado</title>
</head>
<body>

<h1>Editar Chamado</h1>

<!-- Formulário para edição do chamado -->
<form class="form-container" action="" method="post">

    <!-- ID do chamado -->
    <input type="hidden" name="id_maquina" value="<?= $id ?>">

    <label for="maquina">Nome da Máquina:</label>
    <select id="maquina" name="id_maquina" required>
        <option value="">-- Selecione uma máquina --</option>
        <?php foreach ($maquinas as $maquina): ?>
            <option
                value="<?= htmlspecialchars($maquina['id']) ?>"
                <?= ($maquina['id'] == $id_maquina) ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($maquina['nome_maquina']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="categoria">Categoria:</label>
    <select id="categoria" name="categoria" required>
        <option value="">-- Selecione uma categoria --</option>
        <?php foreach ($categoria_chamado as $cat): ?>
            <option
                value="<?= htmlspecialchars($cat['id']) ?>"
                <?= ($cat['id'] == $categoria) ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($cat['categoria']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="data_abertura">Data de Abertura:</label>
    <input type="date" id="data_abertura" name="data_abertura"
           value="<?= htmlspecialchars($data_abertura) ?>" required>

    <label for="data_fechamento">Data de Fechamento:</label>
    <input type="date" id="data_fechamento" name="data_fechamento"
           value="<?= htmlspecialchars($data_fechamento) ?>" required>

    <label for="problema">Problema:</label>
    <input type="text" id="problema" name="problema"
           value="<?= htmlspecialchars($problema) ?>" required>

    <label for="solucao">Solução:</label>
    <input type="text" id="solucao" name="solucao"
           value="<?= htmlspecialchars($solucao) ?>" required>

    <label for="urgencia">Urgência:</label>
    <select name="urgencia" id="urgencia" required>
        <option value="">-- Selecione uma urgência --</option>
        <option value="Baixa"   <?= $urgencia === 'Baixa'   ? 'selected' : '' ?>>Baixa</option>
        <option value="Normal"  <?= $urgencia === 'Normal'  ? 'selected' : '' ?>>Normal</option>
        <option value="Alta"    <?= $urgencia === 'Alta'    ? 'selected' : '' ?>>Alta</option>
        <option value="Urgente" <?= $urgencia === 'Urgente' ? 'selected' : '' ?>>Urgente</option>
    </select>

    <!-- Ações do formulário -->
    <button type="submit">Salvar</button>
    <button type="button" onclick="window.location.href='listar_chamados.php'">
        Cancelar
    </button><br>

</form>

</body>
</html>
