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

// ── 1. Validar e Obter o ID do Fornecedor a Eliminar ─────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

try {
    // ── 2. VALIDAÇÃO CONDICIONADA (RESTRICT): Verificar vínculos ativos ──────
    
    // Verificação A: Contratos associados a este fornecedor
    $stmt_contratos = $pdo->prepare("SELECT COUNT(*) FROM contratos WHERE id_fornecedor = ?");
    $stmt_contratos->execute([$id]);
    $total_contratos = (int) $stmt_contratos->fetchColumn();

    // Verificação B: Equipamentos vinculados a este fornecedor
    $stmt_equipamentos = $pdo->prepare("SELECT COUNT(*) FROM equipamento_fornecedor WHERE id_fornecedor = ?");
    $stmt_equipamentos->execute([$id]);
    $total_equipamentos = (int) $stmt_equipamentos->fetchColumn();

    // Se houver qualquer vínculo ativo, bloqueia a eliminação por segurança
    if ($total_contratos > 0 || $total_equipamentos > 0) {
        $total_vinculos = $total_contratos + $total_equipamentos;
        header('Location: index.php?erro=vinculado&total=' . $total_vinculos);
        exit;
    }

    // ── 3. Executar a Eliminação (Caso esteja 100% livre) ───────────────────
    $stmt_delete = $pdo->prepare('DELETE FROM fornecedores WHERE id = ?');
    $stmt_delete->execute([$id]);

    // REGISTO DE AUDITORIA: Grava a remoção para controlo de engenharia clínica
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
    $log_stmt->execute([$user_id, 'ELIMINAR_FORNECEDOR', "O utilizador removeu o fornecedor ID: $id."]);

    // Regressa à listagem com o alerta verde de sucesso
    header('Location: index.php?sucesso=eliminado');
    exit;

} catch (PDOException $e) {
    // Redireciona com indicação de erro genérico caso a query falhe no banco
    header('Location: index.php?erro=1');
    exit;
}
