<?php
declare(strict_types=1);
$prefixo = '../';
require_once '../includes/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id === 0) {
    header('Location: ../login.php');
    exit;
}

// Quando o utilizador altera o Nome ou o E-mail
// ... (teu código inicial de sessão e includes) ...

$sucesso_mensagem = '';
$erro_mensagem = '';

// Quando o utilizador altera o Nome ou o E-mail
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome  = trim($_POST['perfil_nome'] ?? '');
    $novo_email = trim($_POST['perfil_email'] ?? '');

    if ($novo_nome !== '' && $novo_email !== '') {
        try {
            $stmt_update = $pdo->prepare("UPDATE utilizadores SET nome = ?, email = ? WHERE id = ?");
            $stmt_update->execute([$novo_nome, $novo_email, $user_id]);
            
            // Atualiza também os dados na sessão atual
            $_SESSION['user_nome']  = $novo_nome;
            $_SESSION['user_email'] = $novo_email;
            
            // Mensagem de sucesso ativada
            $sucesso_mensagem = 'Os teus dados pessoais foram atualizados com sucesso!';
        } catch (PDOException $e) {
            $erro_mensagem = 'Erro: Este endereço de e-mail já está a ser utilizado por outro utilizador.';
        }
    } else {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    }
}

// Carrega os dados mais recentes do utilizador
try {
    $stmt = $pdo->prepare("SELECT nome, email, criado_at FROM utilizadores WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}

require_once '../includes/header.php';
?>


<!-- ════════════ CONTEÚDO HTML ACTUALIZADO ════════════ -->
<div class="pagina-equipamentos container-fluid py-4">
  
  <!-- Cabeçalho da página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-person-badge me-2"></i>O meu Perfil</h1>
  </div>

  <!-- ── ADICIONADO: Mensagens visuais de feedback ── -->
  <?php if (!empty($sucesso_mensagem)): ?>
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <div><?php echo $sucesso_mensagem; ?></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($erro_mensagem)): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?php echo $erro_mensagem; ?></div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    
    <!-- COLUNA ESQUERDA: Cartão de Identificação Visual -->
    <div class="col-md-4">
      <div class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <div class="text-center mb-4">
          <i class="bi bi-person-circle text-info" style="font-size: 4.5rem;"></i>
          <h3 class="mt-2 m-0 fw-bold text-white"><?php echo htmlspecialchars($user['nome'] ?? 'Utilizador', ENT_QUOTES, 'UTF-8'); ?></h3>
          <span class="badge bg-success text-white px-3 py-1 mt-2">Administrador</span>
        </div>
        
        <!-- Correção das cores: Forçamos classes text-white e text-muted claras -->
        <div class="border-top border-secondary pt-3 mt-3">
          <div class="mb-3">
            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Endereço de E-mail</small>
            <span class="text-white fs-6"><?php echo htmlspecialchars($user['email'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div>
            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Data de Registo</small>
            <span class="text-white fs-6"><?php echo !empty($user['criado_at']) ? date('d/m/Y', strtotime($user['criado_at'])) : '—'; ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- COLUNA DIREITA: Formulário de Atualização de Dados (CMS do Utilizador) -->
    <div class="col-md-8">
      <div class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h3 class="mb-3" style="font-size: 13px; color: #00bcff; text-transform: uppercase; letter-spacing: 0.5px;">Modificar Informações Pessoais</h3>
        
        <!-- Formulário para atualizar os dados na tabela utilizadores -->
        <form method="POST" action="perfil.php">
          <div class="row g-3">
            
            <div class="col-md-12">
              <label for="perfil_nome" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 11px;">Nome Completo</label>
              <input type="text" id="perfil_nome" name="perfil_nome" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($user['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="col-md-12">
              <label for="perfil_email" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 11px;">E-mail de Acesso</label>
              <input type="email" id="perfil_email" name="perfil_email" class="form-control" style="background: #0b1b3d !important; color: #fff;" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

          </div>

          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4" style="background: #00cc99 !important; border: none; color: #0b1b3d; font-weight: 600;">
              <i class="bi bi-person-check me-1"></i> Atualizar os meus Dados
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

<?php include '../includes/footer.php'; ?>
