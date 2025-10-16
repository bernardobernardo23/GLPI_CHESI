<?php
// Arquivo: estoque/movimentacoes.php

session_start();
require_once '../db_connection.php';

// SOLUÇÃO: Tenta buscar 'item_id' (melhor prática para estoque). Se não encontrar, tenta buscar 'id'.
$item_id = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);

// Se 'item_id' for nulo ou 0, verifica se 'id' foi passado.
if (!$item_id) {
    $item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

// O resto do código usa $item_id normalmente.
if (!$item_id) {
    die("Item não especificado.");
}
// 1. Busca dados do item (nome para o título)
$stmt_item = $pdo->prepare("SELECT nome, quantidade FROM itens WHERE id = ?");
$stmt_item->execute([$item_id]);
$item = $stmt_item->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item não encontrado.");
}
$item_nome = htmlspecialchars($item['nome']);
$saldo_atual = $item['quantidade'];

// 2. Busca todas as movimentações do item
$stmt_mov = $pdo->prepare("
    SELECT 
        m.tipo, 
        m.quantidade, 
        m.data_movimentacao,
        m.motivo,
        f.nome AS fornecedor_nome,
        u.nome AS usuario_nome
    FROM movimentacoes_estoque m
    LEFT JOIN fornecedores f ON m.fornecedor_id = f.id
    LEFT JOIN usuarios u ON m.usuario_id = u.id
    WHERE m.item_id = ?
    ORDER BY m.data_movimentacao DESC
");
$stmt_mov->execute([$item_id]);
$movimentacoes = $stmt_mov->fetchAll(PDO::FETCH_ASSOC);

// Função para estilizar o tipo de movimentação
function getMovimentacaoClass($tipo)
{
    return $tipo === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
}

// Mensagem de sucesso após movimentação
$mensagem_sucesso = '';
if (isset($_GET['success']) && $_GET['success'] === 'entrada') {
    $mensagem_sucesso = "✅ Entrada de estoque registrada com sucesso!";
} elseif (isset($_GET['success']) && $_GET['success'] === 'baixa') {
    $mensagem_sucesso = "✅ Baixa de estoque registrada com sucesso!";
}
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Movimentações - <?= $item_nome ?></title>
</head>

<body class="bg-gray-100 min-h-screen flex">
    <?php include '../areaLateral.php'; ?>
    <main class="flex-1 ml-64 p-10">
        <div class="max-w-7xl mx-auto bg-white p-8 rounded-xl shadow-2xl">

            <?php if ($mensagem_sucesso): ?>
                <div id="successAlert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Sucesso!</strong>
                    <span class="block sm:inline"><?= $mensagem_sucesso ?></span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-4 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-4 sm:mb-0">
                    Histórico de <?= $item_nome ?>
                </h1>

                <div class="flex space-x-3">
                    <div class="px-4 py-2 bg-gray-100 border rounded-lg text-lg font-bold text-gray-800 flex items-center">
                        Saldo Atual: <span class="ml-2 text-blue-600"><?= $saldo_atual ?></span>
                    </div>

                    <a href="registrarEntrada.php?item_id=<?= $item_id ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        + Entrada
                    </a>

                    <a href="registrarBaixa.php?item_id=<?= $item_id ?>" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        - Baixa
                    </a>
                </div>
            </div>

            <?php if (!empty($movimentacoes)): ?>
                <div class="overflow-x-auto shadow-lg rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Qtd</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Motivo</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Responsável</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fornecedor / NF</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($movimentacoes as $m): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?= date('d/m/Y H:i', strtotime($m['data_movimentacao'])) ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= getMovimentacaoClass($m['tipo']) ?>">
                                            <?= strtoupper($m['tipo']) ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-base font-bold <?= $m['tipo'] === 'entrada' ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= $m['tipo'] === 'baixa' ? '-' : '+' ?><?= $m['quantidade'] ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?= htmlspecialchars($m['motivo'] ?: 'N/A') ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                        <?= htmlspecialchars($m['usuario_nome'] ?: 'Sistema') ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?= htmlspecialchars($m['fornecedor_nome'] ?? '-') ?>

                                        <?php if (!empty($m['nota_fiscal'])): ?>
                                            <span class="text-xs block text-gray-500">NF: <?= htmlspecialchars($m['nota_fiscal']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-lg text-gray-500 p-4 border border-gray-300 rounded-lg bg-gray-50">Nenhuma movimentação registrada para este item.</p>
            <?php endif; ?>

        </div>
    </main>
    <script>
        // Script para fechar o alerta de sucesso
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.opacity = '0';
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                }, 3000);
                setTimeout(function() {
                    successAlert.remove();
                }, 3500);
            }
        });
    </script>
</body>

</html>