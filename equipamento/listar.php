<?php
require_once '../db_connection.php';
$dados = $pdo->query("SELECT * FROM equipamentos ORDER BY descricao")->fetchAll();
?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Listar Equipamentos</title>
</head>

<body class="bg-gray-100 min-h-screen flex">
  <?php include '../areaLateral.php'; ?>
  <main class="flex-1 ml-64 p-10">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
      <h1 class="text-2xl font-semibold mb-4">Equipamentos</h1>
      <a href="cadastrar.php" class="bg-blue-600 text-white px-3 py-1 rounded">Novo</a>
      <a href="../tonner/vincularToner.php" class="bg-green-600 text-white px-3 py-1 rounded">Vincular Tonner</a>
      <table class="w-full border mt-3">
        <thead class="bg-gray-200">
          <tr>
            <th class="border p-2">Descrição</th>
            <th class="border p-2">Tipo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dados as $d): ?>
            <tr class="hover:bg-gray-50">
              <td class="border p-2"><?= htmlspecialchars($d['descricao']) ?></td>
              <td class="border p-2"><?= $d['tipo'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>

</html>