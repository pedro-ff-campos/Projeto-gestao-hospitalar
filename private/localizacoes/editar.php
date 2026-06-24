<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Editar Localização';
$modulo_ativo  = 'localizacoes';

$erro_mensagem = '';

// ── 1. Validar e Obter o ID da Localização a Editar ─────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Query: Obter os dados atuais da Localização para preencher o form ──────
try {
    $stmt_loc = $pdo->prepare("SELECT * FROM localizacoes WHERE id = ?");
    $stmt_loc->execute([$id]);
    $localizacao = $stmt_loc->fetch();

    // Se a localização não existir na BD, volta para a listagem
    if (!$localizacao) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// ── 3. Processamento do Formulário (Quando o utilizador clica em Guardar) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edificio = trim($_POST['edificio'] ?? '');
    $piso     = trim($_POST['piso'] ?? '');
    $servico  = trim($_POST['servico'] ?? '');
    $sala     = trim($_POST['sala'] ?? '');

    if ($edificio === '' || $piso === '' || $servico === '' || $sala === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        try {
            // Update seguro para prevenir SQL Injection
            $sql = 'UPDATE localizacoes SET edificio = ?, piso = ?, servico = ?, sala = ? WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$edificio, $piso, $servico, $sala, $id]);

            // Redireciona com feedback de sucesso
            header('Location: index.php?sucesso=editada');
            exit;

        } catch (PDOException $e) {
            $erro_mensagem = 'Não foi possível atualizar a localização. Tente novamente.';
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->

<main class="pagina-localizacoes container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Editar Localização</h1>
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

  <!-- Card do Formulário -->
  <div class="card text-white p-4">
    <form method="POST" action="editar.php?id=<?php echo $id; ?>">
      <div class="row g-3">
        
        <!-- Campo: Edifício -->
        <div class="col-md-6">
          <label for="edificio" class="form-label">Edifício <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="edificio" 
            name="edificio" 
            class="form-control" 
            value="<?php echo htmlspecialchars($localizacao['edificio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
            required
          >
        </div>

        <!-- Campo: Piso -->
        <div class="col-md-6">
          <label for="piso" class="form-label">Piso / Andar <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="piso" 
            name="piso" 
            class="form-control" 
            value="<?php echo htmlspecialchars($localizacao['piso'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
            required
          >
        </div>

        <!-- Campo: Serviço -->
        <div class="col-md-6">
          <label for="servico" class="form-label">Serviço / Especialidade <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="servico" 
            name="servico" 
            class="form-control" 
            value="<?php echo htmlspecialchars($localizacao['servico'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
            required
          >
        </div>

        <!-- Campo: Sala -->
        <div class="col-md-6">
          <label for="sala" class="form-label">Sala / Gabinete <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="sala" 
            name="sala" 
            class="form-control" 
            value="<?php echo htmlspecialchars($localizacao['sala'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
            required
          >
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save"></i> Atualizar Localização
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>