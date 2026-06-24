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

// ── 1. Validar e Obter o ID do Contrato a Eliminar ───────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Executar a Eliminação Segura no MySQL ─────────────────────────────────
try {
    // Comando preparado para evitar SQL Injection
    $sql = 'DELETE FROM contratos WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // REGISTO AUDITORIA: Regista a remoção para controlo de engenharia clínica
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
    $log_stmt->execute([$user_id, 'ELIMINAR_CONTRATO', "O utilizador removeu o registo de contrato de manutenção ID: $id."]);

    // Regressa à listagem principal 
    header('Location: index.php?sucesso=eliminado');
    exit;

} catch (PDOException $e) {
    // Redireciona com indicação de erro caso algo falhe na base de dados
    header('Location: index.php?erro=1');
    exit;
}
