<?php
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