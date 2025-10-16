<?php
// Arquivo: estoque/registrarBaixa.php

session_start();
require_once '../db_connection.php'; 

$item_id = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);
if (!$item_id) {
    die("Item não especificado.");
}

$stmt_item = $pdo->prepare("SELECT nome, quantidade FROM itens WHERE id = ?");
$stmt_item->execute([$item_id]);
$item = $stmt_item->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item não encontrado.");
}
$item_nome = htmlspecialchars($item['nome']);
$saldo_atual = $item['quantidade'];
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Registrar Baixa de Estoque</title>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../areaLateral.php'; ?>
    <main class="flex-1 ml-64 p-10">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
            
            <h1 class="text-3xl font-bold text-gray-900 mb-6 border-b pb-4">Registrar Baixa (Saída)</h1>
            
            <a href="javascript:history.back()" class="text-gray-500 hover:text-gray-700 text-sm mb-4 inline-block">&larr; Voltar para Movimentações</a>
            
            <div class="bg-red-50 border border-red-300 p-4 rounded-lg mb-6">
                <p class="text-red-700 font-semibold">Item: <?= $item_nome ?></p>
                <p class="text-red-700">Estoque Disponível: <span class="font-bold text-xl"><?= $saldo_atual ?></span></p>
            </div>

            <form action="handler_baixa.php" method="POST" class="space-y-6">
                <input type="hidden" name="item_id" value="<?= $item_id ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantidade da Baixa</label>
                    <input type="number" name="quantidade" min="1" max="<?= $saldo_atual ?>" required 
                           class="mt-1 block w-full border border-gray-300 p-2 rounded-md text-lg" 
                           placeholder="Qtd">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Motivo da Baixa</label>
                    <textarea name="motivo" required rows="3" class="mt-1 block w-full border border-gray-300 p-2 rounded-md" placeholder="Ex: Item utilizado no Chamado #123, Danificado, Descarte, etc."></textarea>
                </div>

                <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 text-lg font-bold shadow-xl">
                    Registrar Saída
                </button>
            </form>

        </div>
    </main>
</body>
</html>