<?php

// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
// Caso não esteja, redireciona para a tela de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Verifica se o ID da categoria foi enviado via GET
if (isset($_GET['id'])) {

    // Converte o ID para inteiro (segurança)
    $id = intval($_GET['id']);

    // Consulta para verificar se a categoria está sendo usada em chamados
    $consulta = "SELECT COUNT(*) as total FROM chamado WHERE categoria = ?";
    $stmt = $conn->prepare($consulta);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Se a categoria estiver vinculada a algum chamado
    if ($row['total'] > 0) {

        // Bloqueia a exclusão e informa o usuário
        echo "<script>
                alert('Esta categoria está em uso e não é possivel excluir.');
                window.location.href='listar_categoria.php';
              </script>";

    } else {

        // Caso não esteja em uso, permite a exclusão da categoria
        $sql = "DELETE FROM categoria_chamado WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        // Executa a exclusão
        if ($stmt->execute()) {

            // Redireciona para a listagem com sucesso
            header("Location: listar_categoria.php?sucesso=excluido");
            exit;

        } else {

            // Exibe erro em caso de falha
            echo "Erro ao excluir: " . $conn->error;
        }
    }
}
?>