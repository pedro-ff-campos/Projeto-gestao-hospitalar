<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Editar Garantia';
$modulo_ativo  = 'garantias';

$erro_mensagem = '';

// ── 1. Validar e Obter o ID da Garantia a Editar ───────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Query: Carregar Equipamentos para o Dropdown ───────────────────────────
try {
    $stmt_eq = $pdo->query("SELECT id, codigo, designacao FROM equipamentos ORDER BY codigo ASC");
    $equipamentos_lista = $stmt_eq->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── 3. Query: Obter os dados atuais da Garantia para preencher o form ─────────
try {
    $stmt_gar = $pdo->prepare("SELECT * FROM garantias WHERE id = ?");
    $stmt_gar->execute([$id]);
    $garantia = $stmt_gar->fetch();

    if (!$garantia) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// ── 4. Processamento do Formulário (Quando o utilizador clica em Guardar) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento      = (int)($_POST['id_equipamento'] ?? 0);
    $referencia          = trim($_POST['referencia'] ?? '');
    $fornecedor_garantia = trim($_POST['fornecedor_garantia'] ?? '');
    $data_inicio         = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $data_fim            = !empty($_POST['data_fim']) ? $_POST['data_fim'] : '';
    $observacoes         = trim($_POST['observacoes'] ?? '');

    if ($id_equipamento === 0 || $referencia === '' || $fornecedor_garantia === '' || $data_fim === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios (*).';
    } else {
        try {
            // Update seguro respeitando as colunas exatas da tua tabela garantias
            $sql = 'UPDATE garantias 
                    SET id_equipamento = ?, referencia = ?, fornecedor_garantia = ?, data_inicio = ?, data_fim = ?, observacoes = ? 
                    WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_equipamento, $referencia, $fornecedor_garantia, $data_inicio, $data_fim, $observacoes, $id]);

            header('Location: index.php?sucesso=editada');
            exit;

        } catch (PDOException $e) {
            $erro_mensagem = 'Não foi possível atualizar a garantia. Tente novamente.';
        }
    }
}

// ── Incluir o header (abre a barra lateral e o layout automaticamente) ────────
require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<main class="pagina-garantias container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Editar Garantia</h1>
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

  <!-- Card do Formulário (Usa o teu estilo escuro herdado automaticamente) -->
  <div class="card text-white p-4">
    <form method="POST" action="editar.php?id=<?php echo $id; ?>">
      <div class="row g-3">
        
        <!-- Campo Dinâmico: Seleção de Equipamento -->
        <div class="col-md-6">
          <label for="id_equipamento" class="form-label">Equipamento Coberto <span class="text-danger">*</span></label>
          <select id="id_equipamento" name="id_equipamento" class="form-select" required>
            <option value="" disabled>Escolha o aparelho...</option>
            <?php foreach ($equipamentos_lista as $eq): ?>
              <option value="<?php echo $eq['id']; ?>" <?php echo $garantia['id_equipamento'] === $eq['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($eq['codigo'] . ' — ' . $eq['designacao'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Referência -->
        <div class="col-md-6">
          <label for="referencia" class="form-label">Referência / Nº da Garantia <span class="text-danger">*</span></label>
          <input type="text" id="referencia" name="referencia" class="form-control" value="<?php echo htmlspecialchars($garantia['referencia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Campo: Fornecedor de Garantia -->
        <div class="col-md-12">
          <label for="fornecedor_garantia" class="form-label">Fornecedor / Fabricante Responsável <span class="text-danger">*</span></label>
          <input type="text" id="fornecedor_garantia" name="fornecedor_garantia" class="form-control" value="<?php echo htmlspecialchars($garantia['fornecedor_garantia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Campo: Data de Início -->
        <div class="col-md-6">
          <label for="data_inicio" class="form-label">Data de Início de Cobertura</label>
          <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo $garantia['data_inicio']; ?>">
        </div>

        <!-- Campo: Data de Fim -->
        <div class="col-md-6">
          <label for="data_fim" class="form-label">Data de Fim (Expiração) <span class="text-danger">*</span></label>
          <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?php echo $garantia['data_fim']; ?>" required>
        </div>

        <!-- Campo: Observações -->
        <div class="col-12">
          <label for="observacoes" class="form-label">Termos ou Condições da Garantia</label>
          <textarea id="observacoes" name="observacoes" class="form-control" rows="3"><?php echo htmlspecialchars($garantia['observacoes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save"></i> Atualizar Garantia
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>