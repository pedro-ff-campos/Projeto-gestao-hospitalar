<?php session_start();
require_once 'includes/db.php';

$erro = '';

if (isset($_SESSION['logado'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email_sanitizado = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $email = strtolower($email_sanitizado);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare('SELECT id, email, password FROM utilizadores WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['logado'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];

        $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
        $log_stmt->execute([$user['id'], 'LOGIN_SUCESSO', 'O Utilizador iniciou sessão com sucesso.']);

        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "E-mail ou palavra-passe incorretos!";
        $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
        $log_stmt->execute([null, 'LOGIN_FALHADO', "Tentativa de login falhada para o e-mail:' . $email"]);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - MedInvent</title>
</head>

<body>
    <h2>Acesso ao Inventário Hospitalar</h2>
    
    <?php if ($erro): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>
        
        <label>Palavra-passe:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Entrar</button>
    </form>
</body>
</html>