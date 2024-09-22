<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = [];

// Se desejar, destrói também o cookie da sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona o usuário para a página inicial
header("Location: ../index.php");
exit();
?>
