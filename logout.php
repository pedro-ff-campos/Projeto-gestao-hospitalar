<?php
session_start();

if (isset($_SESSION['user_id'])) {
    include 'includes/db.php';

    $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
    $log_stmt->execute([$_SESSION['user_id'], 'LOGOUT', 'O Utilizador efetuou logout do sistema.']);
}

// Limpa as variáveis de sessão
$_SESSION = array();

// Destrói a sessão ativa
session_destroy();

// Redireciona para o ecrã de login
header("Location: login.php");
exit;
?>