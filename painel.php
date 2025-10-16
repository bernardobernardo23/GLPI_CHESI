
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

<!-- SIDEBAR -->
<?php include 'areaLateral.php'; ?>

<!-- CONTEÚDO PRINCIPAL -->
<main class="flex-1 ml-64 p-10">
    <h1 class="text-2xl font-bold mb-4">Painel Principal</h1>
    <p class="text-gray-700">
        Bem-vindo ao sistema GLPI.  
        Use o menu para navegar entre as funções.
    </p>

    <?php if ($setor == 'TI'): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <a href="equipamento/listar.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2">🖥️ Equipamentos</h2>
            <p class="text-gray-500 text-sm">Gerencie todos os equipamentos cadastrados.</p>
        </a>
        <a href="estoque/listarItens.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2">📦 Estoque</h2>
            <p class="text-gray-500 text-sm">Controle as entradas e saídas de materiais.</p>
        </a>
        <a href="chamado/listarChamados.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2">🎫 Chamados</h2>
            <p class="text-gray-500 text-sm">Visualize e gerencie todos os chamados do sistema.</p>
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <a href="chamado/novo.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2">🎫 Novo Chamado</h2>
            <p class="text-gray-500 text-sm">Solicite toner, materiais ou suporte técnico.</p>
        </a>
        <a href="chamado/listarChamados.php" class="bg-white rounded shadow p-5 hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2">📋 Meus Chamados</h2>
            <p class="text-gray-500 text-sm">Acompanhe o status das suas solicitações.</p>
        </a>
    </div>
    <?php endif; ?>
</main>

</body>
</html>
