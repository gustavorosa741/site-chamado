<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $consulta = "SELECT COUNT(*) as total FROM chamado WHERE categoria = ?";
    $stmt = $conn->prepare($consulta);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['total'] > 0) {
        // Categoria está em uso - não pode excluir
        echo "<script>alert('Esta categoria está em uso e não é possivel excluir.'); window.location.href='listar_categoria.php';</script>";
    } else {
        // Categoria não está em uso - pode excluir
        $sql = "DELETE FROM categoria_chamado WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: listar_categoria.php?sucesso=excluido");
            exit;
        } else {
            echo "Erro ao excluir: " . $conn->error;
        }
    }
}
?>