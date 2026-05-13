<?php
// pages/excluir_material.php
session_start();
require '../config/conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmtMov = $pdo->prepare("DELETE FROM movimentacoes WHERE material_id = ?");
        $stmtMov->execute([$id]);

        $stmtMat = $pdo->prepare("DELETE FROM materiais WHERE id = ?");
        $stmtMat->execute([$id]);

    } catch (PDOException $e) {

    }
}

// Redireciona de volta para a tela de materiais
header("Location: materiais.php");
exit;