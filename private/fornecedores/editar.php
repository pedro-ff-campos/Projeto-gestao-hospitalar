<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o teu CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
// require_once '../../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Editar Fornecedor';
$modulo_ativo  = 'fornecedores';

$erro_mensagem = '';

// ── 1. Validar e Obter o ID do Fornecedor a Editar ──────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Query: Obter os dados atuais do Fornecedor para preencher o form ──────
try {
    $stmt_forn = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
    $stmt_forn->execute([$id]);
    $fornecedor = $stmt_forn->fetch();

    // Se o fornecedor não existir na BD, volta para a listagem
    if (!$fornecedor) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// ── 3. Processamento do Formulário (Quando o utilizador clica em Guardar) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $nif      = trim($_POST['nif'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $tipo     = trim($_POST['tipo'] ?? '');

    // Validação académica simples
    if ($nome === '' || $nif === '' || $tipo === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios (Nome, NIF e Tipo).';
    } else {
        try {
            // Update seguro para prevenir SQL Injection
            $sql = 'UPDATE fornecedores SET nome = ?, nif = ?, telefone = ?, email = ?, tipo = ? WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $nif, $telefone, $email, $tipo, $id]);

            // Redireciona com feedback de sucesso
            header('Location: index.php?sucesso=editado');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erro_mensagem = 'Erro: Já existe um fornecedor registado com este NIF.';
            } else {
                $erro_mensagem = 'Não foi possível atualizar o fornecedor. Tente novamente.';
            }
        }
    }
}

// ── Incluir o header (abre a sidebar e o layout automaticamente) ──────────────
require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<main class="pagina-fornecedores container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Editar Fornecedor</h1>
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
        
        <!-- Campo: Nome do Fornecedor -->
        <div class="col-md-6">
          <label for="nome" class="form-label">Nome da Empresa / Fornecedor <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="nome" 
            name="nome" 
            class="form-control" 
            value="<?php echo htmlspecialchars($fornecedor['nome'], ENT_QUOTES, 'UTF-8'); ?>" 
            required
          />
        </div>

        <!-- Campo: NIF -->
        <div class="col-md-6">
          <label for="nif" class="form-label">NIF (Número de Contribuinte) <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="nif" 
            name="nif" 
            class="form-control" 
            value="<?php echo htmlspecialchars($fornecedor['nif'], ENT_QUOTES, 'UTF-8'); ?>" 
            maxlength="20"
            required
          />
        </div>

        <!-- Campo: Telefone -->
        <div class="col-md-4">
          <label for="telefone" class="form-label">Telefone Geral</label>
          <input 
            type="text" 
            id="telefone" 
            name="telefone" 
            class="form-control" 
            value="<?php echo htmlspecialchars($fornecedor['telefone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
          />
        </div>

        <!-- Campo: E-mail -->
        <div class="col-md-4">
          <label for="email" class="form-label">E-mail de Contacto</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            value="<?php echo htmlspecialchars($fornecedor['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
          />
        </div>

        <!-- Campo: Tipo de Fornecedor -->
        <div class="col-md-4">
          <label for="tipo" class="form-label">Tipo de Fornecimento <span class="text-danger">*</span></label>
          <select id="tipo" name="tipo" class="form-select" required>
            <option value="equipamentos" <?php echo $fornecedor['tipo'] === 'equipamentos' ? 'selected' : ''; ?>>Manutenção / Equipamentos</option>
            <option value="consumiveis" <?php echo $fornecedor['tipo'] === 'consumiveis' ? 'selected' : ''; ?>>Consumíveis Médicos</option>
            <option value="software" <?php echo $fornecedor['tipo'] === 'software' ? 'selected' : ''; ?>>Sistemas / TI</option>
          </select>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save"></i> Atualizar Fornecedor
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>