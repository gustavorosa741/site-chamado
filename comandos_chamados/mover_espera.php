<?php

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Verifica se o ID do chamado foi informado via GET
if (isset($_GET['id'])) {

    // Converte o ID recebido para inteiro
    $id = intval($_GET['id']);
    
    // Verifica se o chamado existe no banco de dados
    $check_sql = "SELECT id FROM chamado WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows === 0) {
        die("ID de chamado inválido ou não encontrado");
    }

    $check_stmt->close();

    // Atualiza o status do chamado para "Espera"
    $update_sql = "UPDATE chamado SET progresso = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    
    $progresso = 'Espera'; 
    
    $stmt->bind_param("si", $progresso, $id);
    
    if ($stmt->execute()) {
        // Redireciona para a página principal após a atualização
        header("Location: ../pagina_principal.php");
        exit;
    } else {
        // Registra o erro no log e exibe mensagem genérica
        error_log("Erro ao atualizar chamado: " . $stmt->error);
        die("Ocorreu um erro ao atualizar o chamado. Tente novamente.");
    }
    
    $stmt->close();

} else {
    // Retorna erro caso o ID não seja informado
    http_response_code(400);
    die("ID não fornecido.");
}

// Encerra a conexão com o banco de dados
$conn->close();
?>
