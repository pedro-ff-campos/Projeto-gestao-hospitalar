<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz (estamos na pasta private/, recua 1 nível)
$prefixo = '../';

// 2. Includes obrigatórios do sistema
require_once '../includes/db.php';     
session_start();

// Proteção básica de sessão
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
   header('Location: ../login.php');
   exit;
}

// ── Paginação Simples para os Logs ───────────────────────────────────────────
$registos_por_pagina = 15;
$pagina_atual        = max(1, (int)($_GET['pagina'] ?? 1));
$offset              = ($pagina_atual - 1) * $registos_por_pagina;

// ── Query 1: Contar o total de logs para a paginação ─────────────────────────
try {
    $total_registos = (int) $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
} catch (PDOException $e) {
    $total_registos = 0;
}

$total_paginas = max(1, (int)ceil($total_registos / $registos_por_pagina));

// ── Query 2: Obter o Histórico de Eventos com os nomes dos utilizadores ──────
try {
    // Usamos LEFT JOIN porque tentativas de login com emails falsos guardam o ID do utilizador como NULL
    $sql = "
        SELECT l.*, u.nome AS nome_utilizador, u.email AS email_utilizador
        FROM logs l
        LEFT JOIN utilizadores u ON u.id = l.utilizador_id
        ORDER BY l.id DESC
        LIMIT $registos_por_pagina OFFSET $offset
    ";
    $historico_logs = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $historico_logs = [];
}

// ── Incluir o header padrão do site ──────────────────────────────────────────
require_once '../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<div class="pagina-equipamentos container-fluid py-4">

  <!-- Cabeçalho da página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-clock-history me-2"></i>Histórico de Sessões e Auditoria</h1>
  </div>

  <div class="row g-4">
    
    <!-- COLUNA ESQUERDA: A Tabela de Logs (Ocupa 8 partes) -->
    <div class="col-md-8">
      <section class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h2 class="mb-3" style="font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Registos de Segurança do Sistema</h2>

        <?php if (empty($historico_logs)): ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-folder-x h1"></i>
            <p class="mt-2 m-0">Nenhum evento registado no histórico de auditoria.</p>
          </div>
        <?php else: ?>
          
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Data / Hora</th>
                  <th>Utilizador</th>
                  <th>Ação</th>
                  <th>Detalhes</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($historico_logs as $log): ?>
                  <?php
                    $badge_classe = 'bg-secondary text-white';
                    $icone = 'bi-info-circle';

                    if ($log['acao'] === 'LOGIN_SUCESSO') {
                        $badge_classe = 'bg-success text-white';
                        $icone = 'bi-check-circle';
                    } elseif ($log['acao'] === 'LOGOUT') {
                        $badge_classe = 'bg-info text-dark';
                        $icone = 'bi-box-arrow-right';
                    } elseif ($log['acao'] === 'LOGIN_FALHADO' || $log['acao'] === 'ALTERAR_PASSWORD_FALHA') {
                        $badge_classe = 'bg-warning text-dark';
                        $icone = 'bi-exclamation-triangle';
                    } elseif ($log['acao'] === 'LOGIN_AVISO') {
                        $badge_classe = 'bg-danger text-white';
                        $icone = 'bi-shield-slash';
                    }
                  ?>
                  <tr>
                    <td class="text-nowrap text-muted" style="font-size: 0.85rem;">
                      <i class="bi bi-calendar3 me-1"></i> 
                      <?php echo date('d/m/Y H:i:s', strtotime($log['criado_at'])); ?>
                    </td>
                    <td>
                      <?php if (!empty($log['nome_utilizador'])): ?>
                        <strong class="text-white"><?php echo htmlspecialchars($log['nome_utilizador'], ENT_QUOTES, 'UTF-8'); ?></strong>
                      <?php else: ?>
                        <span class="text-warning fw-bold"><i class="bi bi-robot me-1"></i>Anónimo</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge <?php echo $badge_classe; ?> px-2 py-1 small fw-bold">
                        <i class="bi <?php echo $icone; ?> me-1"></i>
                        <?php echo htmlspecialchars($log['acao'], ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    </td>
                    <td class="small" style="color: #cbd5e1; font-size: 0.85rem;">
                      <?php echo htmlspecialchars($log['detalhes'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Paginação -->
          <?php if ($total_paginas > 1): ?>
            <nav class="d-flex justify-content-center mt-4">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?php echo $pagina_atual <= 1 ? 'disabled' : ''; ?>">
                  <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>">Anterior</a>
                </li>
                <li class="page-item active">
                  <span class="page-link bg-primary border-primary"><?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
                </li>
                <li class="page-item <?php echo $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
                  <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>">Próxima</a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>

        <?php endif; ?>
      </section>
    </div>

    <!-- COLUNA DIREITA: Resumo Normativo Hospitalar (Ocupa 4 partes) -->
    <div class="col-md-4">
      <div class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h3 class="mb-3 mt-2" style="font-size: 13px; color: #00bcff; text-transform: uppercase; letter-spacing: 0.5px;">Controlo de Dispositivos Médicos</h3>
        
        <div class="mb-4">
          <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.75rem;">Norma ISO 13485</small>
          <p class="text-muted small m-0" style="font-size: 0.85rem; line-height: 1.4;">
            Este registo de auditoria cumpre as diretrizes de rastreabilidade de equipamentos, documentando todas as modificações técnicas e operacionais efetuadas pelo pessoal clínico ou de engenharia.
          </p>
        </div>

        <div class="border-top border-secondary pt-3">
          <small class="text-muted d-block text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">Estados Monitorizados</small>
          <div class="d-flex flex-column gap-2 small text-white">
            <div><span class="badge bg-success me-2">✔</span> Logins bem-sucedidos</div>
            <div><span class="badge bg-warning text-dark me-2">⚠</span> Falhas de palavra-passe</div>
            <div><span class="badge bg-danger me-2">✖</span> Tentativas com e-mails falsos</div>
            <div><span class="badge bg-info text-dark me-2">➡</span> Terminações de sessão (Logout)</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include '../includes/footer.php'; ?>