<!-- Página para os equipamentos -->
<!-- ========== CONTEÚDO PRINCIPAL ========== -->

<?php 

$prefixo = '../../'; // Define o prefixo para os includes (ajusta conforme a estrutura de pastas)

// 1. CARREGAR O HEADER PRIVADO (Trata da Sessão, Bootstrap e liga a variável $pdo da BD)

require_once '../../includes/header.php';
require_once '../../includes/db.php';
// 2. PAGINAÇÃO (Configuração básica)
$itens_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) $pagina_atual = 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// 3. CAPTURAR OS FILTROS DO FORMULÁRIO
$pesquisa    = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
$estado      = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$criticidade = isset($_GET['criticidade']) ? trim($_GET['criticidade']) : '';
$localizacao = isset($_GET['localizacao']) ? trim($_GET['localizacao']) : '';

// 4. CONSTRUIR A QUERY DINÂMICA (Prepara os filtros para a Base de Dados)
$sql = "FROM equipamentos WHERE 1=1";
$params = [];

if ($pesquisa !== '') {
    $sql .= " AND (designacao LIKE ? OR marca LIKE ? OR modelo LIKE ? OR numero_serie LIKE ?)";
    $params[] = "%$pesquisa%"; $params[] = "%$pesquisa%"; $params[] = "%$pesquisa%"; $params[] = "%$pesquisa%";
}

if ($estado !== '') {
    $sql .= " AND estado = ?";
    $params[] = $estado;
}

if ($criticidade !== '') {
    $sql .= " AND criticidade = ?";
    $params[] = $criticidade;
}

if ($localizacao !== '') {
    $sql .= " AND localizacao_id = ?";
    $params[] = $localizacao;
}

// 5. CONTAR TOTAL DE RESULTADOS (Para alimentar o teu contador e paginação no HTML)
$sql_total = "SELECT COUNT(*) " . $sql;
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$total_resultados = $stmt_total->fetchColumn();
$total_paginas = ceil($total_resultados / $itens_por_pagina);
if ($total_paginas < 1) $total_paginas = 1;

// Configurar variáveis para os botões do teu HTML
$pagina_anterior  = $pagina_atual - 1;
$pagina_seguinte  = $pagina_atual + 1;

// 6. PROCURAR OS EQUIPAMENTOS DA PÁGINA ATUAL (Usa a variável $pdo herdada do teu header)
$sql_dados = "SELECT * " . $sql . " LIMIT $itens_por_pagina OFFSET $offset";
$stmt_dados = $pdo->prepare($sql_dados);
$stmt_dados->execute($params);
$equipamentos = $stmt_dados->fetchAll(PDO::FETCH_ASSOC);

// 7. PROCURAR LOCALIZAÇÕES PARA O TEU SELECT DINÂMICO
$stmt_loc = $pdo->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico ASC, sala ASC");
$localizacoes = $stmt_loc->fetchAll(PDO::FETCH_ASSOC);

?>


