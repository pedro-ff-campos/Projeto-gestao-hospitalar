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

// ── 1. Validar e Obter o ID da Localização a Eliminar ───────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

try {
    // ── 2. VALIDAÇÃO CONDICIONADA (RESTRICT): Verificar vínculos ────────────
    // Conta quantos equipamentos dependem desta localização exata
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM equipamentos WHERE id_localizacao = ?");
    $stmt_check->execute([$id]);
    $equipamentos_vinculados = (int) $stmt_check->fetchColumn();

    // Se existirem equipamentos na sala, bloqueia a eliminação por segurança
    if ($equipamentos_vinculados > 0) {
        header('Location: index.php?erro=vinculado&total=' . $equipamentos_vinculados);
        exit;
    }

    // ── 3. Executar a Eliminação (Caso a sala esteja 100% vazia) ────────────
    $stmt_delete = $pdo->prepare('DELETE FROM localizacoes WHERE id = ?');
    $stmt_delete->execute([$id]);

    // REGISTO DE AUDITORIA BIOMÉDICA: Grava o log
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
    $log_stmt->execute([$user_id, 'ELIMINAR_LOCALIZACAO', "O utilizador removeu o registo de localização vazia ID: $id."]);

    // Regressa à listagem com o alerta verde de sucesso
    header('Location: index.php?sucesso=eliminada');
    exit;

} catch (PDOException $e) {
    // Redireciona com indicação de erro genérico caso a query falhe
    header('Location: index.php?erro=1');
    exit;
}
