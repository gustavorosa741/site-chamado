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

// Verifica se o ID da máquina foi informado via GET
if (isset($_GET['id'])) {

    // Converte o ID para inteiro por segurança
    $id = intval($_GET['id']);
    
    // Verifica se a máquina está vinculada a algum chamado
    $consulta = "SELECT COUNT(*) as total FROM chamado WHERE id_maquina = ?";
    $stmt = $conn->prepare($consulta);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Impede exclusão se a máquina estiver em uso
    if ($row['total'] > 0) {

        // Exibe alerta e retorna para a listagem
        echo "<script>alert('Esta máquina está em uso e não é possivel excluir.'); window.location.href='listar_maquinas.php';</script>";

    } else {

        // Exclui a máquina caso não esteja vinculada a chamados
        $sql = "DELETE FROM maquina WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {

            // Redireciona após exclusão bem-sucedida
            header("Location: listar_maquinas.php?sucesso=excluido");
            exit;

        } else {

            // Exibe erro caso a exclusão falhe
            echo "Erro ao excluir: " . $conn->error;
        }
    }
}
?>
