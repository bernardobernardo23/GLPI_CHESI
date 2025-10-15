<?php
session_start();
require_once '../db_connection.php';

// --------------------
// Verifica se o usuário está logado
// --------------------
if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado. Faça login novamente.");
}

$usuario_id = $_SESSION['usuario_id'];

// --------------------
// Função: baixa de estoque e registro de movimentação
// --------------------
function baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, $motivo) {
    // Verifica se há quantidade suficiente
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque = (int) $stmt->fetchColumn();

    if ($estoque < $quantidade) {
        die("Erro: quantidade solicitada ($quantidade) maior que o estoque disponível ($estoque).");
    }

    // Atualiza a tabela de itens
    $stmt = $pdo->prepare("UPDATE itens SET quantidade = quantidade - ? WHERE id = ?");
    $stmt->execute([$quantidade, $item_id]);

    // Registra movimentação
    $stmt = $pdo->prepare("
        INSERT INTO movimentacoes_estoque (item_id, tipo, quantidade, usuario_id, motivo)
        VALUES (?, 'baixa', ?, ?, ?)
    ");
    $stmt->execute([$item_id, $quantidade, $usuario_id, $motivo]);
}

// --------------------
// Upload da imagem (somente chamados gerais)
// --------------------
$imagem_path = null;
$uploadsDir = "../uploads/";

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    $nomeArquivo = uniqid("chamado_") . "." . $ext;
    $destino = $uploadsDir . $nomeArquivo;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
        $imagem_path = "uploads/" . $nomeArquivo; // Caminho relativo para BD
    } else {
        die("Erro ao mover arquivo de imagem.");
    }
}

// --------------------
// Tipo do chamado
// --------------------
$tipo = $_POST['tipo'] ?? null;
if (!$tipo) {
    die("Erro: tipo de chamado não informado.");
}

$titulo = '';
$descricao = '';
$status_inicial = 'Aberto';

// --------------------
// CHAMADO DE TONER
// --------------------
if ($tipo === 'toner') {
    $equipamento_id = $_POST['equipamento_id'] ?? null;
    $item_id = $_POST['item_id'] ?? null;
    $quantidade = (int) ($_POST['quantidade'] ?? 1);

    if (!$equipamento_id) {
        die("Erro: impressora não selecionada.");
    }

    // Caso o toner não venha via formulário, busca o vinculado automaticamente
    if (!$item_id) {
        $stmt = $pdo->prepare("SELECT modeloTonnerId FROM impressora_tonner WHERE impressoraId = ?");
        $stmt->execute([$equipamento_id]);
        $item_id = $stmt->fetchColumn();

        if (!$item_id) {
            die("Erro: nenhum toner vinculado encontrado para esta impressora.");
        }
    }

    // Busca nomes
    $stmt = $pdo->prepare("SELECT descricaoEquipamento FROM equipamentos WHERE idEquipamento = ?");
    $stmt->execute([$equipamento_id]);
    $equipamento_nome = $stmt->fetchColumn() ?: 'Desconhecido';

    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn() ?: 'Toner não encontrado';

    $titulo = "Solicitação de Toner: $item_nome";
    $descricao = "Impressora: $equipamento_nome\nToner: $item_nome\nQuantidade: $quantidade";

    // Insere o chamado
    $stmt = $pdo->prepare("
        INSERT INTO chamados (tipo, titulo, descricao, imagem_path, autor_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$tipo, $titulo, $descricao, $imagem_path, $usuario_id, $status_inicial]);
    $chamado_id = $pdo->lastInsertId();

    // Relaciona toner e impressora
    $stmt = $pdo->prepare("
        INSERT INTO toner_solicitacao (chamado_id, equipamento_id, item_id, quantidade)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$chamado_id, $equipamento_id, $item_id, $quantidade]);

    // Faz a baixa de estoque
    baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, "Solicitação de toner");

}
// --------------------
// CHAMADO DE MATERIAL
// --------------------
elseif ($tipo === 'material') {
    $item_id = $_POST['item_id'] ?? null;
    $quantidade = (int) ($_POST['quantidade'] ?? 1);

    if (!$item_id) {
        die("Erro: item de material não selecionado.");
    }

    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn() ?: 'Desconhecido';

    $titulo = "Solicitação de Material: $item_nome";
    $descricao = "Item: $item_nome\nQuantidade: $quantidade";

    // Insere o chamado
    $stmt = $pdo->prepare("
        INSERT INTO chamados (tipo, titulo, descricao, imagem_path, autor_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$tipo, $titulo, $descricao, $imagem_path, $usuario_id, $status_inicial]);

    // Faz baixa de estoque
    baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, "Solicitação de material");

}
// --------------------
// CHAMADO GERAL
// --------------------
elseif ($tipo === 'geral') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!$titulo || !$descricao) {
        die("Erro: título e descrição são obrigatórios para chamados gerais.");
    }

    // Insere o chamado
    $stmt = $pdo->prepare("
        INSERT INTO chamados (tipo, titulo, descricao, imagem_path, autor_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$tipo, $titulo, $descricao, $imagem_path, $usuario_id, $status_inicial]);

}
// --------------------
// ERRO CASO TIPO INVÁLIDO
// --------------------
else {
    die("Erro: tipo de chamado inválido.");
}

// --------------------
// Redireciona com sucesso
// --------------------
header("Location: listar.php?success=1");
exit;
