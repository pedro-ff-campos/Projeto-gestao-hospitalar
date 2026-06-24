<?php
$host     = '127.0.0.1';        
$port     = '3306';             
$db       = 'projeto_sibdas';     
$username = 'root';             
$password = '';                 
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    //  Desiste de ligar após 4 segundos se o servidor não responder
    PDO::ATTR_TIMEOUT            => 4, 
];

try { 
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
