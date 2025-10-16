<?php
// Arquivo: estoque/handler_entrada.php

session_start();
require_once '../db_connection.php'; 

$usuario_id = $_SESSION['usuario_id'] ?? 1; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}

$nota_fiscal = trim($_POST['nota_fiscal'] ?? '');
$fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
$itens_recebidos = $_POST['itens'] ?? [];
$motivo_base = "Compra - NF: " . $nota_fiscal;

if (empty($nota_fiscal) || $fornecedor_id <= 0 || empty($itens_recebidos)) {
    die("Erro: Dados principais da compra e pelo menos um item são obrigatórios.");
}

try {
    $pdo->beginTransaction();
    
    foreach ($itens_recebidos as $item) {
        $item_id = (int)($item['item_id'] ?? 0);
        $quantidade = (int)($item['quantidade'] ?? 0);
        
        if ($item_id <= 0 || $quantidade <= 0) {
            continue;
        }

        // 1. Registra a movimentação de ENTRADA
        $sql_mov = "
            INSERT INTO movimentacoes_estoque 
            (item_id, tipo, quantidade, fornecedor_id, nota_fiscal, motivo, usuario_id) 
            VALUES (?, 'entrada', ?, ?, ?, ?, ?)
        ";
        $stmt_mov = $pdo->prepare($sql_mov);
        $stmt_mov->execute([
            $item_id, 
            $quantidade, 
            $fornecedor_id, 
            $nota_fiscal, 
            $motivo_base,
            $usuario_id
        ]);
        
        // 2. Atualiza a QUANTIDADE total na tabela ITENS
        $sql_update_item = "UPDATE itens SET quantidade = quantidade + ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update_item);
        $stmt_update->execute([$quantidade, $item_id]);
    }
    
    $pdo->commit();
    
    // Redireciona para o histórico do primeiro item (se houver) ou para a lista geral
    $redirect_id = !empty($itens_recebidos) ? (int)$itens_recebidos[1]['item_id'] : 0;
    
    if ($redirect_id > 0) {
         header("Location: movimentacoes.php?item_id={$redirect_id}&success=entrada");
    } else {
         header("Location: listarItens.php?success=entrada");
    }
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro fatal ao processar entrada de estoque: " . $e->getMessage());
}