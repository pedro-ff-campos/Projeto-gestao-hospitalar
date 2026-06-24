<?php

declare(strict_types=1);

// 1. Variável para o header carregar o CSS a partir desta subpasta
$prefixo = '../../';


require_once '../../includes/auth.php';   
require_once '../../includes/db.php';     

// ── Variáveis para o header ──────────────────────────────────────────────────
$titulo_pagina = 'Documentação';
$modulo_ativo  = 'documentacao';

// ── Tipos de documento válidos  ─────
const TIPOS_DOCUMENTO = [
    'manual'      => 'Manual Técnico',
    'certificado' => 'Certificado / Calibração',
    'fatura'      => 'Fatura / Compra',
    'outro'       => 'Outro Documento',
];

// ── Parâmetros de pesquisa e filtro  ──────
$pesquisa       = trim($_GET['pesquisa'] ?? '');
$tipo           = trim($_GET['tipo'] ?? '');
$equipamento_id = max(0, (int)($_GET['equipamento_id'] ?? 0));

// Validar se o tipo inserido no URL é reconhecido
if (!array_key_exists($tipo, TIPOS_DOCUMENTO)) {
    $tipo = '';
}

// ── Paginação Básica ─────────────────────────────────────────────────────────
$registos_por_pagina = 10;
$pagina_atual        = max(1, (int)($_GET['pagina'] ?? 1));
$offset              = ($pagina_atual - 1) * $registos_por_pagina;

// ── Construção dinâmica das condições WHERE  ─────
$sql_where = " WHERE 1=1";
$params = [];

if ($pesquisa !== '') {
    $sql_where .= ' AND (d.titulo LIKE :pesquisa OR e.designacao LIKE :pesquisa)';
    $params[':pesquisa'] = '%' . $pesquisa . '%';
}

if ($tipo !== '') {
    $sql_where .= ' AND d.tipo = :tipo';
    $params[':tipo'] = $tipo;
}

if ($equipamento_id > 0) {
    $sql_where .= ' AND d.equipamento_id = :equipamento_id';
    $params[':equipamento_id'] = $equipamento_id;
}

// ── Query 1: Contar total de resultados  ──────────
try {
    $sql_count = "
        SELECT COUNT(*) 
        FROM documentacao d
        INNER JOIN equipamentos e ON e.id = d.equipamento_id
        $sql_where
    ";

    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total_registos = (int)$stmt_count->fetchColumn();

} catch (PDOException $e) {
    $total_registos = 0;
}

$total_paginas = max(1, (int)ceil($total_registos / $registos_por_pagina));
// ── Query 2: Obter os Documentos com JOIN, filtros e paginação ────────────────
try {
    $sql = "
        SELECT
            d.id,
            d.titulo,
            d.tipo,
            d.data_documento,
            d.data_validade,
            d.ficheiro,
            e.id          AS equipamento_id,
            e.codigo      AS equipamento_codigo,
            e.designacao  AS equipamento_designacao
        FROM documentacao d
        INNER JOIN equipamentos e ON e.id = d.equipamento_id
        $sql_where
        ORDER BY d.data_documento DESC, d.titulo ASC
        LIMIT $registos_por_pagina OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documentos = $stmt->fetchAll();

} catch (PDOException $e) {
    $documentos = [];
}

