<?php
declare(strict_types=1);

// ── Includes obrigatórios (Regra de Segurança da pasta private/) ─────────────

$prefixo = '../../'; // Prefixo para os includes

require_once '../../includes/auth.php';
require_once '../../includes/db.php';     

// ── Variáveis para o header ──────────────────────────────────────────────────
$titulo_pagina = 'Fornecedores';
$modulo_ativo  = 'fornecedores';

// ── Parâmetros de pesquisa e filtro (vindos do GET) ──────────────────────────
$pesquisa = trim($_GET['pesquisa'] ?? '');
$tipo     = trim($_GET['tipo'] ?? '');

// ── Paginação Básica ─────────────────────────────────────────────────────────
$registos_por_pagina = 10;
$pagina_atual = max(1, (int) ($_GET['pagina'] ?? 1));
$offset       = ($pagina_atual - 1) * $registos_por_pagina;

// ── Query 1: Contar total de resultados  ────────
try {
    $sql_count = 'SELECT COUNT(*) FROM fornecedores WHERE 1=1';
    $params_count = [];

    if ($pesquisa !== '') {
        $sql_count .= ' AND (nome LIKE :pesquisa OR nif LIKE :pesquisa)';
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

// ── Query 2: Obter os Fornecedores ─────────────
try {
    $sql = 'SELECT id, nome, contacto, nif, tipo FROM fornecedores WHERE 1=1';
    $params = [];

    if ($pesquisa !== '') {
        $sql .= ' AND (nome LIKE :pesquisa OR nif LIKE :pesquisa)';
        $params[':pesquisa'] = '%' . $pesquisa . '%';
    }

    if ($tipo !== '') {
        $sql .= ' AND tipo = :tipo';
        $params[':tipo'] = $tipo;
    }

    // Ordenação simples e paginação direta
    $sql .= " ORDER BY nome ASC LIMIT $registos_por_pagina OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fornecedores = $stmt->fetchAll();

} catch (PDOException $e) {
    $fornecedores = [];
}

// ── Labels para os tipos de fornecedor ───────────────────────────────────────
$labels_tipo = [
    'equipamentos' => 'Manutenção / Equipamentos',
    'consumiveis'  => 'Consumíveis Médicos',
    'software'     => 'Sistemas / TI',
];

require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO ════════════ -->
<main class="pagina-fornecedores container-fluid py-4">

  <!-- ── Cabeçalho da página ── -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Fornecedores</h1>
    <a href="criar.php" class="btn btn-success">
      <i class="bi bi-plus-lg"></i> Novo Fornecedor
    </a>
  </div>

  <!-- ── Mensagens de feedback ── -->
  <?php if (isset($_GET['sucesso'])): ?>
    <?php
      $mensagens_sucesso = [
        'criado'    => 'Fornecedor criado com sucesso.',
        'editado'   => 'Fornecedor atualizado com sucesso.',
        'eliminado' => 'Fornecedor eliminado com sucesso.',
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

  <!-- ── Filtros ── -->
  <section class="card text-white p-4 mb-4">
    <h2 class="h5 mb-3">Pesquisa e Filtros</h2>

    <form method="GET" action="index.php">
      <div class="row g-3">
        <!-- Pesquisa por texto -->
        <div class="col-md-6">
          <label for="pesquisa" class="form-label">Pesquisar</label>
          <input
            type="text"
            id="pesquisa"
            name="pesquisa"
            class="form-control bg-secondary text-white border-0"
            placeholder="Nome, NIF ou contacto…"
            value="<?php echo $pesquisa; ?>"
          />
        </div>

        <!-- Filtro por tipo -->
        <div class="col-md-6">
          <label for="tipo" class="form-label">Tipo de Serviço</label>
          <select id="tipo" name="tipo" class="form-select bg-secondary text-white border-0">
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
  <section class="card bg-dark text-white p-4 border-secondary">

    <h2 class="h5 mb-3">
      Lista de Fornecedores
      <span class="badge bg-secondary ms-2">
        <?php echo $total_registos; ?> <?php echo $total_registos === 1 ? 'resultado' : 'resultados'; ?>
      </span>
    </h2>

    <?php if (empty($fornecedores)): ?>
      <!-- Estado vazio -->
      <div class="text-center py-5 text-muted">
        <i class="bi bi-building h1"></i>
        <p class="mt-2">Nenhum fornecedor encontrado.</p>
        <a href="criar.php" class="btn btn-sm btn-outline-primary mt-2">Criar primeiro fornecedor</a>
      </div>
    <?php else: ?>

      <!-- Tabela nativa do Bootstrap -->
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Nome / Empresa</th>
              <th>Contacto</th>
              <th>NIF / Tipo</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fornecedores as $forn): ?>
              <tr>
                <td class="fw-bold"><?php echo htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($forn['contacto'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                  <span class="badge bg-info text-dark">
                    <?php echo htmlspecialchars($forn['tipo']); ?>
                  </span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="editar.php?id=<?php echo (int) $forn['id']; ?>" class="btn btn-outline-warning" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="eliminar.php?id=<?php echo (int) $forn['id']; ?>" class="btn btn-outline-danger" title="Eliminar">
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
            <!-- Anterior -->
            <li class="page-item <?php echo $pagina_atual <= 1 ? 'disabled' : ''; ?>">
              <a class="page-item" href="?pagina=<?php echo $pagina_atual - 1; ?>">Anterior</a>
            </li>
            <!-- Atual -->
            <li class="page-item active">
              <span class="page-link bg-primary border-primary"><?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
            </li>
            <!-- Próxima -->
            <li class="page-item <?php echo $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
              <a class="page-item" href="?pagina=<?php echo $pagina_atual + 1; ?>">Próxima</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    <?php endif; ?>

  </section>

</main>
