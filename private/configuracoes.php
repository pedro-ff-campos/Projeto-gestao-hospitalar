<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz (estamos na pasta private/, recua 1 nível)
$prefixo = '../';

// 2. Includes obrigatórios do sistema
// require_once '../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Configurações do Sistema';
$modulo_ativo  = 'configuracoes';

$sucesso_mensagem = '';
$erro_mensagem = '';

// ── 1. Processamento do Formulário (Quando o utilizador clica em Atualizar) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Instrução SQL segura para atualizar cada campo usando a sua chave única
        $sql = "UPDATE conteudos_publicos SET valor = ? WHERE chave = ?";
        $stmt = $pdo->prepare($sql);

        // Fazemos o update para cada um dos campos do site público
        $stmt->execute([trim($_POST['titulo_hero'] ?? ''), 'titulo_hero']);
        $stmt->execute([trim($_POST['texto_hero'] ?? ''), 'texto_hero']);
        $stmt->execute([trim($_POST['titulo_sobre'] ?? ''), 'titulo_sobre']);
        $stmt->execute([trim($_POST['texto_sobre'] ?? ''), 'texto_sobre']);
        $stmt->execute([trim($_POST['email'] ?? ''), 'email']);
        $stmt->execute([trim($_POST['telefone'] ?? ''), 'telefone']);
        $stmt->execute([trim($_POST['morada'] ?? ''), 'morada']);

        $sucesso_mensagem = 'Conteúdos públicos atualizados com sucesso! As alterações já estão visíveis na página inicial.';
    } catch (PDOException $e) {
        $erro_mensagem = 'Não foi possível atualizar as configurações. Por favor, tente novamente.';
    }
}

// ── 2. Query: Carregar os Conteúdos Atuais da Base de Dados ─────────────────
$conteudos = [];
try {
    $dados = $pdo->query("SELECT chave, valor FROM conteudos_publicos")->fetchAll();
    // Transforma as linhas num array associativo simples indexado pela chave
    foreach ($dados as $linha) {
        $conteudos[$linha['chave']] = $linha['valor'];
    }
} catch (PDOException $e) {
    // Mantém valores vazios de segurança caso a tabela falhe
}

// ── Incluir o header padrão do site ──────────────────────────────────────────
require_once '../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<!-- Usamos a tag main com a classe como tens nos teus estilos CSS para herdar as cores -->
<main class="pagina-equipamentos container-fluid py-4">

  <!-- Cabeçalho da página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-gear-fill me-2"></i>Configurações do Sistema</h1>
  </div>

  <!-- Alerta de Sucesso -->
  <?php if ($sucesso_mensagem !== ''): ?>
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <div><?php echo $sucesso_mensagem; ?></div>
    </div>
  <?php endif; ?>

  <!-- Alerta de Erro -->
  <?php if ($erro_mensagem !== ''): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?php echo $erro_mensagem; ?></div>
    </div>
  <?php endif; ?>

  <!-- Card do Formulário (Herda o teu estilo escuro de forma automática) -->
  <div class="card text-white p-4" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
    <form method="POST" action="configuracoes.php">
      
      <!-- SECÇÃO A: PÁGINA INICIAL (HERO) -->
      <h3 class="mb-3 mt-2" style="font-size: 13px; color: #00bcff; text-transform: uppercase; letter-spacing: 0.5px;">Secção Inicial (Apresentação Hero)</h3>
      <div class="row g-3 mb-4">
        <div class="col-md-12">
          <label for="titulo_hero" class="form-label">Título Principal</label>
          <input type="text" id="titulo_hero" name="titulo_hero" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($conteudos['titulo_hero'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="col-md-12">
          <label for="texto_hero" class="form-label">Texto Descritivo</label>
          <textarea id="texto_hero" name="texto_hero" class="form-control" style="background: #0b1b3d !important; color: #fff;" rows="2" required><?php echo htmlspecialchars($conteudos['texto_hero'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
      </div>

      <hr class="border-secondary opacity-25 my-4">

      <!-- SECÇÃO B: SOBRE NÓS -->
      <h3 class="mb-3" style="font-size: 13px; color: #00bcff; text-transform: uppercase; letter-spacing: 0.5px;">Secção Sobre Nós</h3>
      <div class="row g-3 mb-4">
        <div class="col-md-12">
          <label for="titulo_sobre" class="form-label">Título da Secção</label>
          <input type="text" id="titulo_sobre" name="titulo_sobre" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($conteudos['titulo_sobre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="col-md-12">
          <label for="texto_sobre" class="form-label">Texto Institucional</label>
          <textarea id="texto_sobre" name="texto_sobre" class="form-control" style="background: #0b1b3d !important; color: #fff;" rows="3" required><?php echo htmlspecialchars($conteudos['texto_sobre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
      </div>

      <hr class="border-secondary opacity-25 my-4">

      <!-- SECÇÃO C: CONTACTOS DA EMPRESA -->
      <h3 class="mb-3" style="font-size: 13px; color: #00bcff; text-transform: uppercase; letter-spacing: 0.5px;">Informações de Contacto</h3>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="email" class="form-label">E-mail Oficial</label>
          <input type="email" id="email" name="email" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($conteudos['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="col-md-6">
          <label for="telefone" class="form-label">Telefone de Suporte</label>
          <input type="text" id="telefone" name="telefone" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($conteudos['telefone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="col-md-12">
          <label for="morada" class="form-label">Morada Física / Sede</label>
          <input type="text" id="morada" name="morada" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($conteudos['morada'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
      </div>

      <!-- Botão de Guardar -->
      <div class="mt-4 d-flex justify-content-end">
        <button type="submit" class="btn btn-success px-4" style="background: #00cc99 !important; border: none; color: #0b1b3d; font-weight: 600;">
          <i class="bi bi-save2 me-1"></i> Atualizar Site Público
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../includes/footer.php'; ?>