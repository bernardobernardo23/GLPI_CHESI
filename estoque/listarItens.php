<?php
require_once '../db_connection.php';
$itens = $pdo->query("SELECT * FROM itens ORDER BY nome")->fetchAll();
?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Itens em Estoque</title>
</head>

<body class="bg-gray-100 min-h-screen flex">
  <?php include '../areaLateral.php'; ?>
  <main class="flex-1 ml-64 p-10">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
      <h1 class="text-2xl font-bold mb-4">Estoque</h1>
      <a href="cadastrarItem.php" class="bg-blue-600 text-white px-3 py-1 rounded">Novo Item</a>
      <table class="w-full border mt-3">
        <thead class="bg-gray-200">
          <tr>
            <th class="border p-2">Nome</th>
            <th class="border p-2">Tipo</th>
            <th class="border p-2">Qtd</th>
            <th class="border p-2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($itens as $i): ?>
            <tr class="hover:bg-gray-50">
              <td class="border p-2"><?= $i['nome'] ?></td>
              <td class="border p-2"><?= $i['tipo'] ?></td>
              <td class="border p-2 text-center"><?= $i['quantidade'] ?></td>
              <td class="border p-2 text-center">
                <a href="movimentar.php?id=<?= $i['id'] ?>" class="text-blue-600 hover:underline">Movimentar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>

</html>