<?php
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $tipo = $_POST['tipo'];
    $patrimonio = $_POST['patrimonio'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO equipamentos (descricao, tipo, patrimonio) VALUES (?, ?, ?)");
    $stmt->execute([$descricao, $tipo, $patrimonio]);
    header('Location: listar.php?msg=Equipamento cadastrado');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<script src="https://cdn.tailwindcss.com"></script>
<title>Cadastrar Equipamento</title>
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
<h1 class="text-xl font-bold mb-4">Cadastrar Equipamento</h1>
<form method="post" class="space-y-3">
  <input name="descricao" placeholder="Descrição do equipamento" class="border p-2 rounded w-full" required>
  <input name="tipo" placeholder="Tipo (Impressora, PC...)" class="border p-2 rounded w-full" required>
  <input name="patrimonio" placeholder="Nº Patrimônio (opcional)" class="border p-2 rounded w-full">
  <button class="bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
</form>
</div>
</body>
</html>
