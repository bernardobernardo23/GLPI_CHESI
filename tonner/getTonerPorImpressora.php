<?php
require_once '../db_connection.php';

$equipamento_id = $_GET['equipamento_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT it.item_id, i.nome AS nome_toner, it.cor
    FROM impressora_tonner it
    JOIN itens i ON i.id = it.item_id
    WHERE it.equipamento_id = ? AND it.ativo = 1
");
$stmt->execute([$equipamento_id]);
$toners = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => count($toners) > 0,
    'toners' => $toners
]);
