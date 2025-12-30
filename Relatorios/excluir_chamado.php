<?php

// Inicia a sessão para controle de login
session_start();

// Verifica se o usuário está autenticado
// Caso não esteja, redireciona para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui o arquivo de conexão com o banco de dados
include '../BD/conexao.php';

// Verifica se o ID do chamado foi enviado via GET
if (isset($_GET['id'])) {

    // Converte o ID para inteiro (segurança básica)
    $id = intval($_GET['id']);

    // Monta a query para excluir o chamado pelo ID
    $sql = "DELETE FROM chamado WHERE id = $id";

    // Executa a exclusão do chamado
    if ($conn->query($sql) === TRUE) {

        // Redireciona para a listagem de chamados após excluir
        header("Location: listar_chamados.php");
        exit;

    } else {

        // Exibe mensagem de erro caso a exclusão falhe
        echo "Erro ao excluir: " . $conn->error;
    }

} else {

    // Exibe erro caso o ID não tenha sido informado
    echo "ID não fornecido.";
}
?>
