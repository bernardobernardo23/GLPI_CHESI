<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$setor = $_SESSION['usuario_setor'] ?? 'Usuário';
?>

<!-- SIDEBAR FIXA -->
<aside class="w-64 bg-gray-800 text-white flex flex-col fixed h-full">
    <!-- Cabeçalho -->
    <div class="p-4 text-center border-b border-gray-700">
        <h1 class="text-lg font-bold">GLPI_CHESI</h1>
        <p class="text-sm mt-1 text-gray-400">Olá, <?= htmlspecialchars($nome_usuario) ?></p>
        <span class="text-xs text-green-400 font-semibold">
            <?= ($setor == 'TI') ? 'Administrador' : 'Usuário' ?>
        </span>
    </div>

    <!-- Menu -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <a href="/GLPI_CHESI/painel.php" class="block px-3 py-2 rounded hover:bg-gray-700">🏠 Início</a>

        <h2 class="text-gray-400 text-xs uppercase mt-4">Chamados</h2>
        <a href="/GLPI_CHESI/chamado/novo.php" class="block px-3 py-2 rounded hover:bg-gray-700">🎫 Novo Chamado</a>
        <a href="/GLPI_CHESI/chamado/listarChamados.php" class="block px-3 py-2 rounded hover:bg-gray-700">
            📋 <?= ($setor=='TI') ? 'Todos os Chamados' : 'Meus Chamados' ?>
        </a>

        <?php if ($setor == 'TI'): ?>
        <h2 class="text-gray-400 text-xs uppercase mt-4">Administração</h2>
        <a href="/GLPI_CHESI/equipamento/listar.php" class="block px-3 py-2 rounded hover:bg-gray-700">🖥️ Equipamentos</a>
        <a href="/GLPI_CHESI/estoque/listarItens.php" class="block px-3 py-2 rounded hover:bg-gray-700">📦 Itens de Estoque</a>
        <?php endif; ?>
    </nav>

    <!-- Rodapé -->
    <div class="p-4 border-t border-gray-700">
        <a href="/GLPI_CHESI/logout.php" class="block text-red-400 hover:text-red-500 font-semibold">Sair</a>
    </div>
</aside>
