<?php
session_start();
require_once '../db_connection.php';

// Verifica se usuário é admin pelo setor
$usuario_setor = $_SESSION['usuario_setor'] ?? '';
if($usuario_setor !== 'TI'){
    die("Acesso negado");
}

$chamado_id = $_GET['id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<script src="https://cdn.tailwindcss.com"></script>
<title>Adicionar Atualização</title>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
<h1 class="text-2xl font-bold mb-4">Adicionar Atualização</h1>

<form action="handlerAtualizacao.php" method="post" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="chamado_id" value="<?= htmlspecialchars($chamado_id) ?>">
    
    <label class="block font-medium">Descrição da atualização</label>
    <textarea name="descricao" rows="4" class="border p-2 rounded w-full" required></textarea>

    <label class="block mt-2">Imagem (opcional)</label>
    <input type="file" name="imagem" accept="image/*" class="border p-2 rounded w-full">

    <button class="bg-blue-600 text-white px-5 py-2 rounded mt-2">Adicionar</button>
</form>
</div>

</body>
</html>
