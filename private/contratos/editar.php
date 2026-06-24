<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Editar Contrato';
$modulo_ativo  = 'contratos';

$erro_mensagem = '';

// ── 1. Validar e Obter o ID do Contrato a Editar ───────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Query: Carregar Equipamentos para preencher o Dropdown ────────────────
try {
    $stmt_eq = $pdo->query("SELECT id, codigo, designacao FROM equipamentos ORDER BY codigo ASC");
    $equipamentos_lista = $stmt_eq->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── 3. Query: Carregar Fornecedores para preencher o Dropdown ────────────────
try {
    $stmt_forn = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC");
    $fornecedores_lista = $stmt_forn->fetchAll();
} catch (PDOException $e) {
    $fornecedores_lista = [];
}

// ── 4. Query: Obter os dados atuais do Contrato para preencher o formulário ──
try {
    $stmt_cont = $pdo->prepare("SELECT * FROM contratos WHERE id = ?");
    $stmt_cont->execute([$id]);
    $contrato = $stmt_cont->fetch();

    // Se o contrato não existir na BD, regressa à listagem
    if (!$contrato) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// ── 5. Processamento do Formulário (Quando o utilizador clica em Atualizar) ──
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

    // Validação de campos obrigatórios 
    if ($id_equipamento === 0 || $numero_contrato === '' || $data_fim === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios (Equipamento, Nº Contrato e Data de Fim).';
    } else {
        try {
           
            $sql = 'UPDATE contratos 
                    SET id_equipamento = ?, id_fornecedor = ?, numero_contrato = ?, tipo = ?, entidade_responsavel = ?, periodicidade = ?, data_inicio = ?, data_fim = ?, valor = ?, observacoes = ? 
                    WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_equipamento, 
                $id_fornecedor, 
                $numero_contrato, 
                $tipo, 
                $entidade_responsavel, 
                $periodicidade, 
                $data_inicio, 
                $data_fim, 
                $valor, 
                $observacoes, 
                $id
            ]);

            // Redireciona de volta para a listagem principal com feedback de sucesso
            header('Location: index.php?sucesso=editado');
            exit;

        } catch (PDOException $e) {
            $erro_mensagem = 'Não foi possível atualizar o contrato. Por favor, tente novamente.';
        }
    }
}


require_once '../../includes/header.php';
?>



<!-- ════════════ CONTEÚDO HTML ════════════ -->

<main class="pagina-contratos container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Editar Contrato de Manutenção</h1>
    <a href="index.php" class="btn btn-outline-light">
      <i class="bi bi-arrow-left"></i> Voltar à Lista
    </a>
  </div>

  <!-- Mensagem de Erro Visual (Só aparece se o PHP detetar falhas) -->
  <?php if (!empty($erro_mensagem)): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?php echo htmlspecialchars($erro_mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
  <?php endif; ?>

  <!-- Card do Formulário (Visual Escuro Automático) -->
  <div class="card text-white p-4">
    <form method="POST" action="editar.php?id=<?php echo $id; ?>">
      <div class="row g-3">
        
        <!-- Campo: Número do Contrato -->
        <div class="col-md-4">
          <label for="numero_contrato" class="form-label">Número do Contrato <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="numero_contrato" 
            name="numero_contrato" 
            class="form-control" 
            value="<?php echo htmlspecialchars($contrato['numero_contrato'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
            required
          >
        </div>

        <!-- Campo Dinâmico: Seleção de Equipamento -->
        <div class="col-md-8">
          <label for="id_equipamento" class="form-label">Equipamento Vinculado <span class="text-danger">*</span></label>
          <select id="id_equipamento" name="id_equipamento" class="form-select" required>
            <option value="" disabled>Escolha o aparelho...</option>
            <?php foreach ($equipamentos_lista as $eq): ?>
              <option value="<?php echo $eq['id']; ?>" <?php echo ($contrato['id_equipamento'] === $eq['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($eq['codigo'] . ' — ' . $eq['designacao'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo Dinâmico: Seleção de Fornecedor -->
        <div class="col-md-6">
          <label for="id_fornecedor" class="form-label">Empresa / Fornecedor Contratado</label>
          <select id="id_fornecedor" name="id_fornecedor" class="form-select">
            <option value="">Nenhum fornecedor associado (Opcional)</option>
            <?php foreach ($fornecedores_lista as $forn): ?>
              <option value="<?php echo $forn['id']; ?>" <?php echo ($contrato['id_fornecedor'] === $forn['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Tipo de Contrato -->
        <div class="col-md-6">
          <label for="tipo" class="form-label">Tipo de Contrato</label>
          <select id="tipo" name="tipo" class="form-select">
            <?php
              $tipos = [
                'manutencao_preventiva' => 'Manutenção Preventiva',
                'manutencao_corretiva'  => 'Manutenção Corretiva',
                'calibracao'            => 'Calibração',
                'assistencia_tecnica'   => 'Assistência Técnica',
                'full_service'          => 'Full Service',
                'outro'                 => 'Outro'
              ];
              foreach ($tipos as $valor => $label):
            ?>
              <option value="<?php echo $valor; ?>" <?php echo ($contrato['tipo'] === $valor) ? 'selected' : ''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Entidade Responsável -->
        <div class="col-md-4">
          <label for="entidade_responsavel" class="form-label">Entidade Responsável / Técnico</label>
          <input 
            type="text" 
            id="entidade_responsavel" 
            name="entidade_responsavel" 
            class="form-control" 
            value="<?php echo htmlspecialchars($contrato['entidade_responsavel'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
          >
        </div>

        <!-- Campo: Periodicidade -->
        <div class="col-md-4">
          <label for="periodicidade" class="form-label">Periodicidade das Revisões</label>
          <select id="periodicidade" name="periodicidade" class="form-select">
            <?php
              $periodos = ['Mensal', 'Trimestral', 'Semestral', 'Anual'];
              foreach ($periodos as $p):
            ?>
              <option value="<?php echo $p; ?>" <?php echo ($contrato['periodicidade'] === $p) ? 'selected' : ''; ?>>
                <?php echo $p; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Valor do Contrato -->
        <div class="col-md-4">
          <label for="valor" class="form-label">Valor do Contrato (€)</label>
          <input 
            type="number" 
            step="0.01" 
            id="valor" 
            name="valor" 
            class="form-control" 
            value="<?php echo htmlspecialchars($contrato['valor'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
          >
        </div>

        <!-- Campo: Data de Início -->
        <div class="col-md-6">
          <label for="data_inicio" class="form-label">Data de Início da Vigência</label>
          <input 
            type="date" 
            id="data_inicio" 
            name="data_inicio" 
            class="form-control" 
            value="<?php echo $contrato['data_inicio'] ?? ''; ?>"
          >
        </div>

        <!-- Campo: Data de Fim -->
        <div class="col-md-6">
          <label for="data_fim" class="form-label">Data de Fim (Cessação) <span class="text-danger">*</span></label>
          <input 
            type="date" 
            id="data_fim" 
            name="data_fim" 
            class="form-control" 
            value="<?php echo $contrato['data_fim'] ?? ''; ?>" 
            required
          >
        </div>

        <!-- Campo: Observações -->
        <div class="col-12">
          <label for="observacoes" class="form-label">Notas Especiais ou Cláusulas</label>
          <textarea id="observacoes" name="observacoes" class="form-control" rows="3"><?php echo htmlspecialchars($contrato['observacoes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save"></i> Atualizar Contrato
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>