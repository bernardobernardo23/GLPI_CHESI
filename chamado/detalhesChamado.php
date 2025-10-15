<?php
session_start();
require_once '../db_connection.php';

// ------------------------
// Verifica login e acesso
// ------------------------
if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../index.php');
  exit;
}

$usuario_setor = $_SESSION['usuario_setor'] ?? '';
$usuario_admin = ($usuario_setor === 'TI');

$chamado_id = $_GET['id'] ?? 0;

// ------------------------
// Busca informações do chamado
// ------------------------
$stmt = $pdo->prepare("
    SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor, u.email AS autor_email
    FROM chamados c
    JOIN usuarios u ON c.autor_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$chamado_id]);
$chamado = $stmt->fetch();

if (!$chamado) {
  die("Chamado não encontrado.");
}

// ------------------------
// Busca atualizações
// ------------------------
$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS usuario_nome
    FROM chamado_atualizacoes a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.chamado_id = ?
    ORDER BY a.data ASC
");
$stmt->execute([$chamado_id]);
$atualizacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Detalhes do Chamado</title>
  <style>
    /* Estilo para o modal de zoom */
    .modal {
      display: none;
      position: fixed;
      z-index: 50;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.8);
      justify-content: center;
      align-items: center;
    }

    .modal img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen flex">

  <?php include '../areaLateral.php'; ?>

  <main class="flex-1 ml-64 p-8">
    <div class="bg-white p-8 rounded shadow w-full max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?= htmlspecialchars($chamado['titulo']) ?></h1>

        <?php if ($usuario_admin && $chamado['status'] != 'Fechado'):   ?>
          <div class="flex gap-3">
            <form action="updateStatus.php" method="post" class="flex gap-2 items-center">
              <input type="hidden" name="id" value="<?= $chamado_id ?>">
              <select name="status" class="border p-2 rounded">
                <option value="Aberto" <?= $chamado['status'] == 'Aberto' ? 'selected' : '' ?>>Aberto</option>
                <option value="Em andamento" <?= $chamado['status'] == 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                <option value="Fechado" <?= $chamado['status'] == 'Fechado' ? 'selected' : '' ?>>Fechado</option>
              </select>
              <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Atualizar</button>
            </form>
              <a href="adicionarAtualizacao.php?id=<?= $chamado_id ?>"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                + Adicionar atualização
              </a>
          </div>
        <?php endif; ?>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div>
          <p><strong>Tipo:</strong> <?= ucfirst($chamado['tipo']) ?></p>
          <p><strong>Status:</strong> <?= $chamado['status'] ?></p>
          <p><strong>Autor:</strong> <?= htmlspecialchars($chamado['autor_nome']) ?> (<?= htmlspecialchars($chamado['autor_setor']) ?>)</p>
          <p><strong>Aberto em:</strong> <?= $chamado['dt_abertura'] ?></p>
        </div>

        <?php if ($chamado['imagem_path']): ?>
          <div class="flex justify-center">
            <img src="../<?= $chamado['imagem_path'] ?>"
              alt="Imagem do chamado"
              class="max-h-64 rounded border cursor-pointer hover:opacity-90 transition"
              onclick="openModal('../<?= $chamado['imagem_path'] ?>')">
          </div>
        <?php endif; ?>
      </div>

      <div class="bg-gray-50 p-4 rounded mb-8 border border-gray-200">
        <h2 class="text-lg font-semibold mb-2">Descrição</h2>
        <p><?= nl2br(htmlspecialchars($chamado['descricao'])) ?></p>
      </div>

      <h2 class="text-2xl font-semibold mb-4">Atualizações</h2>
      <?php if (count($atualizacoes) > 0): ?>
        <div class="space-y-4">
          <?php foreach ($atualizacoes as $a): ?>
            <div class="border border-gray-200 p-4 rounded bg-gray-50">
              <p class="text-sm text-gray-600 mb-1">
                <strong><?= htmlspecialchars($a['usuario_nome']) ?></strong> em <?= $a['data'] ?>
              </p>
              <p><?= nl2br(htmlspecialchars($a['descricao'])) ?></p>
              <?php if ($a['imagem_path']): ?>
                <img src="<?= $a['imagem_path'] ?>"
                  alt="Imagem da atualização"
                  class="max-h-48 mt-2 rounded border cursor-pointer hover:opacity-90"
                  onclick="openModal('<?= $a['imagem_path'] ?>')">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-500">Nenhuma atualização ainda.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- Modal para zoom da imagem -->
  <div id="imageModal" class="modal" onclick="closeModal()">
    <img id="modalImage" src="">
  </div>

  <script>
    function openModal(src) {
      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      modalImg.src = src;
      modal.style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('imageModal').style.display = 'none';
    }
  </script>

</body>

</html>