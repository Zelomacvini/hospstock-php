<?php
// config/conexao.php

$host = 'localhost';
$dbname = 'estoque_hospitalar';
$usuario = 'root';
$senha = ''; // No Laragon, a senha do root por padrão é vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>