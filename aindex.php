<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Se chegou aqui, o usuário está logado
echo "Bem-vindo, " . htmlspecialchars($_SESSION['usuario_nome']);
?>
