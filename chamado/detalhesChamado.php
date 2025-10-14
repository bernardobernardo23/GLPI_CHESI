<?php
session_start();
require_once '../db_connection.php';

// Verifica admin pelo setor
$usuario_setor = $_SESSION['usuario_setor'] ?? '';
if ($usuario_setor !== 'TI') {
    die("Acesso negado");
}

$chamado_id = $_GET['id'] ?? 0;

// Buscar dados do chamado
$stmt = $pdo->prepare("
    SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor, u.email AS autor_email
    FROM chamados c
    JOIN usuarios u ON c.autor_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$chamado_id]);
$chamado = $stmt->fetch();
if (!$chamado) {
    die("Chamado não encontrado");
}

// Buscar atualizações
$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS usuario_nome
    FROM chamado_atualizacoes a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.chamado_id = ?
    ORDER BY a.data ASC
");
$stmt->execute([$chamado_id]);
$atualizacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Detalhes do Chamado</title>
</head>

<body class="bg-gray-100 p-8">

    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($chamado['titulo']) ?></h1>

        <p class="mb-2"><strong>Tipo:</strong> <?= ucfirst($chamado['tipo']) ?></p>
        <p class="mb-2"><strong>Status:</strong> <?= $chamado['status'] ?></p>
        <p class="mb-2"><strong>Autor:</strong> <?= htmlspecialchars($chamado['autor_nome']) ?> (<?= htmlspecialchars($chamado['autor_setor']) ?>)</p>
        <p class="mb-2"><strong>Abertura:</strong> <?= $chamado['dt_abertura'] ?></p>

        <?php if ($chamado['imagem_path']): ?>
            <p class="mb-4"><img src="<?= $chamado['imagem_path'] ?>" alt="Imagem do chamado" class="max-w-md"></p>
        <?php endif; ?>

        <p class="mb-6"><?= nl2br(htmlspecialchars($chamado['descricao'])) ?></p>

        <h2 class="text-xl font-semibold mb-2">Atualizações</h2>
        <?php if (count($atualizacoes) > 0): ?>
            <?php foreach ($atualizacoes as $a): ?>
                <div class="border p-3 mb-3 rounded bg-gray-50">
                    <p><strong><?= htmlspecialchars($a['usuario_nome']) ?></strong> em <?= $a['data'] ?></p>
                    <p><?= nl2br(htmlspecialchars($a['descricao'])) ?></p>
                    <?php if ($a['imagem_path']): ?>
                        <p><img src="<?= $a['imagem_path'] ?>" class="max-w-sm mt-2"></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhuma atualização ainda.</p>
        <?php endif; ?>
    </div>

</body>

</html>