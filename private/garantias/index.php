<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o CSS
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';     

// ── Variáveis para o header ──────────────────────────────────────────────────
$titulo_pagina = 'Garantias';
$modulo_ativo  = 'garantias';

// ── Estados de garantia calculados (para o filtro dropdown) ──────────────────
const ESTADOS_GARANTIA = [
    'ativa'     => 'Ativa',
    'a_expirar' => 'A expirar (30 dias)',
    'expirada'  => 'Expirada',
];

// ── Parâmetros de pesquisa e filtro (vindos do GET limpos) ───────────────────
$pesquisa       = trim($_GET['pesquisa'] ?? '');
$estado_filtro  = trim($_GET['estado'] ?? '');
$equipamento_id = max(0, (int)($_GET['equipamento_id'] ?? 0));

if (!array_key_exists($estado_filtro, ESTADOS_GARANTIA)) {
    $estado_filtro = '';
}

// ── Paginação Básica ─────────────────────────────────────────────────────────
$registos_por_pagina = 10;
$pagina_atual        = max(1, (int)($_GET['pagina'] ?? 1));
$offset              = ($pagina_atual - 1) * $registos_por_pagina;

// ── Datas de referência para cálculo de prazos ───────────────────────────────
$hoje       = new DateTimeImmutable('today');
$em_30_dias = $hoje->modify('+30 days');

// ── Construção direta e simples das condições WHERE (Fácil de explicar) ──────
$sql_where = " WHERE 1=1";
$params = [];

if ($pesquisa !== '') {
    $sql_where .= ' AND (g.referencia LIKE :pesquisa OR g.fornecedor_garantia LIKE :pesquisa OR e.designacao LIKE :pesquisa)';
    $params[':pesquisa'] = '%' . $pesquisa . '%';
}

// O filtro de estado traduz-se em condições de data no SQL
if ($estado_filtro === 'expirada') {
    $sql_where       .= ' AND g.data_fim < :hoje';
    $params[':hoje']  = $hoje->format('Y-m-d');
} elseif ($estado_filtro === 'a_expirar') {
    $sql_where            .= ' AND g.data_fim BETWEEN :hoje AND :em_30_dias';
    $params[':hoje']       = $hoje->format('Y-m-d');
    $params[':em_30_dias'] = $em_30_dias->format('Y-m-d');
} elseif ($estado_filtro === 'ativa') {
    $sql_where             .= ' AND g.data_fim > :em_30_dias';
    $params[':em_30_dias']  = $em_30_dias->format('Y-m-d');
}

if ($equipamento_id > 0) {
    $sql_where                 .= ' AND g.equipamento_id = :equipamento_id';
    $params[':equipamento_id']  = $equipamento_id;
}

// ── Query 1: Contar total de resultados para a paginação ─────────────────────
try {
    $sql_count = "
        SELECT COUNT(*) 
        FROM garantias g
        INNER JOIN equipamentos e ON e.id = g.equipamento_id
        $sql_where
    ";

    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total_registos = (int)$stmt_count->fetchColumn();

} catch (PDOException $e) {
    $total_registos = 0;
}

$total_paginas = max(1, (int)ceil($total_registos / $registos_por_pagina));
// ── Query 2: Obter as Garantias com JOIN, filtros e paginação ────────────────
try {
    $sql = "
        SELECT
            g.id,
            g.referencia,
            g.data_inicio,
            g.data_fim,
            g.fornecedor_garantia,
            e.id         AS equipamento_id,
            e.codigo     AS equipamento_codigo,
            e.designacao AS equipamento_designacao
        FROM garantias g
        INNER JOIN equipamentos e ON e.id = g.equipamento_id
        $sql_where
        ORDER BY g.data_fim ASC
        LIMIT $registos_por_pagina OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $garantias = $stmt->fetchAll();

} catch (PDOException $e) {
    $garantias = [];
}

