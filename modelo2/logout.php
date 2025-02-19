<?php
// Iniciar sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy(); 

// Remover cookie com configurações seguras
setcookie('user_id', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]); 

// Redirecionar para a página de login
header('Location: login.php');  
exit();
?>