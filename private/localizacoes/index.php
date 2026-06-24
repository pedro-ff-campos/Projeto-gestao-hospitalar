<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz do projeto e carregar o CSS
$prefixo = '../../';


require_once '../../includes/auth.php';   
require_once '../../includes/db.php';     

// ── Variáveis para o header ──────────────────────────────────────────────────
$titulo_pagina = 'Localizações';
$modulo_ativo  = 'localizacoes';

// ── Parâmetros de pesquisa e filtro (vindos do GET) ──────────────────────────
$pesquisa = trim($_GET['pesquisa'] ?? '');
$tipo     = trim($_GET['tipo'] ?? '');

// ── Paginação Básica ─────────────────────────────────────────────────────────
$registos_por_pagina = 10;
$pagina_atual = max(1, (int) ($_GET['pagina'] ?? 1));
$offset       = ($pagina_atual - 1) * $registos_por_pagina;

// ── Query 1: Contar total de resultados (Necessário para a paginação) ────────
try {
    $sql_count = 'SELECT COUNT(*) FROM localizacoes WHERE 1=1';
    $params_count = [];

    if ($pesquisa !== '') {
        $sql_count .= ' AND (designacao LIKE :pesquisa OR descricao LIKE :pesquisa)';
        $params_count[':pesquisa'] = '%' . $pesquisa . '%';
    }

    if ($tipo !== '') {
        $sql_count .= ' AND tipo = :tipo';
        $params_count[':tipo'] = $tipo;
    }

    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params_count);
    $total_registos = (int) $stmt_count->fetchColumn();

} catch (PDOException $e) {
    $total_registos = 0;
}

$total_paginas = max(1, (int) ceil($total_registos / $registos_por_pagina));

// ── Query 2: Obter as Localizações (Regra: Consulta direta e limpa) ──────────
try {
    $sql = '
        SELECT 
            l.id, 
            l.designacao, 
            l.tipo, 
            l.descricao,
            (SELECT COUNT(*) FROM equipamentos e WHERE e.localizacao_id = l.id) AS total_equipamentos
        FROM localizacoes l 
        WHERE 1=1
    ';
    $params = [];

    if ($pesquisa !== '') {
        $sql .= ' AND (l.designacao LIKE :pesquisa OR l.descricao LIKE :pesquisa)';
        $params[':pesquisa'] = '%' . $pesquisa . '%';
    }

    if ($tipo !== '') {
        $sql .= ' AND l.tipo = :tipo';
        $params[':tipo'] = $tipo;
    }

    // Ordenação e paginação direta sem loops de bindValue
    $sql .= " ORDER BY l.tipo ASC, l.designacao ASC LIMIT $registos_por_pagina OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $localizacoes = $stmt->fetchAll();

} catch (PDOException $e) {
    $localizacoes = [];
}

// ── Labels legíveis para os tipos de localização ─────────────────────────────
$labels_tipo = [
    'edificio' => 'Edifício',
    'piso'     => 'Piso',
    'servico'  => 'Serviço',
    'sala'     => 'Sala',
];


require_once '../../includes/header.php';
?>


<!-- ════════════ CONTEÚDO ════════════ -->
<main class="pagina-localizacoes container-fluid py-4">

  <!-- ── Cabeçalho da página ── -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Localizações</h1>
    <a href="criar.php" class="btn btn-success">
      <i class="bi bi-plus-lg"></i> Nova Localização
    </a>
  </div>

  <!-- ── Mensagens de feedback ── -->
  <?php if (isset($_GET['sucesso'])): ?>
    <?php
      $mensagens_sucesso = [
        'criada'    => 'Localização criada com sucesso.',
        'editada'   => 'Localização atualizada com sucesso.',
        'eliminada' => 'Localização eliminada com sucesso.',
      ];
      $chave_sucesso = htmlspecialchars($_GET['sucesso'], ENT_QUOTES, 'UTF-8');
      $msg_sucesso   = $mensagens_sucesso[$chave_sucesso] ?? 'Operação realizada com sucesso.';
    ?>
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-check-circle me-2"></i>
      <div><?php echo $msg_sucesso; ?></div>
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
        <div class="col-md-6">
          <label for="pesquisa" class="form-label">Pesquisar</label>
          <input
            type="text"
            id="pesquisa"
            name="pesquisa"
            class="form-control"
            placeholder="Nome ou descrição da localização…"
            value="<?php echo $pesquisa; ?>"
          />
        </div>

        <!-- Filtro por tipo -->
        <div class="col-md-6">
          <label for="tipo" class="form-label">Tipo</label>
          <select id="tipo" name="tipo" class="form-select">
            <option value="">Todos os tipos</option>
            <?php foreach ($labels_tipo as $valor => $label): ?>
              <option value="<?php echo $valor; ?>" <?php echo $tipo === $valor ? 'selected' : ''; ?>>
                <?php echo $label; ?>
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
      Lista de Localizações
      <span class="badge bg-secondary ms-2">
        <?php echo $total_registos; ?> <?php echo $total_registos === 1 ? 'resultado' : 'resultados'; ?>
      </span>
    </h2>

    <?php if (empty($localizacoes)): ?>
      <!-- Estado vazio -->
      <div class="text-center py-5 text-muted">
        <i class="bi bi-geo-alt h1"></i>
        <p class="mt-2">Nenhuma localização encontrada.</p>
        <a href="criar.php" class="btn btn-sm btn-outline-primary mt-2">Criar primeira localização</a>
      </div>
    <?php else: ?>

      <!-- Tabela  -->
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Designação</th>
              <th>Tipo</th>
              <th>Descrição</th>
              <th class="text-center">Equipamentos</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($localizacoes as $loc): ?>
              <tr>
                <td class="fw-bold"><?php echo htmlspecialchars($loc['designacao'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                  <span class="badge bg-info text-dark">
                    <?php echo $labels_tipo[$loc['tipo']] ?? htmlspecialchars($loc['tipo']); ?>
                  </span>
                </td>
                <td>
                  <?php
                    $descricao = $loc['descricao'] ?? '';
                    echo htmlspecialchars(
                        mb_strlen($descricao) > 80 ? mb_substr($descricao, 0, 80) . '…' : $descricao,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                  ?>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary"><?php echo (int) $loc['total_equipamentos']; ?></span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="editar.php?id=<?php echo (int) $loc['id']; ?>" class="btn btn-outline-warning" title="Editar localização">
                      <i class="bi bi-pencil"></i>
                    </a>
                    
                    <?php if ((int) $loc['total_equipamentos'] === 0): ?>
                      <a href="eliminar.php?id=<?php echo (int) $loc['id']; ?>" class="btn btn-outline-danger" title="Eliminar localização">
                        <i class="bi bi-trash"></i>
                      </a>
                    <?php else: ?>
                      <span class="btn btn-outline-secondary disabled" data-bs-toggle="tooltip" data-bs-placement="top" title="Não é possível eliminar: existem <?php echo (int) $loc['total_equipamentos']; ?> equipamento(s) associado(s)">
                        <i class="bi bi-trash"></i>
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ── Paginação  ── -->
      <?php if ($total_paginas > 1): ?>
        <nav class="d-flex justify-content-center mt-4" aria-label="Navegação de páginas">
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

</main>
