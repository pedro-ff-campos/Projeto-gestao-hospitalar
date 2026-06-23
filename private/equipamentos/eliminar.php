<?php
declare(strict_types=1);

// 1. Inicia o controlo de sessões (podes ativar o auth quando o login estiver pronto)
session_start();
// require_once '../../includes/auth.php'; 

// 2. Inclui a ligação à base de dados
require_once '../../includes/db.php';     

// ── 1. Validar e Obter o ID do Equipamento a Eliminar ─────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

// Se o ID for inválido ou zero, regressa imediatamente à listagem por segurança
if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Executar a Eliminação Segura no MySQL ─────────────────────────────────
try {
    // Usamos Prepared Statements para evitar ataques à base de dados
    $sql = 'DELETE FROM equipamentos WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Se a query correu bem, volta para a listagem com o feedback de sucesso
    header('Location: index.php?sucesso=eliminado');
    exit;

} catch (PDOException $e) {
    // Se o equipamento não puder ser apagado por causa de restrições (chaves estrangeiras)
    header('Location: index.php?erro=1');
    exit;
}