// ── Query 3: Lista de equipamentos para preencher o filtro Dropdown ──────────
try {
    $equipamentos_lista = $pdo
        ->query('SELECT id, codigo, designacao FROM equipamentos ORDER BY designacao ASC')
        ->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── Query string para a paginação (mantém os filtros ativos ao mudar de página) ──
$qs_filtros = http_build_query(array_filter([
    'pesquisa'       => $pesquisa,
    'tipo'           => $tipo,
    'equipamento_id' => $equipamento_id ?: '',
]));

require_once '../../includes/header.php';
?>


<!-- ════════════ CONTEÚDO ════════════ -->

<main class="pagina-documentacao container-fluid py-4">

  <!-- ── Cabeçalho da página ── -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Documentação</h1>
    <a href="criar.php" class="btn btn-success">
      <i class="bi bi-plus-lg"></i> Novo Documento
    </a>
  </div>

  <!-- ── Mensagens de feedback ── -->
  <?php if (isset($_GET['sucesso'])): ?>
    <?php
      $mensagens_sucesso = [
        'criado'    => 'Documento registado com sucesso.',
        'editado'   => 'Documento atualizado com sucesso.',
        'eliminado' => 'Documento eliminado com sucesso.',
      ];
      $chave = htmlspecialchars($_GET['sucesso'], ENT_QUOTES, 'UTF-8');
      $msg_ok = $mensagens_sucesso[$chave] ?? 'Operação realizada com sucesso.';
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

  <!-- ── Filtros  ── -->
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
            placeholder="Título do documento ou equipamento…"
            value="<?php echo $pesquisa; ?>"
          />
        </div>

        <!-- Filtro por tipo de documento -->
        <div class="col-md-4">
          <label for="tipo" class="form-label">Tipo de Documento</label>
          <select id="tipo" name="tipo" class="form-select">
            <option value="">Todos os tipos</option>
            <?php foreach (TIPOS_DOCUMENTO as $valor => $label): ?>
              <option value="<?php echo $valor; ?>" <?php echo $tipo === $valor ? 'selected' : ''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filtro por equipamento -->
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

      <!-- Botões de Ação dos Filtros -->
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
  <!-- ── Listagem (Bloco de cartão integrado no tema) ── -->
  <section class="card text-white p-4">

    <h2 class="mb-3">
      Lista de Documentos
      <span class="badge bg-secondary ms-2">
        <?php echo $total_registos; ?> <?php echo $total_registos === 1 ? 'resultado' : 'resultados'; ?>
      </span>
    </h2>

    <?php if (empty($documentos)): ?>
      <!-- Estado vazio  -->
      <div class="text-center py-5 text-muted">
        <i class="bi bi-file-earmark-text h1"></i>
        <p class="mt-2">Nenhum documento encontrado.</p>
        <?php if ($pesquisa !== '' || $tipo !== '' || $equipamento_id > 0): ?>
          <a href="index.php" class="btn btn-sm btn-outline-light mt-2">Limpar filtros e ver todos</a>
        <?php else: ?>
          <a href="criar.php" class="btn btn-sm btn-outline-primary mt-2">Registar primeiro documento</a>
        <?php endif; ?>
      </div>
    <?php else: ?>

      <!-- Tabela com scroll horizontal automático  -->
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Título</th>
              <th>Tipo</th>
              <th>Equipamento</th>
              <th>Data</th>
              <th>Validade</th>
              <th>Ficheiro</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documentos as $doc): ?>
              <?php
                $hoje           = new DateTimeImmutable('today');
                $em_30_dias     = $hoje->modify('+30 days');
                $data_validade  = !empty($doc['data_validade']) ? new DateTimeImmutable($doc['data_validade']) : null;

                // Cores automáticas do Bootstrap para os prazos de validade
                $badge_validade = 'bg-secondary';
                $validade_label = '—';
                $icone_alerta   = '';

                if ($data_validade !== null) {
                    $validade_label = $data_validade->format('d/m/Y');
                    if ($data_validade < $hoje) {
                        $badge_validade = 'bg-danger text-white'; // Vermelho se expirou
                        $icone_alerta = '<i class="bi bi-exclamation-circle me-1" title="Documento expirado"></i>';
                    } elseif ($data_validade <= $em_30_dias) {
                        $badge_validade = 'bg-warning text-dark';  // Amarelo se faltam menos de 30 dias
                        $icone_alerta = '<i class="bi bi-clock-history me-1" title="Validade próxima"></i>';
                    }
                }
              ?>
              <tr>
                <!-- Título -->
                <td class="fw-bold"><?php echo htmlspecialchars($doc['titulo'], ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- Tipo de Documento -->
                <td>
                  <span class="badge bg-info text-dark">
                    <?php echo TIPOS_DOCUMENTO[$doc['tipo']] ?? htmlspecialchars($doc['tipo']); ?>
                  </span>
                </td>

                <!-- Link limpo para ver o equipamento associado -->
                <td>
                  <a href="../equipamentos/ver.php?id=<?php echo (int) $doc['equipamento_id']; ?>" class="text-info text-decoration-none">
                    <span class="badge bg-dark border border-secondary text-white me-1"><?php echo htmlspecialchars($doc['equipamento_codigo'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <small><?php echo htmlspecialchars($doc['equipamento_designacao'], ENT_QUOTES, 'UTF-8'); ?></small>
                  </a>
                </td>

                <!-- Data do documento -->
                <td>
                  <?php echo !empty($doc['data_documento']) ? (new DateTimeImmutable($doc['data_documento']))->format('d/m/Y') : '—'; ?>
                </td>

                <!-- Validade com o indicador visual em badge -->
                <td>
                  <span class="badge <?php echo $badge_validade; ?>">
                    <?php echo $icone_alerta . $validade_label; ?>
                  </span>
                </td>

                <!-- Botão discreto para descarregar ou abrir o ficheiro -->
                <td>
                  <?php if (!empty($doc['ficheiro'])): ?>
                    <a href="../../assets/docs/<?php echo htmlspecialchars($doc['ficheiro'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.8rem;">
                      <i class="bi bi-file-earmark-arrow-down"></i> Abrir
                    </a>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>

                <!-- Botões de ação em grupo comprimido -->
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="editar.php?id=<?php echo (int) $doc['id']; ?>" class="btn btn-outline-warning" title="Editar documento">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="eliminar.php?id=<?php echo (int) $doc['id']; ?>" class="btn btn-outline-danger" title="Eliminar documento">
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

</main> 
<?php include '../../includes/footer.php'; ?>