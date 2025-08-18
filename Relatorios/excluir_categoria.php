<?php
include '../BD/conexao.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM categoria_chamado WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: listar_categoria.php");
        exit;
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
} else {
    echo "ID não fornecido.";
}
?>