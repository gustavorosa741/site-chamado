<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    error_log("Usuário " . $_SESSION['usuario_id'] . " fez logout em " . date('Y-m-d H:i:s'));
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: login.php?msg=logout_success");
exit;
?>