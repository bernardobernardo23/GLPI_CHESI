<?php
session_start();
require_once '../db_connection.php';

// proteção de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// busca dados necessários
$equipamentos = $pdo->query("SELECT * FROM equipamentos WHERE tipo='Impressora'")->fetchAll();
// Não estamos usando 'quantidade' aqui, mas o campo é útil no select
$itens = $pdo->query("SELECT id, nome, tipo, quantidade FROM itens ORDER BY nome")->fetchAll(); 
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Novo Chamado - GLPI Simplificado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex">

    <?php include '../areaLateral.php'; ?>

    <main class="flex-1 ml-64 p-8">
        <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6">Abrir Novo Chamado</h1>

            <form id="formChamado" action="handler.php" method="post" enctype="multipart/form-data" class="space-y-4">

                <div>
                    <label class="block font-medium">Tipo de solicitação</label>
                    <select id="tipoSelect" name="tipo" class="border p-2 rounded w-full" required>
                        <option value="">Selecione...</option>
                        <option value="toner">Solicitação de Toner</option>
                        <option value="material">Solicitação de Material</option>
                        <option value="geral">Solicitação Geral</option>
                    </select>
                </div>

                <div id="tonerFields" class="hidden">
                    <label class="block mt-3">Impressora</label>
                    <select id="impressoraSelect" name="equipamento_id" class="border p-2 rounded w-full">
                        <option value="">Selecione...</option>
                        <?php foreach ($equipamentos as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['descricao']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div id="tonerSelectWrapper" class="mt-3"></div>

                    <label class="block mt-3">Quantidade (Toner)</label>
                    <input name="quantidade_toner" type="number" min="1" value="1" class="border p-2 rounded w-32 mt-1" placeholder="Qtd">
                </div>

                <div id="materialFields" class="hidden">
                    <label class="block mt-3">Item de Material</label>
                    <select name="material_item_id" class="border p-2 rounded w-full">
                        <option value="">Selecione...</option>
                        <?php foreach ($itens as $i): ?>
                            <?php if ($i['tipo'] === 'suprimento' || $i['tipo'] === 'outro'): ?>
                                <option value="<?= $i['id'] ?>">
                                    <?= htmlspecialchars($i['nome']) ?> (<?= $i['quantidade'] ?> em estoque)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <label class="block mt-3">Quantidade (Material)</label>
                    <input name="quantidade_material" type="number" min="1" value="1"
                        class="border p-2 rounded w-32 mt-1" placeholder="Qtd" id="quantidadeMaterialInput">
                </div>


                <div id="geralFields" class="hidden">
                    <input name="titulo" placeholder="Assunto" class="border p-2 rounded w-full">
                    <textarea name="descricao" placeholder="Descrição" class="border p-2 rounded w-full mt-2" rows="4"></textarea>

                    <label class="block mt-2">Imagem (opcional)</label>
                    <input type="file" name="imagem" accept="image/*" class="border p-2 rounded w-full">
                </div>

                <button class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">Enviar</button>
            </form>
        </div>
    </main>

    <script>
        // 1. Variáveis de Toggle
        const tipo = document.getElementById('tipoSelect');
        const tonerFields = document.getElementById('tonerFields');
        const materialFields = document.getElementById('materialFields');
        const geralFields = document.getElementById('geralFields');

        // 2. Variáveis Específicas de Toner/AJAX
        const impressoraSelect = document.getElementById('impressoraSelect');
        const tonerSelectWrapper = document.getElementById('tonerSelectWrapper');

        // 3. Lógica de Mostrar/Esconder Campos
        tipo.addEventListener('change', () => {
            const v = tipo.value;
            tonerFields.classList.toggle('hidden', v !== 'toner');
            materialFields.classList.toggle('hidden', v !== 'material');
            geralFields.classList.toggle('hidden', v !== 'geral');

            // Limpa mensagens de erro ao trocar o tipo
            tonerSelectWrapper.innerHTML = '';
        });

        // 4. Lógica de Busca Dinâmica de Toner
        if (impressoraSelect) {
            impressoraSelect.addEventListener('change', () => {
                const equipamentoId = impressoraSelect.value;
                tonerSelectWrapper.innerHTML = '<p class="text-gray-500">Carregando...</p>';

                if (!equipamentoId) {
                    tonerSelectWrapper.innerHTML = '';
                    return;
                }

                fetch(`../tonner/getTonerPorImpressora.php?equipamento_id=${equipamentoId}`)
                    .then(resp => resp.json())
                    .then(data => {
                        if (data.success && data.toners.length > 0) {

                            let htmlOutput = '';

                            if (data.toners.length === 1) {
                                // Impressora Mono (Toner Único)
                                const t = data.toners[0];
                                htmlOutput = `
                                    <label class="block font-medium">Toner vinculado</label>
                                    <input type="text" class="border p-2 rounded w-full bg-gray-100"
                                        value="${t.nome_toner}${t.cor ? ' (' + t.cor + ')' : ''}" readonly>
                                    <input type="hidden" name="item_id" value="${t.item_id}">
                                `;

                            } else {
                                // Impressora Colorida (Seleção de cor/item)
                                htmlOutput = `
                                    <label class="block font-medium">Selecione o toner/cor</label>
                                    <select name="item_id" required class="border p-2 rounded w-full"> 
                                        <option value="">Selecione...</option>
                                        ${data.toners.map(t =>
                                            `<option value="${t.item_id}">${t.nome_toner}${t.cor ? ' (' + t.cor + ')' : ''}</option>`
                                        ).join('')}
                                    </select>
                                `;
                            }
                            tonerSelectWrapper.innerHTML = htmlOutput;

                        } else {
                            tonerSelectWrapper.innerHTML = `<p class="text-red-600">Nenhum toner vinculado.</p>`;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        tonerSelectWrapper.innerHTML = '<p class="text-red-600">Erro ao buscar toner.</p>';
                    });
            });
        }
    </script>


</body>

</html>