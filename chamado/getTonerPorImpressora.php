<?php
require_once '../db_connection.php';

$equipamento_id = $_GET['equipamento_id'] ?? 0;
if(!$equipamento_id){
  echo json_encode(['success'=>false, 'error'=>'Equipamento não informado']);
  exit;
}

$stmt = $pdo->prepare("
  SELECT it.id AS item_id, it.nome AS nome_toner, it.quantidade, imp.cor
  FROM impressora_tonner imp
  JOIN itens it ON imp.item_id = it.id
  WHERE imp.equipamento_id = ? AND imp.ativo = 1
  LIMIT 1
");
$stmt->execute([$equipamento_id]);
$toner = $stmt->fetch(PDO::FETCH_ASSOC);

if($toner){
  echo json_encode(['success'=>true, ...$toner]);
} else {
  echo json_encode(['success'=>false]);
}
