<?php
session_start();
require_once '../db_connection.php';

// Verifica se é TI
if (!isset($_SESSION['usuario_setor']) || $_SESSION['usuario_setor'] !== 'TI') {
    die("Acesso negado");
}

$equipamento_id = $_POST['equipamento_id'] ?? null;
$item_id = $_POST['item_id'] ?? null;
$cor = trim($_POST['cor'] ?? '');

if (!$equipamento_id || !$item_id) {
    die("Dados incompletos.");
}

// Verifica se já existe o vínculo ativo
$stmt = $pdo->prepare("SELECT * FROM impressora_tonner WHERE equipamento_id = ? AND item_id = ?");
$stmt->execute([$equipamento_id, $item_id]);
$existe = $stmt->fetch();

if ($existe) {
    // Atualiza se já existir
    $stmt = $pdo->prepare("UPDATE impressora_tonner SET cor = ?, ativo = 1 WHERE equipamento_id = ? AND item_id = ?");
    $stmt->execute([$cor, $equipamento_id, $item_id]);
} else {
    // Insere novo vínculo
    $stmt = $pdo->prepare("INSERT INTO impressora_tonner (equipamento_id, item_id, cor, ativo) VALUES (?, ?, ?, 1)");
    $stmt->execute([$equipamento_id, $item_id, $cor]);
}

header("Location: vincularToner.php?success=1");
exit;
