 
<?php
$modo_teste = false; // coloque false para voltar ao normal (modo teste pula tela de login para facilitar atualizações)

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