<main>
 
    <!-- ── Cabeçalho da página ── -->
    <div class="page-header">
      <h1>Equipamentos</h1>
      <a href="criar.php" class="btn-novo"><i class="fa-solid fa-plus"></i> Novo Equipamento</a>
    </div>
 
    <!-- ── Mensagens de feedback (preenchidas pelo PHP após operações CRUD) ── -->
    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alerta alerta-sucesso">
        <?php
          $mensagens = [
            'criado'    => 'Equipamento registado com sucesso.',
            'editado'   => 'Equipamento atualizado com sucesso.',
            'eliminado' => 'Equipamento eliminado com sucesso.',
          ];
          $chave = htmlspecialchars($_GET['sucesso']);
          echo $mensagens[$chave] ?? 'Operação realizada com sucesso.';
        ?>
      </div>
    <?php endif; ?>
 
    <?php if (isset($_GET['erro'])): ?>
      <div class="alerta alerta-erro">
        Ocorreu um erro. Por favor tente novamente.
      </div>
    <?php endif; ?>
 
    <!-- ── Secção de pesquisa e filtros ── -->
    <section class="secao-filtros">
      <h2>Pesquisa e Filtros</h2>
 
      <form method="GET" action="index.php">
 
        <div class="filtros-grid">
 
          <!-- Pesquisa por texto livre -->
          <div class="campo-filtro">
            <label for="pesquisa">Pesquisar</label>
            <input
              type="text"
              id="pesquisa"
              name="pesquisa"
              placeholder="Designação, marca, modelo ou nº série…"
              value="<?php echo htmlspecialchars($_GET['pesquisa'] ?? ''); ?>"
            />
          </div>
 
          <!-- Filtro por estado -->
          <div class="campo-filtro">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
              <option value="">Todos os Estados</option>
              <option value="ativo"       <?php echo (($_GET['estado'] ?? '') === 'ativo')       ? 'selected' : ''; ?>>Ativo</option>
              <option value="manutencao"  <?php echo (($_GET['estado'] ?? '') === 'manutencao')  ? 'selected' : ''; ?>>Em Manutenção</option>
              <option value="calibracao"  <?php echo (($_GET['estado'] ?? '') === 'calibracao')  ? 'selected' : ''; ?>>Em Calibração</option>
              <option value="inativo"     <?php echo (($_GET['estado'] ?? '') === 'inativo')     ? 'selected' : ''; ?>>Inativo</option>
              <option value="abatido"     <?php echo (($_GET['estado'] ?? '') === 'abatido')     ? 'selected' : ''; ?>>Abatido</option>
            </select>
          </div>
 
          <!-- Filtro por criticidade -->
          <div class="campo-filtro">
            <label for="criticidade">Criticidade</label>
            <select id="criticidade" name="criticidade">
              <option value="">Todas as Criticidades</option>
              <option value="vida"  <?php echo (($_GET['criticidade'] ?? '') === 'vida')  ? 'selected' : ''; ?>>Suporte de Vida</option>
              <option value="alta"  <?php echo (($_GET['criticidade'] ?? '') === 'alta')  ? 'selected' : ''; ?>>Alta</option>
              <option value="media" <?php echo (($_GET['criticidade'] ?? '') === 'media') ? 'selected' : ''; ?>>Média</option>
              <option value="baixa" <?php echo (($_GET['criticidade'] ?? '') === 'baixa') ? 'selected' : ''; ?>>Baixa</option>
            </select>
          </div>
 
          <!-- Filtro por localização -->
          <div class="campo-filtro">
            <label for="localizacao">Localização / Serviço</label>
            <select id="localizacao" name="localizacao">
                <option value="">Todas as Localizações</option>
                <?php foreach ($localizacoes as $loc): ?>
                    <option value="<?php echo $loc['id']; ?>"
                        <?php echo (($_GET['localizacao'] ?? '') == $loc['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc['servico'] . ' - ' . $loc['sala']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
          </div>
 
        </div><!-- /filtros-grid -->
 
        <div class="filtros-acoes">
          <button type="submit">Pesquisar</button>
          <a href="index.php" class="btn-limpar">Limpar Filtros</a>
        </div>
 
      </form>
    </section>
 
    <!-- ── Secção de listagem ── -->
    <section class="secao-listagem">
 
      <h2>
        Lista de Equipamentos
        <?php if (!empty($equipamentos)): ?>
          <span class="contador">(<?php echo count($equipamentos); ?> resultados)</span>
        <?php endif; ?>
      </h2>
 
      <table class="tabela-med">
        <thead>
          <tr>
            <th>Código</th>
            <th>Designação</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Nº Série</th>
            <th>Serviço / Localização</th>
            <th>Estado</th>
            <th>Criticidade</th>
            <th class="text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($equipamentos)): ?>
            <tr>
              <td colspan="9" class="tabela-vazia">
                Nenhum equipamento encontrado.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($equipamentos as $eq): ?>
              <tr>
                <td class="eq-codigo"><?php echo htmlspecialchars($eq['codigo']); ?></td>
                <td><strong><?php echo htmlspecialchars($eq['designacao']); ?></strong></td>
                <td><?php echo htmlspecialchars($eq['marca']); ?></td>
                <td><?php echo htmlspecialchars($eq['modelo']); ?></td>
                <td class="text-muted"><?php echo htmlspecialchars($eq['numero_serie']); ?></td>
                <td><i class="fa-solid fa-location-dot" style="color: #64748b; margin-right: 4px;"></i> <?php echo htmlspecialchars($eq['localizacao']); ?></td>
                <td>
                  <span class="badge badge-<?php echo strtolower($eq['estado']); ?>">
                    <?php echo ucfirst($eq['estado']); ?>
                  </span>
                </td>
                <td>
                  <span class="badge-crit badge-crit-<?php echo strtolower($eq['criticidade']); ?>">
                    <?php echo ucfirst($eq['criticidade']); ?>
                  </span>
                </td>
                <td class="acoes-tabela">
                  <a href="ver.php?id=<?php echo $eq['id']; ?>" title="Ver Detalhes" class="btn-acao-ver"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?php echo $eq['id']; ?>" title="Editar" class="btn-acao-editar"><i class="fa-solid fa-pen"></i></a>
                  <a href="eliminar.php?id=<?php echo $eq['id']; ?>" class="acao-eliminar" title="Eliminar"><i class="fa-solid fa-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
 
      <!-- Paginação ativa pelo PHP -->
      <div class="paginacao">
        <a href="?pagina=<?php echo $pagina_anterior; ?>" class="<?php echo ($pagina_atual <= 1) ? 'disabled' : ''; ?>">← Anterior</a>
        <span>Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
        <a href="?pagina=<?php echo $pagina_seguinte; ?>" class="<?php echo ($pagina_atual >= $total_paginas) ? 'disabled' : ''; ?>">Próxima →</a>
      </div>
 
    </section>
 
</main>

<?php include '../../includes/footer.php'; ?>