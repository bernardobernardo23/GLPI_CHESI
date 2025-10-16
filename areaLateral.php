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
        <a href="/GLPI_CHESI/painel.php" class="block px-3 py-2 rounded hover:bg-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.149-.439 1.588 0L21.75 12M4.5 9.75v10.125c0 .621.503 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21.75h7.5" />
            </svg>
            Início
        </a>

        <h2 class="text-gray-400 text-xs uppercase mt-4">Chamados</h2>

        <a href="/GLPI_CHESI/chamado/novo.php" class="block px-3 py-2 rounded hover:bg-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Chamado
        </a>

        <a href="/GLPI_CHESI/chamado/listarChamados.php" class="block px-3 py-2 rounded hover:bg-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            <?= ($setor == 'TI') ? 'Todos os Chamados' : 'Meus Chamados' ?>
        </a>

        <?php if ($setor == 'TI'): ?>

            <?php if ($setor == 'TI'): ?>
                <h2 class="text-gray-400 text-xs uppercase mt-4">Administração</h2>

                <a href="/GLPI_CHESI/equipamento/listar.php" class="block px-3 py-2 rounded hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                    Equipamentos
                </a>

                <a href="/GLPI_CHESI/estoque/listarItens.php" class="block px-3 py-2 rounded hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Itens de Estoque
                </a>

                <a href="/GLPI_CHESI/fornecedores/adm_entrada_estoque.php" class="block px-3 py-2 rounded hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Forncedores
                </a>

            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <!-- Rodapé -->
    <div class="p-4 border-t border-gray-700">
        <a href="/GLPI_CHESI/logout.php" class="block text-red-400 hover:text-red-500 font-semibold">Sair</a>
    </div>
</aside>