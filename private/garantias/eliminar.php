<?php
declare(strict_types=1);

// 1. Inicia o controlo de sessões
session_start();

// Proteção: se não estiver logado, expulsa para o login
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ../../login.php');
    exit;
}

// 2. Inclui a ligação à base de dados
require_once '../../includes/db.php';     

// ── 1. Validar e Obter o ID da Garantia a Eliminar ───────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Executar a Eliminação Segura no MySQL ─────────────────────────────────
try {
    // Usamos Prepared Statements para evitar ataques à base de dados (SQL Injection)
    $sql = 'DELETE FROM garantias WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // REGISTO BIOMÉDICO: Grava o log de auditoria hospitalar
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
    $log_stmt->execute([$user_id, 'ELIMINAR_GARANTIA', "O utilizador removeu o registo de cobertura de garantia ID: $id."]);

    // Se correu bem, volta para a listagem com o feedback de sucesso
    header('Location: index.php?sucesso=eliminada');
    exit;

} catch (PDOException $e) {
    // Redireciona com erro genérico caso a query falhe
    header('Location: index.php?erro=1');
    exit;
}
