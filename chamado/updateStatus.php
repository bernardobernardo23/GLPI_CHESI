<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_setor'] !== 'TI') {
    die("Acesso negado.");
}

$chamado_id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

if (!$chamado_id || !$status) {
    die("Dados inválidos.");
}

$stmt = $pdo->prepare("UPDATE chamados SET status = ?, dt_fechamento = (CASE WHEN ? = 'Fechado' THEN NOW() ELSE NULL END) WHERE id = ?");
$stmt->execute([$status, $status, $chamado_id]);

header("Location: detalhesChamado.php?id=$chamado_id");
exit;
