<?php
session_start(); 
require_once '../db_connection.php';

// Busca dados de equipamentos
$stmt = $pdo->query("SELECT id, descricao, tipo FROM equipamentos ORDER BY tipo, descricao");
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mapeamento de classes para o badge de tipo
function getTipoBadgeClassEquipamento($tipo) {
    switch (strtolower($tipo)) {
        case 'impressora':
            return 'bg-purple-100 text-purple-800';
        case 'computador':
            return 'bg-teal-100 text-teal-800';
        case 'notebook':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-700';
    }
}

// Ícones para os botões de ação (mantidos)
$icon_novo = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>';
$icon_vincular = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m13.19 8.627 3.038 3.04.577-.577a2.25 2.25 0 0 1 3.18 0l.55.55a2.25 2.25 0 0 1 0 3.18l-1.55 1.55a2.25 2.25 0 0 1-3.18 0l-.577-.577m-1.575-3.038-3.038-3.04a2.25 2.25 0 0 0-3.18 0l-.55.55a2.25 2.25 0 0 0 0 3.18l1.55 1.55a2.25 2.25 0 0 0 3.18 0l.577-.577m-1.575-3.038-3.038-3.04a2.25 2.25 0 0 0-3.18 0l-.55.55a2.25 2.25 0 0 0 0 3.18l1.55 1.55a2.25 2.25 0 0 0 3.18 0l.577-.577" /></svg>';
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Inventário de Equipamentos</title>
</head>

<body class="bg-gray-100 min-h-screen flex">
    <?php include '../areaLateral.php'; ?>
    <main class="flex-1 ml-64 p-10">
        <div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-4 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-4 sm:mb-0">Inventário de Equipamentos</h1>
                <div class="flex space-x-3">
                    
                    <a href="cadastrar.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-150 flex items-center shadow-md">
                        <?= $icon_novo ?>
                        Novo
                    </a>
                    
                    <a href="../tonner/vincularToner.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-150 flex items-center shadow-md">
                        <?= $icon_vincular ?>
                        Vincular Toner
                    </a>
                </div>
            </div>

            <?php if (!empty($dados)): ?>
            <div class="overflow-x-auto shadow-lg rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/3">
                                Descrição
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/6">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/6">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($dados as $d): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($d['descricao']) ?></div>
                                </td>
                                

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= getTipoBadgeClassEquipamento($d['tipo']) ?>">
                                        <?= htmlspecialchars(ucfirst($d['tipo'])) ?>
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="detalhes.php?id=<?= $d['id'] ?>" class="text-blue-600 hover:text-blue-800 transition">
                                        Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-lg text-gray-500 p-4 border border-gray-300 rounded-lg bg-gray-50">Nenhum equipamento cadastrado.</p>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>