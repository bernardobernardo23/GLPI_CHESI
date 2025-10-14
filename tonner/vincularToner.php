<?php
session_start();
require '../db_connection.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$mensagem = "";

// Quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipamento_id = $_POST['equipamento_id'];
    $item_id = $_POST['item_id'];
    $cor = $_POST['cor'];

    $stmt = $pdo->prepare("INSERT INTO impressora_tonner (equipamento_id, item_id, cor, ativo) VALUES (?, ?, ?, 1)");
    if ($stmt->execute([$equipamento_id, $item_id, $cor])) {
        $mensagem = "✅ Toner vinculado com sucesso!";
    } else {
        $mensagem = "❌ Erro ao vincular toner.";
    }
}

// Busca impressoras (equipamentos do tipo impressora)
$equipamentos = $pdo->query("SELECT id, descricao FROM equipamentos WHERE tipo = 'impressora' ORDER BY descricao ASC")->fetchAll(PDO::FETCH_ASSOC);

// Busca toners disponíveis
$toners = $pdo->query("SELECT id, nome FROM itens WHERE tipo = 'toner' ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Vincular Toner a Impressora</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 h-screen bg-gray-900 text-white p-5">
        <h2 class="text-xl font-bold mb-6">Painel GLPI</h2>
        <ul>
            <li class="mb-3"><a href="../dashboard.php" class="hover:underline">🏠 Dashboard</a></li>
            <li class="mb-3"><a href="../tonner/listarToners.php" class="hover:underline">📄 Listar Toners</a></li>
            <li class="mb-3"><a href="vincularToner.php" class="text-yellow-400 font-bold">🔗 Vincular Toner</a></li>
        </ul>
    </div>

    <!-- Conteúdo -->
    <div class="flex-1 p-10">
        <h1 class="text-2xl font-bold mb-6">🔗 Vincular Toner a Impressora</h1>

        <?php if ($mensagem): ?>
            <div class="mb-4 p-3 rounded <?= strpos($mensagem, '✅') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-6 rounded-lg shadow-md w-full max-w-lg">
            <div class="mb-4">
                <label for="equipamento_id" class="block text-gray-700 font-medium mb-2">Impressora:</label>
                <select name="equipamento_id" id="equipamento_id" required class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Selecione uma impressora</option>
                    <?php foreach ($equipamentos as $eq): ?>
                        <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['descricao']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="item_id" class="block text-gray-700 font-medium mb-2">Toner:</label>
                <select name="item_id" id="item_id" required class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Selecione um toner</option>
                    <?php foreach ($toners as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="cor" class="block text-gray-700 font-medium mb-2">Cor:</label>
                <input type="text" name="cor" id="cor" placeholder="Ex: Preto, Azul, Amarelo" class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                Vincular
            </button>
        </form>
    </div>
</div>
</body>
</html>
