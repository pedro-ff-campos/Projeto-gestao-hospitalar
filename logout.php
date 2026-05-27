<?php
session_start();

// Limpa as variáveis de sessão
$_SESSION = array();

// Destrói a sessão ativa
session_destroy();

// Redireciona para o ecrã de login
header("Location: login.php");
exit;
?>