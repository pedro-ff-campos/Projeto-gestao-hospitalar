<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o teu CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
// require_once '../../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Novo Contrato';
$modulo_ativo  = 'contratos';

$erro_mensagem = '';

// ── Query 1: Carregar Equipamentos para o Dropdown ───────────────────────────
try {
    $stmt_eq = $pdo->query("SELECT id, codigo, designacao FROM equipamentos ORDER BY codigo ASC");
    $equipamentos_lista = $stmt_eq->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── Query 2: Carregar Fornecedores para o Dropdown ───────────────────────────
try {
    $stmt_forn = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC");
    $fornecedores_lista = $stmt_forn->fetchAll();
} catch (PDOException $e) {
    $fornecedores_lista = [];
}

// ── Processamento do Formulário (Quando o utilizador clica em Guardar) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento       = (int)($_POST['id_equipamento'] ?? 0);
    $id_fornecedor        = $_POST['id_fornecedor'] !== '' ? (int)$_POST['id_fornecedor'] : null;
    $numero_contrato      = trim($_POST['numero_contrato'] ?? '');
    $tipo                 = trim($_POST['tipo'] ?? '');
    $entidade_responsavel = trim($_POST['entidade_responsavel'] ?? '');
    $periodicidade        = trim($_POST['periodicidade'] ?? '');
    $data_inicio          = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $data_fim             = !empty($_POST['data_fim']) ? $_POST['data_fim'] : '';
    $valor                = $_POST['valor'] !== '' ? (float)$_POST['valor'] : null;
    $observacoes          = trim($_POST['observacoes'] ?? '');

    // Validação de campos obrigatórios conforme as restrições do teu SQL
    if ($id_equipamento === 0 || $numero_contrato === '' || $data_fim === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios (Equipamento, Nº Contrato e Data de Fim).';
    } else {
        try {
            // Inserção segura respeitando as colunas exatas da tua tabela contratos
            $sql = 'INSERT INTO contratos (id_equipamento, id_fornecedor, numero_contrato, tipo, entidade_responsavel, periodicidade, data_inicio, data_fim, valor, observacoes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_equipamento, $id_fornecedor, $numero_contrato, $tipo, $entidade_responsavel, $periodicidade, $data_inicio, $data_fim, $valor, $observacoes]);

            // Redireciona de volta para a listagem principal com sucesso
            header('Location: index.php?sucesso=criado');
            exit;

        } catch (PDOException $e) {
            $erro_mensagem = 'Não foi possível registar o contrato. Por favor, tente novamente.';
        }
    }
}

// ── Incluir o header (abre a barra lateral e o layout automaticamente) ────────
require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<main class="pagina-contratos container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Registar Novo Contrato de Manutenção</h1>
    <a href="index.php" class="btn btn-outline-light">
      <i class="bi bi-arrow-left"></i> Voltar à Lista
    </a>
  </div>

  <!-- Mensagem de Erro Visual -->
  <?php if ($erro_mensagem !== ''): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?php echo htmlspecialchars($erro_mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
  <?php endif; ?>

  <!-- Card do Formulário (Herda o teu estilo escuro de forma automática) -->
  <div class="card text-white p-4">
    <form method="POST" action="criar.php">
      <div class="row g-3">
        
        <!-- Campo: Número do Contrato -->
        <div class="col-md-4">
          <label for="numero_contrato" class="form-label">Número do Contrato <span class="text-danger">*</span></label>
          <input type="text" id="numero_contrato" name="numero_contrato" class="form-control" placeholder="Ex: CTR-2026-0045" required>
        </div>

        <!-- Campo Dinâmico: Seleção de Equipamento (Lê da BD) -->
        <div class="col-md-8">
          <label for="id_equipamento" class="form-label">Equipamento Vinculado <span class="text-danger">*</span></label>
          <select id="id_equipamento" name="id_equipamento" class="form-select" required>
            <option value="" disabled selected>Escolha o aparelho...</option>
            <?php foreach ($equipamentos_lista as $eq): ?>
              <option value="<?php echo $eq['id']; ?>">
                <?php echo htmlspecialchars($eq['codigo'] . ' — ' . $eq['designacao'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo Dinâmico: Seleção de Fornecedor (Lê da BD) -->
        <div class="col-md-6">
          <label for="id_fornecedor" class="form-label">Empresa / Fornecedor Contratado</label>
          <select id="id_fornecedor" name="id_fornecedor" class="form-select">
            <option value="">Nenhum fornecedor associado (Opcional)</option>
            <?php foreach ($fornecedores_lista as $forn): ?>
              <option value="<?php echo $forn['id']; ?>">
                <?php echo htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Tipo de Contrato -->
        <div class="col-md-6">
          <label for="tipo" class="form-label">Tipo de Contrato</label>
          <select id="tipo" name="tipo" class="form-select">
            <option value="manutencao_preventiva">Manutenção Preventiva</option>
            <option value="manutencao_corretiva">Manutenção Corretiva</option>
            <option value="calibracao">Calibração</option>
            <option value="assistencia_tecnica">Assistência Técnica</option>
            <option value="full_service">Full Service</option>
            <option value="outro">Outro</option>
          </select>
        </div>

        <!-- Campo: Entidade Responsável -->
        <div class="col-md-4">
          <label for="entidade_responsavel" class="form-label">Entidade Responsável / Técnico</label>
          <input type="text" id="entidade_responsavel" name="entidade_responsavel" class="form-control" placeholder="Ex: Engenharia Clínica - Equipa B">
        </div>

        <!-- Campo: Periodicidade das Visitas -->
        <div class="col-md-4">
          <label for="periodicidade" class="form-label">Periodicidade das Revisões</label>
          <select id="periodicidade" name="periodicidade" class="form-select">
            <option value="Mensal">Mensal</option>
            <option value="Trimestral">Trimestral</option>
            <option value="Semestral" selected>Semestral</option>
            <option value="Anual">Anual</option>
          </select>
        </div>

        <!-- Campo: Valor Anual/Total -->
        <div class="col-md-4">
          <label for="valor" class="form-label">Valor do Contrato (€)</label>
          <input type="number" step="0.01" id="valor" name="valor" class="form-control" placeholder="Ex: 1250.00">
        </div>

        <!-- Campo: Data de Início -->
        <div class="col-md-6">
          <label for="data_inicio" class="form-label">Data de Início da Vigência</label>
          <input type="date" id="data_inicio" name="data_inicio" class="form-control">
        </div>

        <!-- Campo: Data de Fim -->
        <div class="col-md-6">
          <label for="data_fim" class="form-label">Data de Fim (Cessação) <span class="text-danger">*</span></label>
          <input type="date" id="data_fim" name="data_fim" class="form-control" required>
        </div>

        <!-- Campo: Observações -->
        <div class="col-12">
          <label for="observacoes" class="form-label">Notas Especiais ou Cláusulas</label>
          <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Ex: Inclui substituição de filtros e atualizações de firmware gratuitas..."></textarea>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <button type="reset" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-eraser"></i> Limpar
        </button>
        <button type="submit" class="btn btn-success px-4">
          <i class="bi bi-check-lg"></i> Guardar Contrato
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>