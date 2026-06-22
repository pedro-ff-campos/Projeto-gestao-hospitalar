<?php
declare(strict_types=1);

session_start();

// 1. Auditoria e Rastreabilidade: Regista a saída do utilizador
if (isset($_SESSION['user_id'])) {
    // Como o logout está na raiz, acede diretamente à pasta includes/
    require_once 'includes/db.php';

    try {
        // Insere o evento de LOGOUT na tabela de auditoria
        $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
        $log_stmt->execute([
            $_SESSION['user_id'], 
            'LOGOUT', 
            'O Utilizador efetuou logout do sistema.'
        ]);
    } catch (PDOException $e) {
        // Bloco try/catch para o site não dar erro fatal se a tabela de logs falhar
    }
}

// 2. Limpeza de Segurança: Limpa todas as variáveis de sessão da memória
$_SESSION = [];

// 3. Destruição: Invalida o ID da sessão ativa no servidor por completo
session_destroy();

// 4. Redirecionamento: Envia o utilizador de volta para o login que está na raiz
header('Location: login.php');
exit;
?>
