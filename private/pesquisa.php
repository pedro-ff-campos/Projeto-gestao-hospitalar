<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz (estamos na pasta private/, recua 1 nível)
$prefixo = '../';

// 2. Includes obrigatórios do sistema
require_once '../includes/auth.php'; 
require_once '../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Pesquisa Avançada';
$modulo_ativo  = 'pesquisa';

// ── Parâmetros de pesquisa cruzada (vindos do GET) ───────────────────────────
$termo        = trim($_GET['termo'] ?? '');
$estado       = trim($_GET['estado'] ?? '');
$criticidade  = trim($_GET['criticidade'] ?? '');
$id_servico   = max(0, (int)($_GET['id_servico'] ?? 0));

// ── Query 1: Carregar os Serviços Reais para o Dropdown de Filtro ────────────
try {
    $servicos_lista = $pdo->query("SELECT id, servico, edificio FROM localizacoes GROUP BY servico ORDER BY servico ASC")->fetchAll();
} catch (PDOException $e) {
    $servicos_lista = [];
}

// ── Query 2: Construção dinâmica da Pesquisa Cruzada ─────────────────────────
$equipamentos = [];
$pesquisa_feita = false;

// Só faz a busca na base de dados se o utilizador tiver submetido algum filtro
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($termo) || !empty($estado) || !empty($criticidade) || $id_servico > 0)) {
    $pesquisa_feita = true;
    
    try {
        $sql = "
            SELECT e.*, l.servico AS nome_servico, l.edificio AS nome_edificio
            FROM equipamentos e
            INNER JOIN localizacoes l ON l.id = e.id_localizacao
            WHERE 1=1
        ";
        $params = [];

        if ($termo !== '') {
            $sql .= ' AND (e.codigo LIKE :termo OR e.designacao LIKE :termo OR e.marca LIKE :termo OR e.modelo LIKE :termo)';
            $params[':termo'] = '%' . $termo . '%';
        }

        if ($estado !== '') {
            $sql .= ' AND e.estado = :estado';
            $params[':estado'] = $estado;
        }

        if ($criticidade !== '') {
            $sql .= ' AND e.criticidade = :criticidade';
            $params[':criticidade'] = $criticidade;
        }

        if ($id_servico > 0) {
            $sql .= ' AND e.id_localizacao = :id_servico';
            $params[':id_servico'] = $id_servico;
        }

        $sql .= ' ORDER BY e.codigo ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $equipamentos = $stmt->fetchAll();

    } catch (PDOException $e) {
        $equipamentos = [];
    }
}

// ── Incluir o header padrão do site ──────────────────────────────────────────
require_once '../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<!-- Usamos uma div com a classe para herdar o Dark Mode automático do teu CSS -->
<div class="pagina-equipamentos container-fluid py-4">

  <!-- Cabeçalho da página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-search-heart me-2"></i>Pesquisa Avançada</h1>
  </div>

  <!-- Bloco de Filtros Cruzados -->
  <section class="card text-white p-4 mb-4" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important;">
    <h2 class="mb-3" style="font-size: 13px; color: #94a3b8; text-transform: uppercase;">Definir Parâmetros de Busca Cruzada</h2>
    
    <form method="GET" action="pesquisa.php">
      <div class="row g-3">
        
        <!-- Texto livre -->
        <div class="col-md-6">
          <label for="termo" class="form-label small text-muted text-uppercase fw-bold">Palavra-Chave (Código, Nome, Marca...)</label>
          <input type="text" id="termo" name="termo" class="form-control" style="background: #0b1b3d !important; color: #fff;" placeholder="Ex: Ventilador, Dräger, EQ-01..." value="<?php echo htmlspecialchars($termo, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <!-- Filtro Serviço Hospitalar -->
        <div class="col-md-6">
          <label for="id_servico" class="form-label small text-muted text-uppercase fw-bold">Serviço / Especialidade</label>
          <select id="id_servico" name="id_servico" class="form-select" style="background: #0b1b3d !important; color: #fff;">
            <option value="">Todos os serviços</option>
            <?php foreach ($servicos_lista as $serv): ?>
              <option value="<?php echo $serv['id']; ?>" <?php echo $id_servico === (int)$serv['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($serv['servico'] . ' (' . $serv['edificio'] . ')', ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filtro Estado -->
        <div class="col-md-6">
          <label for="estado" class="form-label small text-muted text-uppercase fw-bold">Estado Operacional</label>
          <select id="estado" name="estado" class="form-select" style="background: #0b1b3d !important; color: #fff;">
            <option value="">Todos os estados</option>
            <option value="Ativo" <?php echo $estado === 'Ativo' ? 'selected' : ''; ?>>Ativo (Em funcionamento)</option>
            <option value="Manutenção" <?php echo $estado === 'Manutenção' ? 'selected' : ''; ?>>Em Manutenção</option>
            <option value="Inativo" <?php echo $estado === 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
          </select>
        </div>

        <!-- Filtro Criticidade -->
        <div class="col-md-6">
          <label for="criticidade" class="form-label small text-muted text-uppercase fw-bold">Nível de Criticidade</label>
          <select id="criticidade" name="criticidade" class="form-select" style="background: #0b1b3d !important; color: #fff;">
            <option value="">Todas as criticidades</option>
            <option value="BAIXA" <?php echo $criticidade === 'BAIXA' ? 'selected' : ''; ?>>BAIXA</option>
            <option value="MÉDIA" <?php echo $criticidade === 'MÉDIA' ? 'selected' : ''; ?>>MÉDIA</option>
            <option value="ALTA" <?php echo $criticidade === 'ALTA' ? 'selected' : ''; ?>>ALTA</option>
          </select>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary" style="background: #00bcff !important; border: none;"><i class="bi bi-search me-1"></i> Executar Busca</button>
        <a href="pesquisa.php" class="btn btn-outline-light text-muted"><i class="bi bi-x-circle me-1"></i> Limpar</a>
      </div>
    </form>
  </section>

  <!-- Bloco de Resultados -->
  <?php if ($pesquisa_feita): ?>
    <section class="card text-white p-4" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important;">
      <h2 class="mb-3" style="font-size: 13px; color: #94a3b8; text-transform: uppercase;">Resultados da Procura (<?php echo count($equipamentos); ?>)</h2>
      
      <?php if (empty($equipamentos)): ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-exclamation-circle h1"></i>
          <p class="mt-2">Nenhum equipamento corresponde aos filtros selecionados.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-dark table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Código</th>
                <th>Designação</th>
                <th>Marca/Modelo</th>
                <th>Serviço</th>
                <th>Estado</th>
                <th>Criticidade</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($equipamentos as $eq): ?>
                <tr>
                  <td class="fw-bold text-info"><?php echo htmlspecialchars($eq['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><strong><?php echo htmlspecialchars($eq['designacao'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                  <td><small class="text-muted"><?php echo htmlspecialchars(($eq['marca'] ?? '—') . ' / ' . ($eq['modelo'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></small></td>
                  <td><i class="bi bi-geo-alt-fill text-muted me-1"></i> <?php echo htmlspecialchars($eq['nome_servico'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td>
                    <!-- Reutiliza as classes de badge que o teu CSS original já formata -->
                    <span class="badge bg-opacity-10 text-white border border-secondary"><?php echo $eq['estado']; ?></span>
                  </td>
                  <td>
                    <span class="badge bg-dark border border-secondary text-white"><?php echo $eq['criticidade']; ?></span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a href="equipamentos/editar.php?id=<?php echo $eq['id']; ?>" class="btn btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

</div>
<?php include '../includes/footer.php'; ?>