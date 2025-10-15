<?php
session_start();
require_once '../db_connection.php';

// Usuário logado
$usuario_id = $_SESSION['usuario_id'];
$usuario_setor = $_SESSION['usuario_setor'] ?? '';

// Determina se é admin pelo setor
$usuario_admin = ($usuario_setor === 'TI');

// Busca os chamados
if ($usuario_admin) {
    $stmt = $pdo->query("
        SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor
        FROM chamados c
        JOIN usuarios u ON c.autor_id = u.id
        ORDER BY c.dt_abertura DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor
        FROM chamados c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.autor_id = ?
        ORDER BY c.dt_abertura DESC
    ");
    $stmt->execute([$usuario_id]);
}

$chamados = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Lista de Chamados</title>
</head>
<body class="bg-gray-100 min-h-screen flex">

<?php include '../areaLateral.php'; ?>

<main class="flex-1 ml-64 p-8">
  <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Lista de Chamados</h1>

    <table class="w-full border border-gray-300">
      <thead class="bg-gray-200">
        <tr>
          <th class="border p-2">ID</th>
          <th class="border p-2">Tipo</th>
          <th class="border p-2">Título</th>
          <th class="border p-2">Status</th>
          <th class="border p-2">Autor</th>
          <th class="border p-2">Abertura</th>
          <th class="border p-2">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($chamados as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="border p-2 text-center"><?= $c['id'] ?></td>
            <td class="border p-2"><?= ucfirst($c['tipo']) ?></td>
            <td class="border p-2"><?= htmlspecialchars($c['titulo']) ?></td>
            <td class="border p-2"><?= $c['status'] ?></td>
            <td class="border p-2"><?= htmlspecialchars($c['autor_nome']) ?> (<?= htmlspecialchars($c['autor_setor']) ?>)</td>
            <td class="border p-2"><?= $c['dt_abertura'] ?></td>
            <td class="border p-2 text-center">
              <?php if ($usuario_admin): ?>
                <a href="detalhesChamado.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline">Ver detalhes</a>
              <?php else: ?>
                <span class="text-gray-500">Visualizar</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
