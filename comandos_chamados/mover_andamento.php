<?php
include '../BD/conexao.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Verifica se o ID é válido e existe
    $check_sql = "SELECT id FROM chamado WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows === 0) {
        die("ID de chamado inválido ou não encontrado");
    }
    $check_stmt->close();

    // Atualização segura com prepared statement
    $update_sql = "UPDATE chamado SET progresso = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    
    // Define o valor do progresso (consistente com seu sistema)
    $progresso = 'Em andamento'; 
    
    $stmt->bind_param("si", $progresso, $id);
    
    if ($stmt->execute()) {
        header("Location: ../pagina_principal.php");
        exit;
    } else {
        // Log do erro (em produção, grave em um arquivo de log)
        error_log("Erro ao atualizar chamado: " . $stmt->error);
        die("Ocorreu um erro ao atualizar o chamado. Tente novamente.");
    }
    
    $stmt->close();
} else {
    http_response_code(400); // Bad Request
    die("ID não fornecido.");
}

$conn->close();
?>