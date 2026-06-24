<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o teu CSS
$prefixo = '../../';


require_once '../../includes/auth.php';   
require_once '../../includes/db.php';     

// ── Variáveis para o header ──────────────────────────────────────────────────
$titulo_pagina = 'Contratos';
$modulo_ativo  = 'contratos';

// ── Tipos e estados válidos (Constantes limpas para os dropdowns) ─────────────
const TIPOS_CONTRATO = [
    'manutencao_preventiva' => 'Manutenção Preventiva',
    'manutencao_corretiva'  => 'Manutenção Corretiva',
    'calibracao'            => 'Calibração',
    'assistencia_tecnica'   => 'Assistência Técnica',
    'full_service'          => 'Full Service',
    'outro'                 => 'Outro',
];

const ESTADOS_CONTRATO = [
    'ativo'      => 'Ativo',
    'a_renovar'  => 'A renovar (30 dias)',
    'expirado'   => 'Expirado',
];

// ── Parâmetros de pesquisa e filtro (vindos do GET limpos) ───────────────────
$pesquisa       = trim($_GET['pesquisa'] ?? '');
$tipo           = trim($_GET['tipo'] ?? '');
$estado_filtro  = trim($_GET['estado'] ?? '');
$equipamento_id = max(0, (int)($_GET['equipamento_id'] ?? 0));

if (!array_key_exists($tipo, TIPOS_CONTRATO))   $tipo = '';
if (!array_key_exists($estado_filtro, ESTADOS_CONTRATO)) $estado_filtro = '';

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
    $sql_where .= ' AND (c.numero_contrato LIKE :pesquisa OR e.designacao LIKE :pesquisa OR f.nome LIKE :pesquisa)';
    $params[':pesquisa'] = '%' . $pesquisa . '%';
}

if ($tipo !== '') {
    $sql_where .= ' AND c.tipo = :tipo';
    $params[':tipo'] = $tipo;
}

if ($estado_filtro === 'expirado') {
    $sql_where       .= ' AND c.data_fim < :hoje';
    $params[':hoje']  = $hoje->format('Y-m-d');
} elseif ($estado_filtro === 'a_renovar') {
    $sql_where            .= ' AND c.data_fim BETWEEN :hoje AND :em_30_dias';
    $params[':hoje']       = $hoje->format('Y-m-d');
    $params[':em_30_dias'] = $em_30_dias->format('Y-m-d');
} elseif ($estado_filtro === 'ativo') {
    $sql_where             .= ' AND c.data_fim > :em_30_dias';
    $params[':em_30_dias']  = $em_30_dias->format('Y-m-d');
}

if ($equipamento_id > 0) {
    $sql_where                 .= ' AND c.equipamento_id = :equipamento_id';
    $params[':equipamento_id']  = $equipamento_id;
}

// ── Query 1: Contar total de resultados para a paginação ─────────────────────
try {
    $sql_count = "
        SELECT COUNT(*) 
        FROM contratos c
        INNER JOIN equipamentos e ON e.id = c.equipamento_id
        LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
        $sql_where
    ";

    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total_registos = (int)$stmt_count->fetchColumn();

} catch (PDOException $e) {
    $total_registos = 0;
}

$total_paginas = max(1, (int)ceil($total_registos / $registos_por_pagina));
// ── Query 2: Obter os Contratos com JOINs, filtros e paginação ────────────────
try {
    $sql = "
        SELECT
            c.id,
            c.numero_contrato,
            c.tipo,
            c.data_inicio,
            c.data_fim,
            c.valor,
            e.id         AS equipamento_id,
            e.codigo     AS equipamento_codigo,
            e.designacao AS equipamento_designacao,
            f.id         AS fornecedor_id,
            f.nome       AS fornecedor_nome
        FROM contratos c
        INNER JOIN equipamentos e ON e.id = c.equipamento_id
        LEFT JOIN fornecedores f  ON f.id = c.fornecedor_id
        $sql_where
        ORDER BY c.data_fim ASC
        LIMIT $registos_por_pagina OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contratos = $stmt->fetchAll();

} catch (PDOException $e) {
    $contratos = [];
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
    'tipo'           => $tipo,
    'estado'         => $estado_filtro,
    'equipamento_id' => $equipamento_id ?: '',
]));

