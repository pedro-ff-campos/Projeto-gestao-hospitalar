<?php session_start();

/*Evitar erros de duplicação*/
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['Logado']) || $_SESSION['Logado'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>