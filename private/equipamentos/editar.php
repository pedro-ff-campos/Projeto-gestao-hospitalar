<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Editar Equipamento';
$modulo_active  = 'equipamentos';

$erro_mensagem = '';
$sucesso_mensagem = '';

// ── 1. Validar e Obter o ID do Equipamento a Editar ─────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Query: Carregar Localizações para o Dropdown ───────────────────────────
try {
    $stmt_loc = $pdo->query("SELECT id, edificio, piso, servico, sala FROM localizacoes ORDER BY edificio ASC, servico ASC");
    $localizacoes_lista = $stmt_loc->fetchAll();
} catch (PDOException $e) {
    $localizacoes_lista = [];
}

// ── 3. Query: Carregar Fornecedores para o Dropdown ───────────────────────────
try {
    $stmt_forn = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC");
    $fornecedores_lista = $stmt_forn->fetchAll();
} catch (PDOException $e) {
    $fornecedores_lista = [];
}

// ── 4. Query: Obter os dados atuais do Equipamento para preencher o form ──────
try {
    $stmt_eq = $pdo->prepare("SELECT * FROM equipamentos WHERE id = ?");
    $stmt_eq->execute([$id]);
    $equipamento = $stmt_eq->fetch();

    // Se o equipamento não existir na BD, volta para a listagem
    if (!$equipamento) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// ── 5. Processamento do Formulário (Quando o utilizador clica em Guardar) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo         = trim($_POST['codigo'] ?? '');
    $designacao     = trim($_POST['designacao'] ?? '');
    $marca          = trim($_POST['marca'] ?? '');
    $modelo         = trim($_POST['modelo'] ?? '');
    $numero_serie   = trim($_POST['numero_serie'] ?? '');
    $estado         = trim($_POST['estado'] ?? '');
    $criticidade    = trim($_POST['criticidade'] ?? '');
    $id_localizacao = (int)($_POST['id_localizacao'] ?? 0);
    $id_fornecedor  = $_POST['id_fornecedor'] !== '' ? (int)$_POST['id_fornecedor'] : null;

    if ($codigo === '' || $designacao === '' || $estado === '' || $id_localizacao === 0) {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        try {
            // Update seguro respeitando as colunas exatas do teu SQL
            $sql = 'UPDATE equipamentos 
                    SET codigo = ?, designacao = ?, marca = ?, modelo = ?, numero_serie = ?, estado = ?, criticidade = ?, id_localizacao = ?, id_fornecedor = ? 
                    WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $designacao, $marca, $modelo, $numero_serie, $estado, $criticidade, $id_localizacao, $id_fornecedor, $id]);

            // Redireciona de volta para a listagem principal indicando que foi editado
            header('Location: index.php?sucesso=editado');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erro_mensagem = 'Erro: Já existe um equipamento registado com este Código.';
            } else {
                $erro_mensagem = 'Não foi possível atualizar o equipamento. Tente novamente.';
            }
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<main class="pagina-equipamentos container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Editar Equipamento</h1>
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

  <!-- Card do Formulário (Herda o teu CSS Dark unificado automaticamente) -->
  <div class="card text-white p-4">
    <form method="POST" action="editar.php?id=<?php echo $id; ?>">
      <div class="row g-3">
        
        <!-- Campo: Código -->
        <div class="col-md-4">
          <label for="codigo" class="form-label">Código Interno <span class="text-danger">*</span></label>
          <input type="text" id="codigo" name="codigo" class="form-control" value="<?php echo htmlspecialchars($equipamento['codigo'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Campo: Designação -->
        <div class="col-md-8">
          <label for="designacao" class="form-label">Designação / Nome do Equipamento <span class="text-danger">*</span></label>
          <input type="text" id="designacao" name="designacao" class="form-control" value="<?php echo htmlspecialchars($equipamento['designacao'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Campo: Marca -->
        <div class="col-md-4">
          <label for="marca" class="form-label">Marca</label>
          <input type="text" id="marca" name="marca" class="form-control" value="<?php echo htmlspecialchars($equipamento['marca'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <!-- Campo: Modelo -->
        <div class="col-md-4">
          <label for="modelo" class="form-label">Modelo</label>
          <input type="text" id="modelo" name="modelo" class="form-control" value="<?php echo htmlspecialchars($equipamento['modelo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <!-- Campo: Número de Série -->
        <div class="col-md-4">
          <label for="numero_serie" class="form-label">Número de Série</label>
          <input type="text" id="numero_serie" name="numero_serie" class="form-control" value="<?php echo htmlspecialchars($equipamento['numero_serie'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <!-- Campo: Seleção de Localização (Com preenchimento automático) -->
        <div class="col-md-6">
          <label for="id_localizacao" class="form-label">Localização no Hospital <span class="text-danger">*</span></label>
          <select id="id_localizacao" name="id_localizacao" class="form-select" required>
            <option value="" disabled>Escolha a sala / serviço...</option>
            <?php foreach ($localizacoes_lista as $loc): ?>
              <option value="<?php echo $loc['id']; ?>" <?php echo $equipamento['id_localizacao'] === $loc['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($loc['edificio'] . ' — ' . $loc['servico'] . ' (Sala ' . $loc['sala'] . ')', ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Seleção de Fornecedor (Com preenchimento automático) -->
        <div class="col-md-6">
          <label for="id_fornecedor" class="form-label">Fornecedor Associado</label>
          <select id="id_fornecedor" name="id_fornecedor" class="form-select">
            <option value="">Nenhum fornecedor associado (Opcional)</option>
            <?php foreach ($fornecedores_lista as $forn): ?>
              <option value="<?php echo $forn['id']; ?>" <?php echo $equipamento['id_fornecedor'] === $forn['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Estado -->
        <div class="col-md-6">
          <label for="estado" class="form-label">Estado Operacional <span class="text-danger">*</span></label>
          <select id="estado" name="estado" class="form-select" required>
            <option value="Ativo" <?php echo $equipamento['estado'] === 'Ativo' ? 'selected' : ''; ?>>Ativo (Em funcionamento)</option>
            <option value="Manutenção" <?php echo $equipamento['estado'] === 'Manutenção' ? 'selected' : ''; ?>>Em Manutenção (Oficina/Técnico)</option>
            <option value="Inativo" <?php echo $equipamento['estado'] === 'Inativo' ? 'selected' : ''; ?>>Inativo (Fora de serviço)</option>
          </select>
        </div>

        <!-- Campo: Criticidade -->
        <div class="col-md-6">
          <label for="criticidade" class="form-label">Criticidade</label>
          <select id="criticidade" name="criticidade" class="form-select">
            <option value="BAIXA" <?php echo $equipamento['criticidade'] === 'BAIXA' ? 'selected' : ''; ?>>BAIXA</option>
            <option value="MÉDIA" <?php echo $equipamento['criticidade'] === 'MÉDIA' ? 'selected' : ''; ?>>MÉDIA</option>
            <option value="ALTA" <?php echo $equipamento['criticidade'] === 'ALTA' ? 'selected' : ''; ?>>ALTA</option>
          </select>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save"></i> Atualizar Equipamento
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>