// ── Função Auxiliar Académica: Calcular o estado com classes Bootstrap ──────
function estado_contracto(DateTimeImmutable $data_fim, DateTimeImmutable $hoje, DateTimeImmutable $em_30_dias): array
{
    if ($data_fim < $hoje) {
        // Expirado -> Cor vermelha do Bootstrap
        return ['classe' => 'bg-danger text-white', 'label' => 'Expirado', 'icone' => 'bi-file-earmark-x'];
    }
    if ($data_fim <= $em_30_dias) {
        // A renovar -> Cor amarela do Bootstrap
        return ['classe' => 'bg-warning text-dark', 'label' => 'A renovar', 'icone' => 'bi-arrow-clockwise'];
    }
    // Ativo -> Cor verde do Bootstrap
    return ['classe' => 'bg-success text-white', 'label' => 'Ativo', 'icone' => 'bi-file-earmark-check'];
}

// ── Incluir o header (abre o menu lateral e o main com a classe de contexto) ──
require_once '../../includes/header.php';
?>


<!-- ════════════ CONTEÚDO ════════════ -->
<!-- Classe de contexto e espaçamentos do Bootstrap -->
<main class="pagina-contratos container-fluid py-4">

  <!-- ── Cabeçalho da página ── -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Contratos de Manutenção</h1>
    <a href="criar.php" class="btn btn-success">
      <i class="bi bi-plus-lg"></i> Novo Contrato
    </a>
  </div>

  <!-- ── Mensagens de feedback ── -->
  <?php if (isset($_GET['sucesso'])): ?>
    <?php
      $msgs = [
        'criado'    => 'Contrato registado com sucesso.',
        'editado'   => 'Contrato atualizado com sucesso.',
        'eliminado' => 'Contrato eliminado com sucesso.',
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

  <!-- ── Filtros (Grelha de 4 colunas bem distribuídas) ── -->
  <section class="card text-white p-4 mb-4">
    <h2 class="mb-3">Pesquisa e Filtros</h2>

    <form method="GET" action="index.php">
      <div class="row g-3">
        
        <!-- Pesquisa por texto -->
        <div class="col-md-3">
          <label for="pesquisa" class="form-label">Pesquisar</label>
          <input
            type="text"
            id="pesquisa"
            name="pesquisa"
            class="form-control"
            placeholder="Nº contrato, equipamento ou fornecedor…"
            value="<?php echo $pesquisa; ?>"
          />
        </div>

        <!-- Filtro por Tipo de Contrato -->
        <div class="col-md-3">
          <label for="tipo" class="form-label">Tipo de Contrato</label>
          <select id="tipo" name="tipo" class="form-select">
            <option value="">Todos os tipos</option>
            <?php foreach (TIPOS_CONTRATO as $valor => $label): ?>
              <option value="<?php echo $valor; ?>" <?php echo $tipo === $valor ? 'selected' : ''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filtro por Estado -->
        <div class="col-md-3">
          <label for="estado" class="form-label">Estado</label>
          <select id="estado" name="estado" class="form-select">
            <option value="">Todos os estados</option>
            <?php foreach (ESTADOS_CONTRATO as $valor => $label): ?>
              <option value="<?php echo $valor; ?>" <?php echo $estado_filtro === $valor ? 'selected' : ''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filtro por Equipamento -->
        <div class="col-md-3">
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

      <!-- Botões de Ação -->
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
  <!-- ── Listagem (Cartão integrado no tema escuro) ── -->
  <section class="card text-white p-4">

    <h2 class="mb-3">
      Lista de Contratos
      <span class="badge bg-secondary ms-2">
        <?php echo $total_registos; ?> <?php echo $total_registos === 1 ? 'resultado' : 'resultados'; ?>
      </span>
    </h2>

    <?php if (empty($contratos)): ?>
      <!-- Estado vazio bem desenhado -->
      <div class="text-center py-5 text-muted">
        <i class="bi bi-file-earmark-ruled h1"></i>
        <p class="mt-2">Nenhum contrato encontrado.</p>
        <?php if ($pesquisa !== '' || $tipo !== '' || $estado_filtro !== '' || $equipamento_id > 0): ?>
          <a href="index.php" class="btn btn-sm btn-outline-light mt-2">Limpar filtros e ver todos</a>
        <?php else: ?>
          <a href="criar.php" class="btn btn-sm btn-outline-primary mt-2">Registar primeiro contrato</a>
        <?php endif; ?>
      </div>
    <?php else: ?>

      <!-- Tabela Responsiva com scroll automático -->
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Nº Contrato</th>
              <th>Tipo</th>
              <th>Equipamento</th>
              <th>Fornecedor</th>
              <th>Início</th>
              <th>Fim</th>
              <th>Valor</th>
              <th>Estado</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contratos as $cont): ?>
              <?php
                $data_fim = new DateTimeImmutable($cont['data_fim']);
                $estado   = estado_contracto($data_fim, $hoje, $em_30_dias);
              ?>
              <tr>
                <!-- Número do Contrato -->
                <td class="fw-bold"><?php echo htmlspecialchars($cont['numero_contrato'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- Tipo de Contrato -->
                <td>
                  <span class="badge bg-info text-dark">
                    <?php echo TIPOS_CONTRATO[$cont['tipo']] ?? htmlspecialchars($cont['tipo']); ?>
                  </span>
                </td>

                <!-- Equipamento associado (JOIN) -->
                <td>
                  <a href="../equipamentos/ver.php?id=<?php echo (int) $cont['equipamento_id']; ?>" class="text-info text-decoration-none">
                    <span class="badge bg-dark border border-secondary text-white me-1"><?php echo htmlspecialchars($cont['equipamento_codigo'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <small><?php echo htmlspecialchars($cont['equipamento_designacao'], ENT_QUOTES, 'UTF-8'); ?></small>
                  </a>
                </td>

                <!-- Fornecedor associado (JOIN) -->
                <td>
                  <?php if (!empty($cont['fornecedor_nome'])): ?>
                    <a href="../fornecedores/editar.php?id=<?php echo (int) $cont['fornecedor_id']; ?>" class="text-info text-decoration-none">
                      <?php echo htmlspecialchars($cont['fornecedor_nome'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>

                <!-- Data de Início -->
                <td>
                  <?php echo !empty($cont['data_inicio']) ? (new DateTimeImmutable($cont['data_inicio']))->format('d/m/Y') : '—'; ?>
                </td>

                <!-- Data de Fim -->
                <td>
                  <?php echo $data_fim->format('d/m/Y'); ?>
                </td>

                <!-- Valor formatado -->
                <td class="text-nowrap">
                  <?php echo !empty($cont['valor']) ? number_format((float) $cont['valor'], 2, ',', '.') . ' €' : '—'; ?>
                </td>

                <!-- Badge de Estado vindo da função PHP -->
                <td>
                  <span class="badge <?php echo $estado['classe']; ?>">
                    <i class="bi <?php echo $estado['icone']; ?> me-1"></i>
                    <?php echo $estado['label']; ?>
                  </span>
                </td>

                <!-- Grupo de ações comprimido e alinhado -->
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="editar.php?id=<?php echo (int) $cont['id']; ?>" class="btn btn-outline-warning" title="Editar contrato">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="eliminar.php?id=<?php echo (int) $cont['id']; ?>" class="btn btn-outline-danger" title="Eliminar contrato">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ── Paginação Alinhada ao Centro ── -->
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

</main> <!-- Fecho seguro da página-contratos -->
<?php include '../../includes/footer.php'; ?>