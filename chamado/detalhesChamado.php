<?php
session_start();
require_once '../db_connection.php';

// ------------------------
// Função para estilização do Status
// ------------------------
function getStatusClass($status) {
    switch ($status) {
        case 'Aberto':
            return 'bg-red-500 text-white';
        case 'Em andamento':
            return 'bg-yellow-500 text-gray-900';
        case 'Fechado':
            return 'bg-green-600 text-white';
        default:
            return 'bg-gray-400 text-white';
    }
}

// ------------------------
// Verifica login e acesso
// ------------------------
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$usuario_setor = $_SESSION['usuario_setor'] ?? '';
$usuario_admin = ($usuario_setor === 'TI');

$chamado_id = $_GET['id'] ?? 0;

// ------------------------
// Busca informações do chamado
// ------------------------
$stmt = $pdo->prepare("
    SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor, u.email AS autor_email
    FROM chamados c
    JOIN usuarios u ON c.autor_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$chamado_id]);
$chamado = $stmt->fetch();

if (!$chamado) {
    die("Chamado não encontrado.");
}

// ------------------------
// Busca atualizações
// ------------------------
$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS usuario_nome
    FROM chamado_atualizacoes a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.chamado_id = ?
    ORDER BY a.data ASC
");
$stmt->execute([$chamado_id]);
$atualizacoes = $stmt->fetchAll();

// Formatação da data
$dt_abertura_formatada = date('d/m/Y H:i', strtotime($chamado['dt_abertura']));
$dt_fechamento_formatada = $chamado['dt_fechamento'] ? date('d/m/Y H:i', strtotime($chamado['dt_fechamento'])) : 'Em aberto';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Detalhes do Chamado #<?= $chamado_id ?></title>
    <style>
        /* Estilo para o modal de zoom */
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        .modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex">

    <?php include '../areaLateral.php'; ?>

    <main class="flex-1 ml-64 p-8">
        <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-4 border-b border-gray-200">
                
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2 md:mb-0">
                    #<?= $chamado_id ?>: <?= htmlspecialchars($chamado['titulo']) ?>
                </h1>

                <?php if ($usuario_admin): ?>
                <div class="flex gap-3 mt-3 md:mt-0">
                    
                    <?php if ($chamado['status'] != 'Fechado'): ?>
                    <form action="updateStatus.php" method="post" class="flex gap-2 items-center bg-gray-100 p-2 rounded-lg border">
                        <input type="hidden" name="id" value="<?= $chamado_id ?>">
                        <select name="status" class="border p-2 rounded-md bg-white text-gray-700">
                            <option value="Aberto" <?= $chamado['status'] == 'Aberto' ? 'selected' : '' ?>>Aberto</option>
                            <option value="Em andamento" <?= $chamado['status'] == 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                            <option value="Fechado" <?= $chamado['status'] == 'Fechado' ? 'selected' : '' ?>>Fechado</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">Atualizar Status</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($chamado['status'] != 'Fechado'): ?>
                    <a href="adicionarAtualizacao.php?id=<?= $chamado_id ?>"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition self-center">
                        + Adicionar atualização
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10 p-6 bg-gray-50 rounded-lg border border-gray-200">
                
                <div>
                    <p class="text-sm text-gray-500 mb-1">Status Atual</p>
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-base font-bold <?= getStatusClass($chamado['status']) ?>">
                        <?= $chamado['status'] ?>
                    </span>
                    <p class="mt-4 text-gray-700">
                        <strong class="font-semibold text-gray-800">Tipo:</strong> <?= ucfirst($chamado['tipo']) ?>
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 mb-1">Informações do Autor</p>
                    <p class="text-gray-700">
                        <strong class="font-semibold text-gray-800">Autor:</strong> <?= htmlspecialchars($chamado['autor_nome']) ?>
                    </p>
                    <p class="text-gray-700">
                        <strong class="font-semibold text-gray-800">Setor:</strong> <?= htmlspecialchars($chamado['autor_setor']) ?>
                    </p>
                    <p class="text-gray-700">
                        <strong class="font-semibold text-gray-800">Email:</strong> <?= htmlspecialchars($chamado['autor_email']) ?>
                    </p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500 mb-1">Datas</p>
                    <p class="text-gray-700">
                        <strong class="font-semibold text-gray-800">Aberto em:</strong> <?= $dt_abertura_formatada ?>
                    </p>
                    <p class="text-gray-700">
                        <strong class="font-semibold text-gray-800">Fechamento:</strong> <?= $dt_fechamento_formatada ?>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                
                <div class="md:col-span-2 bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-xl font-bold mb-3 text-gray-800">Descrição do Problema</h2>
                    <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($chamado['descricao']) ?></p>
                </div>
                
                <?php if ($chamado['imagem_path']): ?>
                    <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h2 class="text-lg font-semibold mb-2 text-gray-800">Anexo do Chamado</h2>
                        <img src="../<?= $chamado['imagem_path'] ?>"
                            alt="Imagem do chamado"
                            class="max-h-56 w-auto rounded-lg shadow-md border cursor-pointer hover:opacity-90 transition"
                            onclick="openModal('../<?= $chamado['imagem_path'] ?>')">
                    </div>
                <?php endif; ?>
            </div>

            <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Histórico de Atualizações</h2>
            <?php if (count($atualizacoes) > 0): ?>
                <div class="space-y-6">
                    <?php foreach ($atualizacoes as $a): ?>
                        <div class="border border-gray-300 p-5 rounded-lg bg-white shadow-sm">
                            <div class="flex justify-between items-center mb-2 border-b pb-2">
                                <p class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($a['usuario_nome']) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    em <?= date('d/m/Y H:i', strtotime($a['data'])) ?>
                                </p>
                            </div>
                            
                            <p class="text-gray-700 whitespace-pre-line mb-3"><?= htmlspecialchars($a['descricao']) ?></p>
                            
                            <?php if ($a['imagem_path']): ?>
                                <div class="mt-4">
                                    <img src="<?= $a['imagem_path'] ?>"
                                        alt="Imagem da atualização"
                                        class="max-h-48 rounded-lg border cursor-pointer hover:opacity-90 shadow-md"
                                        onclick="openModal('<?= $a['imagem_path'] ?>')">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 bg-gray-50 p-4 rounded-lg border">Nenhuma atualização registrada ainda.</p>
            <?php endif; ?>

        </div>
    </main>

    <div id="imageModal" class="modal" onclick="closeModal()">
        <img id="modalImage" src="">
    </div>

    <script>
        // Função JS para abrir o modal de imagem
        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modalImg.src = src;
            modal.style.display = 'flex';
        }

        // Função JS para fechar o modal
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
    </script>

</body>

</html>