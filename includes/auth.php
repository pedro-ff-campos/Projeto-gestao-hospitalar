<?php session_start();

/*Evitar erros de duplicação*/
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>