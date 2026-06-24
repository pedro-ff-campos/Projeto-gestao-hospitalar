<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz (estamos na pasta private/, recua 1 nível)
$prefixo = '../';

// 2. Includes obrigatórios do sistema
require_once '../includes/auth.php'; 
require_once '../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Relatórios e Estatísticas';
$modulo_ativo  = 'relatorios';

$hoje = (new DateTimeImmutable('today'))->format('Y-m-d');

// ── LÓGICA PHP: Queries de Agregação e Estatística Real ──────────────────────
try {
    // 1. Valor total investido em contratos de manutenção ativos
    $total_investido_contratos = (float) $pdo->query("
        SELECT SUM(valor) 
        FROM contratos 
        WHERE data_fim >= '$hoje'
    ")->fetchColumn();

    // 2. Contagem de documentos fora do prazo de validade (Expirados)
    $docs_expirados = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM documentacao 
        WHERE data_validade < '$hoje'
    ")->fetchColumn();

    // 3. Contagem de garantias que terminam nos próximos 30 dias (A expirar)
    $garantias_criticas = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM garantias 
        WHERE data_fim BETWEEN '$hoje' AND DATE_ADD('$hoje', INTERVAL 30 DAY)
    ")->fetchColumn();

    // 4. Lista dos 5 fornecedores com mais contratos assinados (JOIN + GROUP BY)
    $stmt_top_fornecedores = $pdo->query("
        SELECT f.nome, COUNT(c.id) AS total_contratos
        FROM fornecedores f
        INNER JOIN contratos c ON c.id_fornecedor = f.id
        GROUP BY f.id
        ORDER BY total_contratos DESC
        LIMIT 5
    ");
    $top_fornecedores = $stmt_top_fornecedores->fetchAll();

    // 5. Contagem de equipamentos divididos por nível de criticidade
    $stmt_criticidade = $pdo->query("
        SELECT criticidade, COUNT(*) AS total 
        FROM equipamentos 
        GROUP BY criticidade
        ORDER BY total DESC
    ");
    $criticidade_dados = $stmt_criticidade->fetchAll();

} catch (PDOException $e) {
    // Valores nulos de segurança caso as tabelas estejam vazias
    $total_investido_contratos = 0.0;
    $docs_expirados = $garantias_criticas = 0;
    $top_fornecedores = $criticidade_dados = [];
}

// ── Incluir o header padrão do site ──────────────────────────────────────────
require_once '../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->

<div class="pagina-relatorios container-fluid py-4">

  <!-- Cabeçalho da página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-graph-up-arrow me-2"></i>Relatórios do Sistema</h1>
  </div>

  <!-- ── LINHA 1: Cartões de Alerta e Resumos Financeiros ── -->
  <div class="row g-3 mb-4">
    
    <!-- Cartão 1: Total Financeiro em Contratos -->
    <div class="col-md-4">
      <div class="card p-4 text-white text-center" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <i class="bi bi-currency-euro h1 text-info mb-2"></i>
        <small class="text-muted text-uppercase d-block mb-1 fw-bold">Investimento em Contratos</small>
        <h2 class="fw-bold m-0"><?php echo number_format($total_investido_contratos, 2, ',', '.'); ?> €</h2>
      </div>
    </div>

    <!-- Cartão 2: Documentos Expirados -->
    <div class="col-md-4">
      <div class="card p-4 text-white text-center" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <i class="bi bi-file-earmark-x h1 text-danger mb-2"></i>
        <small class="text-muted text-uppercase d-block mb-1 fw-bold">Documentos Expirados</small>
        <h2 class="fw-bold m-0"><?php echo $docs_expirados; ?> <small class="fs-6 text-muted">un.</small></h2>
      </div>
    </div>

    <!-- Cartão 3: Garantias a Terminar -->
    <div class="col-md-4">
      <div class="card p-4 text-white text-center" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <i class="bi bi-shield-exclamation h1 text-warning mb-2"></i>
        <small class="text-muted text-uppercase d-block mb-1 fw-bold">Garantias a Expirar (30d)</small>
        <h2 class="fw-bold m-0"><?php echo $garantias_criticas; ?> <small class="fs-6 text-muted">un.</small></h2>
      </div>
    </div>

  </div>

  <!-- ── LINHA 2: Tabelas de Resumo e Distribuição ── -->
  <div class="row g-4">
    
    <!-- Bloco Esquerdo: Distribuição por Criticidade -->
    <div class="col-md-6">
      <section class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h3 class="mb-3" style="font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Equipamentos por Criticidade</h3>
        
        <?php if (empty($criticidade_dados)): ?>
          <p class="text-muted py-4 text-center m-0">Nenhum equipamento registado.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Nível</th>
                  <th class="text-center">Quantidade</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($criticidade_dados as $dado): ?>
                  <tr>
                    <td>
                      <!-- Cores condicionais dinâmicas para a criticidade -->
                      <?php 
                        $badge_cor = 'bg-secondary';
                        if ($dado['criticidade'] === 'ALTA') $badge_cor = 'bg-danger text-white';
                        if ($dado['criticidade'] === 'MÉDIA') $badge_cor = 'bg-warning text-dark';
                      ?>
                      <span class="badge <?php echo $badge_cor; ?> px-3 py-1">
                        <?php echo htmlspecialchars($dado['criticidade'], ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    </td>
                    <td class="text-center fw-bold"><?php echo $dado['total']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <!-- Bloco Direito: Top Fornecedores com mais Contratos -->
    <div class="col-md-6">
      <section class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h3 class="mb-3" style="font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Top Fornecedores por Contratos</h3>
        
        <?php if (empty($top_fornecedores)): ?>
          <p class="text-muted py-4 text-center m-0">Nenhum contrato ativo registado.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Nome do Fornecedor</th>
                  <th class="text-center">Contratos Ativos</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($top_fornecedores as $forn): ?>
                  <tr>
                    <td class="fw-bold"><i class="bi bi-building text-muted me-2"></i> <?php echo htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text-center text-info fw-bold"><?php echo $forn['total_contratos']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </div>

  </div>

</div>
<?php include '../includes/footer.php'; ?>