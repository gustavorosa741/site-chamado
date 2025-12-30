<?php

// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
// Caso não esteja, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui o arquivo de conexão com o banco de dados
include '../BD/conexao.php';

// Verifica se o ID do usuário foi informado via GET
if (isset($_GET['id'])) {

    // Converte o ID para inteiro por segurança
    $id = intval($_GET['id']);

    // Monta a query para excluir o usuário pelo ID
    $sql = "DELETE FROM usuario WHERE id = $id";

    // Executa a exclusão
    if ($conn->query($sql) === TRUE) {

        // Redireciona para a listagem após exclusão
        header("Location: listar_usuarios.php");
        exit;

    } else {

        // Exibe erro caso a exclusão falhe
        echo "Erro ao excluir: " . $conn->error;
    }

} else {

    // Exibe mensagem caso o ID não seja informado
    echo "ID não fornecido.";
}
?>
