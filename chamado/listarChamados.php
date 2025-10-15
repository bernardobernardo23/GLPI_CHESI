<?php
session_start();
require_once '../db_connection.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_setor = $_SESSION['usuario_setor'] ?? '';
$usuario_admin = ($usuario_setor === 'TI');

// Filtro de status
$status_filtro = $_GET['status'] ?? 'todos';

$where = "";
$params = [];

if ($status_filtro !== 'todos') {
    $where .= " AND c.status = ?";
    $params[] = $status_filtro;
}

if ($usuario_admin) {
    $sql = "
        SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor
        FROM chamados c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE 1=1 $where
        ORDER BY c.dt_abertura DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $sql = "
        SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor
        FROM chamados c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.autor_id = ? $where
        ORDER BY c.dt_abertura DESC
    ";
    $params = array_merge([$usuario_id], $params);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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

    <!-- Filtro de status -->
    <form method="get" class="mb-4 flex gap-4 items-center">
      <label class="font-medium">Filtrar por status:</label>
      <select name="status" class="border p-2 rounded">
        <option value="todos" <?= $status_filtro==='todos'?'selected':'' ?>>Todos</option>
        <option value="Aberto" <?= $status_filtro==='Aberto'?'selected':'' ?>>Abertos</option>
        <option value="Fechado" <?= $status_filtro==='Fechado'?'selected':'' ?>>Fechados</option>
        <option value="Em andamento" <?= $status_filtro==='Em andamento'?'selected':'' ?>>Em andamento</option>
      </select>
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Filtrar</button>
    </form>

    <table class="w-full border border-gray-300 text-sm">
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
              <a href="detalhesChamado.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline">Ver detalhes</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
