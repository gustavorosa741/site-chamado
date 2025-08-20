<?php
// logout.php
session_start();

// Registrar o logout (opcional, para auditoria)
if (isset($_SESSION['usuario_id'])) {
    // Você pode registrar em um log quem fez logout e quando
    error_log("Usuário " . $_SESSION['usuario_id'] . " fez logout em " . date('Y-m-d H:i:s'));
}

// Limpar todos os dados da sessão
$_SESSION = [];

// Destruir o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para login com mensagem (opcional)
header("Location: login.php?msg=logout_success");
exit;
?>