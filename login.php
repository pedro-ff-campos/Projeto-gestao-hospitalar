<?php 
declare(strict_types=1);

session_start();
require_once 'includes/db.php';

$erro = '';

// Se já estiver logado, redireciona direto para a área privada
if (isset($_SESSION['logado'])) {
    header('Location: private/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_sanitizado = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $email = strtolower($email_sanitizado);
    $password = trim($_POST['password']);

    // Procura o utilizador pelo e-mail
    $stmt = $pdo->prepare('SELECT id, email, password FROM utilizadores WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Cenário A: Login com Sucesso
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['logado'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];

        try {
            $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
            $log_stmt->execute([$user['id'], 'LOGIN_SUCESSO', 'O Utilizador iniciou sessão com sucesso.']);
        } catch (PDOException $e) {
            // Silencia o erro se a tabela de logs falhar
        }

        // Redireciona para o painel dentro da pasta private/
        header("Location: private/dashboard.php");
        exit;
        
    } else {
        // Se a validação falhou, descobrimos se o e-mail existe ou não para refinar o log
        $erro = "E-mail ou palavra-passe incorretos!";
        
        try {
            $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
            
            if ($user) {
                // Cenário B: O utilizador existe, mas errou a password (guardamos o ID dele)
                $log_stmt->execute([$user['id'], 'LOGIN_FALHADO', 'Tentativa de login com palavra-passe incorreta para o e-mail: ' . $email]);
            } else {
                // Cenário C: O e-mail nem sequer existe no sistema (guardamos NULL no ID)
                $log_stmt->execute([null, 'LOGIN_AVISO', 'Tentativa de login com e-mail inexistente: ' . $email]);
            }
        } catch (PDOException $e) {
            // Silencia o erro se a tabela de logs falhar
        }
    }
}
?>


<!-- ==========================================================================
     2. ESTRUTURA HTML DA PÁGINA (Livre de <br> e integrada com o CSS)
     ========================================================================== -->
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - MedInvent</title>

  <!-- Bootstrap CSS (Para normalizar as fontes do navegador) -->
  <link href="https://jsdelivr.net" rel="stylesheet"/>
  
  <!-- Bootstrap Icons (Para desenhar a carta e o cadeado nos inputs) -->
  <link href="https://jsdelivr.net" rel="stylesheet"/>
  
  <!-- Ficheiro de estilos onde guardou o design do login -->
  <link rel="stylesheet" href="../assets/css/publico.css?v=9"/>

  
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">

</head>
<body class="login-body">

  <!-- Contentor centralizador -->
  <div class="login-container">
    <div class="login-card">
      
      <!-- Cabeçalho do Bloco -->
      <div class="login-header">
        <h2>Med<span>Invent</span></h2>
        <p>Acesso ao Inventário Hospitalar</p>
      </div>

      <!-- ALERTA DE ERRO DINÂMICO (Só aparece se o PHP detetar erro) -->
      <?php if ($erro): ?>
        <div class="alert alert-danger p-2 text-center" style="font-size: 0.85rem; border-radius: 8px; background-color: #3b191e; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); margin-bottom: 20px;">
          <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $erro; ?>
        </div>
      <?php endif; ?>

      <!-- Formulário estruturado com classes CSS modernas -->
      <form method="POST" action="login.php">
        
        <!-- Campo: E-mail -->
        <div class="input-group-custom">
          <label for="email">E-mail:</label>
          <div class="input-wrapper">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" id="email" name="email" placeholder="Ex: admin@hospital.com" required autocomplete="off" />
          </div>
        </div>

        <!-- Campo: Palavra-passe -->
        <div class="input-group-custom">
          <label for="password">Palavra-passe:</label>
          <div class="input-wrapper">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" id="password" name="password" placeholder="Introduza a sua senha" required />
          </div>
        </div>

        <!-- Botão de Submissão -->
        <button type="submit" class="btn-login">
          Entrar <i class="bi bi-box-arrow-in-right"></i>
        </button>

      </form>

      <!-- Link para regressar à landing page -->
      <div class="login-footer">
        <a href="index.php"><i class="bi bi-arrow-left"></i> Voltar à página inicial</a>
      </div>

    </div>
  </div>

</body>
</html>
