<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o teu CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
// require_once '../../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Nova Localização';
$modulo_ativo  = 'localizacoes';

$erro_mensagem = '';

// ── Processamento do Formulário (Quando o utilizador clica em Guardar) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolha e limpeza básica dos dados estruturados conforme o teu SQL
    $edificio = trim($_POST['edificio'] ?? '');
    $piso     = trim($_POST['piso'] ?? '');
    $servico  = trim($_POST['servico'] ?? '');
    $sala     = trim($_POST['sala'] ?? '');

    // Validação simples dos campos obrigatórios
    if ($edificio === '' || $piso === '' || $servico === '' || $sala === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        try {
            // Inserção segura respeitando as colunas exatas da tua tabela localizacoes
            $sql = 'INSERT INTO localizacoes (edificio, piso, servico, sala) VALUES (?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$edificio, $piso, $servico, $sala]);

            // Se correr bem, redireciona para a listagem principal com mensagem de sucesso
            header('Location: index.php?sucesso=criada');
            exit;

        } catch (PDOException $e) {
            $erro_mensagem = 'Não foi possível registar a localização. Por favor, tente novamente.';
        }
    }
}

// ── Incluir o header (abre a barra lateral e o layout automaticamente) ────────
require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<!-- Usamos a classe de contexto para reaproveitar os teus estilos escuros unificados -->
<main class="pagina-localizacoes container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Registar Nova Localização</h1>
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

  <!-- Card do Formulário (Usa o mesmo estilo escuro que unificámos) -->
  <div class="card text-white p-4">
    <form method="POST" action="criar.php">
      <div class="row g-3">
        
        <!-- Campo: Edifício -->
        <div class="col-md-6">
          <label for="edificio" class="form-label">Edifício <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="edificio" 
            name="edificio" 
            class="form-control" 
            placeholder="Ex: Edifício Principal, Ala Nova" 
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
            placeholder="Ex: Piso 0, Piso -1, 2º Andar" 
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
            placeholder="Ex: Urgências, Cardiologia, Pediatria" 
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
            placeholder="Ex: Sala 102, Triagem, UCI-3" 
            required
          >
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <button type="reset" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-eraser"></i> Limpar
        </button>
        <button type="submit" class="btn btn-success px-4">
          <i class="bi bi-check-lg"></i> Guardar Localização
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>