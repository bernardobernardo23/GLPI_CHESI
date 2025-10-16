<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$nome_usuario = $_SESSION['usuario_nome'];
$setor = $_SESSION['usuario_setor'] ?? 'Usuário';
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Painel - Sistema GLPI Simplificado</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

<?php include 'areaLateral.php'; ?>

<main class="flex-1 ml-64 p-10">
    <h1 class="text-2xl font-bold mb-4">Painel Principal</h1>
    <p class="text-gray-700">
        Bem-vindo ao sistema GLPI.  
        Use o menu para navegar entre as funções.
    </p>

    <?php if ($setor == 'TI'): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        
        <a href="equipamento/listar.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                </svg>
                Equipamentos
            </h2>
            <p class="text-gray-500 text-sm">Gerencie todos os equipamentos cadastrados.</p>
        </a>
        
        <a href="estoque/listarItens.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                Estoque
            </h2>
            <p class="text-gray-500 text-sm">Controle as entradas e saídas de materiais.</p>
        </a>
        
        <a href="chamado/listarChamados.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-yellow-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Chamados
            </h2>
            <p class="text-gray-500 text-sm">Visualize e gerencie todos os chamados do sistema.</p>
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <a href="chamado/novo.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Novo Chamado
            </h2>
            <p class="text-gray-500 text-sm">Solicite toner, materiais ou suporte técnico.</p>
        </a>
        
        <a href="chamado/listarChamados.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-purple-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Meus Chamados
            </h2>
            <p class="text-gray-500 text-sm">Acompanhe o status das suas solicitações.</p>
        </a>
    </div>
    <?php endif; ?>
</main>

</body>
</html>