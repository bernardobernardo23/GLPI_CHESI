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
$itens = $pdo->query("SELECT * FROM itens ORDER BY nome")->fetchAll();
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

<!-- CONTEÚDO PRINCIPAL -->
<main class="flex-1 ml-64 p-8">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Abrir Novo Chamado</h1>

        <form id="formChamado" action="handler.php" method="post" enctype="multipart/form-data" class="space-y-4">

            <!-- Tipo de Solicitação -->
            <div>
                <label class="block font-medium">Tipo de solicitação</label>
                <select id="tipoSelect" name="tipo" class="border p-2 rounded w-full" required>
                    <option value="">Selecione...</option>
                    <option value="toner">Solicitação de Toner</option>
                    <option value="material">Solicitação de Material</option>
                    <option value="geral">Solicitação Geral</option>
                </select>
            </div>

            <!-- Solicitação de Toner -->
            <div id="tonerFields" class="hidden">
                <label class="block mt-3">Impressora</label>
                <select id="impressoraSelect" name="equipamento_id" class="border p-2 rounded w-full">
                    <option value="">Selecione...</option>
                    <?php foreach($equipamentos as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['descricao']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="block mt-3">Toner vinculado</label>
                <input id="tonerNome" type="text" class="border p-2 rounded w-full bg-gray-100" readonly>
                <input id="tonerId" type="hidden" name="item_id">

                <label class="block mt-3">Quantidade</label>
                <input name="quantidade" type="number" min="1" value="1" class="border p-2 rounded w-32 mt-1" placeholder="Qtd">
            </div>

            <!-- Solicitação de Material -->
            <div id="materialFields" class="hidden">
                <label class="block mt-3">Item de Material</label>
                <select name="item_id" class="border p-2 rounded w-full">
                    <option value="">Selecione...</option>
                    <?php foreach($itens as $i): if($i['tipo']=='suprimento' || $i['tipo']=='outro'): ?>
                        <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nome']) ?> (<?= $i['quantidade'] ?> em estoque)</option>
                    <?php endif; endforeach; ?>
                </select>

                <label class="block mt-3">Quantidade</label>
                <input name="quantidade" type="number" min="1" value="1" class="border p-2 rounded w-32 mt-1" placeholder="Qtd">
            </div>

            <!-- Solicitação Geral -->
            <div id="geralFields" class="hidden">
                <input name="titulo" placeholder="Assunto" class="border p-2 rounded w-full">
                <textarea name="descricao" placeholder="Descrição" class="border p-2 rounded w-full mt-2" rows="4"></textarea>

                <label class="block mt-2">Imagem (opcional)</label>
                <input type="file" name="imagem" accept="image/*" class="border p-2 rounded w-full">
            </div>

            <!-- Botão Enviar -->
            <button class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">Enviar</button>
        </form>
    </div>
</main>

<script>
// alterna os campos conforme o tipo
const tipo = document.getElementById('tipoSelect');
const tonerFields = document.getElementById('tonerFields');
const materialFields = document.getElementById('materialFields');
const geralFields = document.getElementById('geralFields');

tipo.addEventListener('change', ()=>{
    const v = tipo.value;
    tonerFields.classList.toggle('hidden', v !== 'toner');
    materialFields.classList.toggle('hidden', v !== 'material');
    geralFields.classList.toggle('hidden', v !== 'geral');
});

// busca toner automaticamente ao escolher impressora
const impressoraSelect = document.getElementById('impressoraSelect');
const tonerNome = document.getElementById('tonerNome');
const tonerId = document.getElementById('tonerId');

if(impressoraSelect){
    impressoraSelect.addEventListener('change', ()=>{
        const equipamentoId = impressoraSelect.value;
        tonerNome.value = 'Buscando...';
        tonerId.value = '';

        if(!equipamentoId){
            tonerNome.value = '';
            return;
        }

        fetch(`getTonerPorImpressora.php?equipamento_id=${equipamentoId}`)
            .then(resp => resp.json())
            .then(data => {
                if(data.success){
                    tonerNome.value = data.nome_toner + (data.cor ? ' (' + data.cor + ')' : '');
                    tonerId.value = data.item_id;
                } else {
                    tonerNome.value = 'Nenhum toner vinculado';
                }
            })
            .catch(err => {
                tonerNome.value = 'Erro ao buscar toner';
                console.error(err);
            });
    });
}
</script>

</body>
</html>
