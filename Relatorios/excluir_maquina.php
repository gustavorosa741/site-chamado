<?php

session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM maquina WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: listar_maquinas.php");
        exit;
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
} else {
    echo "ID não fornecido.";
}
?>