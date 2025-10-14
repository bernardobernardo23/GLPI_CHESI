<?php
require_once '../db_connection.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM chamados  ORDER BY dt_abertura DESC");
$chamados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Meus Chamados</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
<h1 class="text-2xl font-bold mb-4">Meus Chamados</h1>
<a href="novo.php" class="bg-blue-600 text-white px-3 py-2 rounded">Novo Chamado</a>

<table class="w-full border mt-4">
<thead class="bg-gray-200">
<tr>
  <th class="border p-2">Tipo</th>
  <th class="border p-2">Título</th>
  <th class="border p-2">Status</th>
  <th class="border p-2">Data Abertura</th>
</tr>
</thead>
<tbody>
<?php foreach($chamados as $c): ?>
<tr class="hover:bg-gray-50">
  <td class="border p-2 text-center"><?=$c['tipo']?></td>
  <td class="border p-2"><?=$c['titulo']?></td>
  <td class="border p-2 text-center"><?=$c['status']?></td>
  <td class="border p-2 text-center"><?=date('d/m/Y H:i', strtotime($c['dt_abertura']))?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>
