<?php
declare(strict_types=1);

$prefixo = '../';
require_once '../includes/db.php';     
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$sucesso_mensagem = '';
$erro_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_atual    = trim($_POST['password_atual'] ?? '');
    $nova_password     = trim($_POST['nova_password'] ?? '');
    $confirma_password = trim($_POST['confirma_password'] ?? '');

    if ($password_atual === '' || $nova_password === '' || $confirma_password === '') {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif ($nova_password !== $confirma_password) {
        $erro_mensagem = 'A nova palavra-passe e a confirmação não são iguais.';
    } elseif (strlen($nova_password) < 6) {
        $erro_mensagem = 'A nova palavra-passe deve ter pelo menos 6 caracteres.';
    } else {
        try {
            // 1. Vai buscar a password encriptada atual do utilizador à base de dados
            $stmt_check = $pdo->prepare("SELECT password FROM utilizadores WHERE id = ?");
            $stmt_check->execute([$user_id]);
            $user = $stmt_check->fetch();

            // 2. Valida se a password atual digitada corresponde à hash guardada
            if ($user && password_verify($password_atual, $user['password'])) {
                
                // Encripta a nova password
                $password_encriptada = password_hash($nova_password, PASSWORD_DEFAULT);

                // Atualiza no MySQL
                $stmt_update = $pdo->prepare("UPDATE utilizadores SET password = ? WHERE id = ?");
                $stmt_update->execute([$password_encriptada, $user_id]);

                // EXIGIDO NO GUIÃO: Grava o log de sucesso na tabela de auditoria
                $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
                $log_stmt->execute([$user_id, 'ALTERAR_PASSWORD_SUCESSO', 'O utilizador alterou a sua palavra-passe de acesso.']);

                $sucesso_mensagem = 'Palavra-passe atualizada com sucesso!';
            } else {
                // EXIGIDO NO GUIÃO: Grava o log de aviso/fraude caso errem a senha atual
                $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
                $log_stmt->execute([$user_id, 'ALTERAR_PASSWORD_FALHA', 'Tentativa falhada de alteração de password (senha atual incorreta).']);

                $erro_mensagem = 'A palavra-passe atual introduzida está incorreta.';
            }
        } catch (PDOException $e) {
            $erro_mensagem = 'Erro ao processar a alteração. Por favor, tente novamente.';
        }
    }
}

require_once '../includes/header.php';
?>


<!-- ════════════ CONTEÚDO HTML ATUALIZADO ════════════ -->
<main class="pagina-equipamentos container-fluid py-4">

  <!-- Cabeçalho da página -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-shield-lock-fill me-2"></i>Configurações da Conta</h1>
  </div>

  <!-- Alertas de Feedback -->
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
    
    <!-- COLUNA ESQUERDA: Formulário de Segurança (Senha) -->
    <div class="col-md-7">
      <div class="card text-white p-4" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h3 class="mb-3 mt-2" style="font-size: 13px; color: #00bcff; text-transform: uppercase; letter-spacing: 0.5px;">Alterar Palavra-Passe</h3>
        
        <form method="POST" action="configuracoes_conta.php">
          <div class="row g-3">
            
            <!-- EXIGIDO NO GUIÃO: Campo de validação de identidade -->
            <div class="col-md-12">
              <label for="password_atual" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 11px;">Palavra-Passe Atual <span class="text-danger">*</span></label>
              <input type="password" id="password_atual" name="password_atual" class="form-control" style="background: #0b1b3d !important; color: #fff;" placeholder="Confirme a sua chave atual" required>
            </div>

            <div class="col-md-12">
              <label for="nova_password" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 11px;">Nova Palavra-Passe <span class="text-danger">*</span></label>
              <input type="password" id="nova_password" name="nova_password" class="form-control" style="background: #0b1b3d !important; color: #fff;" placeholder="Mínimo 6 caracteres" required>
            </div>

            <div class="col-md-12">
              <label for="confirma_password" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 11px;">Confirmar Nova Palavra-Passe <span class="text-danger">*</span></label>
              <input type="password" id="confirma_password" name="confirma_password" class="form-control" style="background: #0b1b3d !important; color: #fff;" placeholder="Repita a nova palavra-passe" required>
            </div>

          </div>

          <!-- Botão de Guardar (Com a cor do texto forçada a branco) -->
          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4 text-white" style="background: #00cc99 !important; border: none; font-weight: 600;">
              <i class="bi bi-key-fill me-1"></i> Atualizar Palavra-Passe
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- COLUNA DIREITA: Informações Adicionais de Segurança (Para preencher o ecrã) -->
    <div class="col-md-5">
      <div class="card text-white p-4 h-100" style="background: #111a2e !important; border: 1px solid rgba(255, 255, 255, 0.04) !important; border-radius: 12px;">
        <h3 class="mb-3 mt-2" style="font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Estado de Segurança</h3>
        
        <div class="mb-4">
          <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.75rem;">Sessão Atual</small>
          <div class="d-flex align-items-center text-success small">
            <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>
            <span class="text-white">Ligado a partir do Endereço IP Local (127.0.0.1)</span>
          </div>
        </div>

        <div class="border-top border-secondary pt-3">
          <small class="text-muted d-block text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">Dicas do Guião para a Defesa</small>
          <ul class="ps-3 text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
            <li class="mb-2">As palavras-passe são protegidas no MySQL com o algoritmo seguro <strong class="text-white">BCRYPT</strong>.</li>
            <li class="mb-2">Qualquer alteração gera um registo automático na tabela de <strong class="text-white">auditoria (logs)</strong>.</li>
            <li>O sistema impede ataques forçados exigindo a validação da chave anterior.</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</main>

<?php include '../includes/footer.php'; ?>