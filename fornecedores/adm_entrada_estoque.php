<?php
// Arquivo: estoque/registrarEntrada.php

session_start();
require_once '../db_connection.php'; 

// Busca listas necessárias
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$itens = $pdo->query("SELECT id, nome, tipo FROM itens ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC); 
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Registrar Entrada de Estoque</title>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../areaLateral.php'; ?>
    <main class="flex-1 ml-64 p-10">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
            
            <h1 class="text-3xl font-bold text-gray-900 mb-6 border-b pb-4">Registrar Entrada de Estoque</h1>

            <form action="../estoque/handler_entrada.php" method="POST" class="space-y-6">
                
                <fieldset class="p-6 border rounded-lg space-y-4">
                    <legend class="text-lg font-semibold text-gray-700 px-2">Dados da Compra</legend>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nota Fiscal</label>
                        <input type="text" name="nota_fiscal" required class="mt-1 block w-full border border-gray-300 p-2 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fornecedor</label>
                        <select name="fornecedor_id" required class="mt-1 block w-full border border-gray-300 p-2 rounded-md">
                            <option value="">Selecione o Fornecedor</option>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Gerencie fornecedores em <a href="adm_fornecedores.php" class="text-blue-600 hover:underline">Fornecedores</a></p>
                    </div>
                </fieldset>
                
                <fieldset class="p-6 border border-dashed rounded-lg space-y-4 bg-gray-50">
                    <legend class="text-lg font-semibold text-gray-700 px-2">Itens Recebidos</legend>
                    
                    <div id="itens-container" class="space-y-4">
                        </div>
                    
                    <button type="button" onclick="adicionarItem()" class="bg-gray-400 text-white px-4 py-2 rounded-md hover:bg-gray-500 flex items-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Adicionar Item
                    </button>
                </fieldset>

                <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 text-lg font-bold shadow-xl">
                    Registrar Entrada
                </button>
            </form>

        </div>
    </main>

    <script>
        // Dados de itens disponíveis para o JavaScript
        const itensDisponiveis = <?= json_encode($itens) ?>;
        let contadorItem = 0;

        /**
         * Cria e adiciona uma nova linha de item ao formulário.
         */
        function adicionarItem() {
            contadorItem++;
            const container = document.getElementById('itens-container');
            const novaLinha = document.createElement('div');
            novaLinha.id = `item-row-${contadorItem}`;
            novaLinha.className = 'flex gap-4 items-end bg-white p-3 rounded-md shadow-sm border';

            let options = '<option value="">Selecione o Item</option>';
            itensDisponiveis.forEach(item => {
                options += `<option value="${item.id}">${item.nome} (${item.tipo})</option>`;
            });

            novaLinha.innerHTML = `
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500">Item</label>
                    <select name="itens[${contadorItem}][item_id]" required class="mt-1 block w-full border border-gray-300 p-2 rounded-md text-sm">
                        ${options}
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-gray-500">Qtd</label>
                    <input type="number" name="itens[${contadorItem}][quantidade]" min="1" value="1" required class="mt-1 block w-full border border-gray-300 p-2 rounded-md text-sm text-center">
                </div>
                <div>
                    <button type="button" onclick="removerItem(${contadorItem})" class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            `;
            container.appendChild(novaLinha);
        }

        /**
         * Remove uma linha de item do formulário.
         */
        function removerItem(id) {
            document.getElementById(`item-row-${id}`).remove();
        }

        // Adiciona um item inicial ao carregar a página
        document.addEventListener('DOMContentLoaded', adicionarItem);
    </script>
</body>
</html>