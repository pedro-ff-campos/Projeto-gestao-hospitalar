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

// ── 1. Validar e Obter o ID do Documento a Eliminar ─────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Buscar o Nome do Ficheiro Real antes de Apagar da BD ─────────────────
try {
    $stmt_busca = $pdo->prepare("SELECT ficheiro FROM documentacao WHERE id = ?");
    $stmt_busca->execute([$id]);
    $documento = $stmt_busca->fetch();

    if ($documento) {
        // Se existir um PDF/Imagem anexado e o ficheiro físico existir no servidor
        $nome_ficheiro = $documento['ficheiro'];
        $caminho_fisico = '../../assets/docs/' . $nome_ficheiro;

        if (!empty($nome_ficheiro) && file_exists($caminho_fisico)) {
            // Apaga o ficheiro do disco do servidor para não acumular lixo
            unlink($caminho_fisico);
        }
    }

    // ── 3. Executar a Eliminação na Base de Dados ───────────────────────────
    $stmt_delete = $pdo->prepare('DELETE FROM documentacao WHERE id = ?');
    $stmt_delete->execute([$id]);

    // EXIGIDO NO GUIÃO: Grava o log de auditoria
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
    $log_stmt->execute([$user_id, 'ELIMINAR_DOCUMENTO', "O utilizador eliminou o registo de documento ID: $id."]);

    // Regressa à listagem com feedback de sucesso
    header('Location: index.php?sucesso=eliminado');
    exit;

} catch (PDOException $e) {
    // Caso ocorra algum erro inesperado na query
    header('Location: index.php?erro=1');
    exit;
}
