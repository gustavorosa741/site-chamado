<?php
$modo_teste = true; // coloque false para voltar ao normal

if ($modo_teste) {
    header("Location: pagina_principal.php");
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

session_start();

?>