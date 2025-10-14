<?php
require_once '../db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $quantidade = $_POST['quantidade'];

    $stmt = $pdo->prepare("INSERT INTO itens (nome, tipo, descricao, quantidade) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $tipo, $descricao, $quantidade]);
    header('Location: listarItens.php?msg=Item cadastrado');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<script src="https://cdn.tailwindcss.com"></script>
<title>Cadastrar Item</title>
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
<h1 class="text-xl font-bold mb-4">Cadastrar Item</h1>
<form method="post" class="space-y-3">
  <input name="nome" placeholder="Nome do item" class="border p-2 rounded w-full" required>
  <select name="tipo" class="border p-2 rounded w-full" required>
    <option value="suprimento">Suprimento</option>
    <option value="toner">Toner</option>
    <option value="outro">Outro</option>
  </select>
  <textarea name="descricao" placeholder="Descrição" class="border p-2 rounded w-full"></textarea>
  <input name="quantidade" type="number" min="0" class="border p-2 rounded w-full" placeholder="Quantidade inicial">
  <button class="bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
</form>
</div>
</body>
</html>
