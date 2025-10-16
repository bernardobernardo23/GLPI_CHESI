<?php
// Arquivo: estoque/fornecedores.php

session_start();
require_once '../db_connection.php'; // Sua conexão PDO

// Definição de Ícones (Para manter a consistência do menu Tailwind)
$icon_plus = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>';

$mensagem = '';
$nome = $cnpj = $telefone = $email = $endereco = '';

// ----------------------
// Lógica de Cadastro/Edição
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'salvar') {
    $nome = trim($_POST['nome']);
    $cnpj = trim($_POST['cnpj']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $endereco = trim($_POST['endereco']);
    $id = (int)$_POST['id_fornecedor'];

    try {
        if ($id > 0) {
            // Update (Edição)
            $sql = "UPDATE fornecedores SET nome = ?, cnpj = ?, telefone = ?, email = ?, endereco = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $cnpj, $telefone, $email, $endereco, $id]);
            $mensagem = "Fornecedor atualizado com sucesso!";
        } else {
            // Create (Novo Cadastro)
            $sql = "INSERT INTO fornecedores (nome, cnpj, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $cnpj, $telefone, $email, $endereco]);
            $mensagem = "Fornecedor '$nome' cadastrado com sucesso!";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // 23000 é o código para duplicidade (CNPJ)
            $mensagem = "Erro: CNPJ já cadastrado. " . $e->getMessage();
        } else {
            $mensagem = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}

// ----------------------
// Lógica de Leitura (Listagem)
// ----------------------
$fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>CRUD Fornecedores</title>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../areaLateral.php'; ?>
    <main class="flex-1 ml-64 p-10">
        <div class="max-w-7xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
            
            <h1 class="text-3xl font-bold text-gray-900 mb-6 border-b pb-4">Gerenciar Fornecedores</h1>

            <?php if ($mensagem): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <?= $mensagem ?>
            </div>
            <?php endif; ?>

            <h2 class="text-xl font-semibold mb-3">Novo Cadastro</h2>
            <form method="POST" class="space-y-4 bg-gray-50 p-6 rounded-lg border mb-8">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id_fornecedor" value="0" id="id_fornecedor">

                <input type="text" name="nome" placeholder="Nome do Fornecedor" required class="border p-2 rounded w-full">
                <input type="text" name="cnpj" placeholder="CNPJ" required class="border p-2 rounded w-full">
                <input type="text" name="telefone" placeholder="Telefone" class="border p-2 rounded w-full">
                <input type="email" name="email" placeholder="E-mail" class="border p-2 rounded w-full">
                <input type="text" name="endereco" placeholder="Endereço Completo" class="border p-2 rounded w-full">

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                    <?= $icon_plus ?> Salvar Fornecedor
                </button>
            </form>

            <h2 class="text-xl font-semibold mb-3">Fornecedores Cadastrados</h2>
            <div class="overflow-x-auto shadow-lg rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">CNPJ</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Contato</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($fornecedores as $f): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><?= htmlspecialchars($f['nome']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($f['cnpj']) ?></td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700"><?= htmlspecialchars($f['telefone'] ?: $f['email']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900" 
                                   onclick="preencherEdicao(<?= htmlspecialchars(json_encode($f)) ?>); return false;">
                                    Editar
                                </a>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
    <script>
    // Função JavaScript para preencher o formulário para edição
    function preencherEdicao(fornecedor) {
        document.getElementById('id_fornecedor').value = fornecedor.id;
        document.querySelector('[name="nome"]').value = fornecedor.nome;
        document.querySelector('[name="cnpj"]').value = fornecedor.cnpj;
        document.querySelector('[name="telefone"]').value = fornecedor.telefone;
        document.querySelector('[name="email"]').value = fornecedor.email;
        document.querySelector('[name="endereco"]').value = fornecedor.endereco;
        document.querySelector('h2').innerText = 'Editar Cadastro';
        window.scrollTo(0, 0); // Rola para o topo do formulário
    }
    </script>
</body>
</html>