// ── Query 3: Lista de equipamentos para preencher o filtro Dropdown ──────────
try {
    $equipamentos_lista = $pdo
        ->query('SELECT id, codigo, designacao FROM equipamentos ORDER BY designacao ASC')
        ->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── Query string para a paginação (mantém os filtros ativos ao navegar) ──────
$qs_filtros = http_build_query(array_filter([
    'pesquisa'       => $pesquisa,
    'estado'         => $estado_filtro,
    'equipamento_id' => $equipamento_id ?: '',
]));

// ── Função Auxiliar Académica: Calcular o estado e as classes Bootstrap ──────
function estado_garantia(DateTimeImmutable $data_fim, DateTimeImmutable $hoje, DateTimeImmutable $em_30_dias): array
{
    if ($data_fim < $hoje) {
        // Expirada -> Cor vermelha do Bootstrap
        return ['classe' => 'bg-danger text-white', 'label' => 'Expirada', 'icone' => 'bi-shield-x'];
    }
    if ($data_fim <= $em_30_dias) {
        // A expirar -> Cor amarela do Bootstrap
        return ['classe' => 'bg-warning text-dark', 'label' => 'A expirar', 'icone' => 'bi-shield-exclamation'];
    }
    // Ativa -> Cor verde do Bootstrap
    return ['classe' => 'bg-success text-white', 'label' => 'Ativa', 'icone' => 'bi-shield-check'];
}

/
require_once '../../includes/header.php';
?>


<!-- ════════════ CONTEÚDO ════════════ -->
<!-- Classe de contexto adicionada para herdar o teu design escuro automaticamente -->
<main class="pagina-garantias container-fluid py-4">

  <!-- ── Cabeçalho da página ── -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Garantias</h1>
    <a href="criar.php" class="btn btn-success">
      <i class="bi bi-plus-lg"></i> Nova Garantia
    </a>
  </div>

  <!-- ── Mensagens de feedback ── -->
  <?php if (isset($_GET['sucesso'])): ?>
    <?php
      $msgs = [
        'criada'    => 'Garantia registada com sucesso.',
        'editada'   => 'Garantia atualizada com sucesso.',
        'eliminada' => 'Garantia eliminada com sucesso.',
      ];
      $chave  = htmlspecialchars($_GET['sucesso'], ENT_QUOTES, 'UTF-8');
      $msg_ok = $msgs[$chave] ?? 'Operação realizada com sucesso.';
    ?>
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-check-circle me-2"></i>
      <div><?php echo $msg_ok; ?></div>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      <div>Não foi possível completar a operação. Por favor tente novamente.</div>
    </div>
  <?php endif; ?>

  <!-- ── Filtros (Grelha de 3 colunas do Bootstrap) ── -->
  <section class="card text-white p-4 mb-4">
    <h2 class="mb-3">Pesquisa e Filtros</h2>

    <form method="GET" action="index.php">
      <div class="row g-3">
        
        <!-- Pesquisa por texto -->
        <div class="col-md-4">
          <label for="pesquisa" class="form-label">Pesquisar</label>
          <input
            type="text"
            id="pesquisa"
            name="pesquisa"
            class="form-control"
            placeholder="Referência, fornecedor ou equipamento…"
            value="<?php echo $pesquisa; ?>"
          />
        </div>

        <!-- Filtro por Estado -->
        <div class="col-md-4">
          <label for="estado" class="form-label">Estado</label>
          <select id="estado" name="estado" class="form-select">
            <option value="">Todos os estados</option>
            <?php foreach (ESTADOS_GARANTIA as $valor => $label): ?>
              <option value="<?php echo $valor; ?>" <?php echo $estado_filtro === $valor ? 'selected' : ''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filtro por Equipamento -->
        <div class="col-md-4">
          <label for="equipamento_id" class="form-label">Equipamento</label>
          <select id="equipamento_id" name="equipamento_id" class="form-select">
            <option value="">Todos os equipamentos</option>
            <?php foreach ($equipamentos_lista as $eq): ?>
              <option value="<?php echo (int) $eq['id']; ?>" <?php echo $equipamento_id === (int) $eq['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($eq['codigo'] . ' — ' . $eq['designacao'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> Pesquisar
        </button>
        <a href="index.php" class="btn btn-outline-light">
          <i class="bi bi-x-circle"></i> Limpar Filtros
        </a>
      </div>
    </form>
  </section>

  <!-- ── Listagem ── -->
  <section class="card text-white p-4">

    <h2 class="mb-3">
      Lista de Garantias
      <span class="badge bg-secondary ms-2">
        <?php echo $total_registos; ?> <?php echo $total_registos === 1 ? 'resultado' : 'resultados'; ?>
      </span>
    </h2>

    <?php if (empty($garantias)): ?>
      <!-- Estado vazio -->
      <div class="text-center py-5 text-muted">
        <i class="bi bi-shield-check h1"></i>
        <p class="mt-2">Nenhuma garantia encontrada.</p>
        <?php if ($pesquisa !== '' || $estado_filtro !== '' || $equipamento_id > 0): ?>
          <a href="index.php" class="btn btn-sm btn-outline-light mt-2">Limpar filtros e ver todas</a>
        <?php else: ?>
          <a href="criar.php" class="btn btn-sm btn-outline-primary mt-2">Registar primeira garantia</a>
        <?php endif; ?>
      </div>
    <?php else: ?>

      <!-- Tabela Responsiva Bootstrap -->
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Referência</th>
              <th>Equipamento</th>
              <th>Fornecedor</th>
              <th>Início</th>
              <th>Fim</th>
              <th>Estado</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($garantias as $gar): ?>
              <?php
                $data_fim  = new DateTimeImmutable($gar['data_fim']);
                $estado    = estado_garantia($data_fim, $hoje, $em_30_dias);
              ?>
              <tr>
                <!-- Referência -->
                <td class="fw-bold"><?php echo htmlspecialchars($gar['referencia'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- Link para o equipamento associado -->
                <td>
                  <a href="../equipamentos/ver.php?id=<?php echo (int) $gar['equipamento_id']; ?>" class="text-info text-decoration-none">
                    <span class="badge bg-dark border border-secondary text-white me-1"><?php echo htmlspecialchars($gar['equipamento_codigo'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <small><?php echo htmlspecialchars($gar['equipamento_designacao'], ENT_QUOTES, 'UTF-8'); ?></small>
                  </a>
                </td>

                <!-- Fornecedor -->
                <td><?php echo htmlspecialchars($gar['fornecedor_garantia'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- Data de Início -->
                <td>
                  <?php echo !empty($gar['data_inicio']) ? (new DateTimeImmutable($gar['data_inicio']))->format('d/m/Y') : '—'; ?>
                </td>

                <!-- Data de Fim -->
                <td>
                  <?php echo $data_fim->format('d/m/Y'); ?>
                </td>

                <!-- Estado da garantia em Badge nativo do Bootstrap -->
                <td>
                  <span class="badge <?php echo $estado['classe']; ?>">
                    <i class="bi <?php echo $estado['icone']; ?> me-1"></i>
                    <?php echo $estado['label']; ?>
                  </span>
                </td>

                <!-- Botões de ação em grupo -->
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="editar.php?id=<?php echo (int) $gar['id']; ?>" class="btn btn-outline-warning" title="Editar garantia">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="eliminar.php?id=<?php echo (int) $gar['id']; ?>" class="btn btn-outline-danger" title="Eliminar garantia">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ── Paginação Bootstrap ── -->
      <?php if ($total_paginas > 1): ?>
        <nav class="d-flex justify-content-center mt-4" aria-label="Navegação de páginas">
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?php echo $pagina_atual <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?><?php echo isset($qs_filtros) && $qs_filtros ? '&' . $qs_filtros : ''; ?>">Anterior</a>
            </li>
            <li class="page-item active">
              <span class="page-link bg-primary border-primary"><?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
            </li>
            <li class="page-item <?php echo $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
              <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?><?php echo isset($qs_filtros) && $qs_filtros ? '&' . $qs_filtros : ''; ?>">Próxima</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    <?php endif; ?>

  </section>

</main>
<?php include '../../includes/footer.php'; ?>