<?php
// Arquivo: estoque/handler_baixa.php

session_start();
require_once '../db_connection.php'; 

$usuario_id = $_SESSION['usuario_id'] ?? 1; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}

$item_id = (int)($_POST['item_id'] ?? 0);
$quantidade = (int)($_POST['quantidade'] ?? 0);
$motivo = trim($_POST['motivo'] ?? '');

if ($item_id <= 0 || $quantidade <= 0 || empty($motivo)) {
    die("Erro: Item, quantidade e motivo são obrigatórios.");
}

try {
    $pdo->beginTransaction();

    // 1. Verifica o estoque atual para evitar saldo negativo
    $stmt_check = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt_check->execute([$item_id]);
    $estoque_atual = (int)$stmt_check->fetchColumn();

    if ($estoque_atual < $quantidade) {
        throw new Exception("Estoque insuficiente para registrar a baixa. Saldo: {$estoque_atual}.");
    }

    // 2. Registra a movimentação de BAIXA
    $sql_mov = "
        INSERT INTO movimentacoes_estoque 
        (item_id, tipo, quantidade, motivo, usuario_id) 
        VALUES (?, 'baixa', ?, ?, ?)
    ";
    $stmt_mov = $pdo->prepare($sql_mov);
    $stmt_mov->execute([
        $item_id, 
        $quantidade, 
        $motivo,
        $usuario_id
    ]);
    
    // 3. Atualiza a QUANTIDADE total na tabela ITENS (subtrai)
    $sql_update_item = "UPDATE itens SET quantidade = quantidade - ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update_item);
    $stmt_update->execute([$quantidade, $item_id]);
    
    $pdo->commit();
    
    // Redireciona para o histórico com sucesso
    header("Location: movimentacoes.php?item_id={$item_id}&success=baixa");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro fatal ao processar baixa de estoque: " . $e->getMessage());
}