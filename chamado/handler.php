<?php
session_start();
require_once '../db_connection.php';

// --------------------
// Dados do usuário
// --------------------
$usuario_id = $_SESSION['usuario_id'];

// --------------------
// Função para baixar estoque e registrar movimentação
// --------------------
function baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, $motivo){
    // Verifica estoque disponível
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque = (int)$stmt->fetchColumn();

    if($estoque < $quantidade){
        die("Erro: quantidade solicitada ($quantidade) maior que o estoque disponível ($estoque).");
    }

    // Atualiza estoque
    $stmt = $pdo->prepare("UPDATE itens SET quantidade = quantidade - ? WHERE id = ?");
    $stmt->execute([$quantidade, $item_id]);

    // Registra movimentação
    $stmt = $pdo->prepare("
        INSERT INTO movimentacoes_estoque 
        (item_id, tipo, quantidade, usuario_id, motivo) 
        VALUES (?, 'baixa', ?, ?, ?)
    ");
    $stmt->execute([$item_id, $quantidade, $usuario_id, $motivo]);
}

// --------------------
// Dados do formulário
// --------------------
$tipo = $_POST['tipo'] ?? null;
$imagem_path = null;

// Upload de imagem (somente para chamados gerais)
if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0){
    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid().'.'.$ext;
    move_uploaded_file($_FILES['imagem']['tmp_name'], "../uploads/".$nomeArquivo);
    $imagem_path = "uploads/".$nomeArquivo;
}

// Inicializa título e descrição
$titulo = '';
$descricao = '';

// --------------------
// Lógica por tipo de chamado
// --------------------
if($tipo === 'toner'){
    $equipamento_id = $_POST['equipamento_id'] ?? null;
    $item_id = $_POST['item_id'] ?? null;
    $quantidade = (int)($_POST['quantidade'] ?? 1);

    if(!$equipamento_id || !$item_id){
        die("Erro: impressora ou toner não selecionado.");
    }

    // Buscar nomes para gerar título e descrição
    $stmt = $pdo->prepare("SELECT descricao FROM equipamentos WHERE id = ?");
    $stmt->execute([$equipamento_id]);
    $equipamento_nome = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn();

    $titulo = "Toner solicitado: $item_nome";
    $descricao = "Equipamento: $equipamento_nome\nItem: $item_nome\nQuantidade: $quantidade";

} elseif($tipo === 'material'){
    $item_id = $_POST['item_id'] ?? null;
    $quantidade = (int)($_POST['quantidade'] ?? 1);

    if(!$item_id){
        die("Erro: item de material não selecionado.");
    }

    // Buscar nome do item
    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn();

    $titulo = "Material solicitado: $item_nome";
    $descricao = "Item: $item_nome\nQuantidade: $quantidade";

} elseif($tipo === 'geral'){
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
} else {
    die("Erro: tipo de chamado inválido.");
}

// --------------------
// Inserir chamado
// --------------------
$stmt = $pdo->prepare("
    INSERT INTO chamados 
    (tipo, titulo, descricao, imagem_path, autor_id) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$tipo, $titulo, $descricao, $imagem_path, $usuario_id]);
$chamado_id = $pdo->lastInsertId();

// --------------------
// Registrar toner ou baixar estoque
// --------------------
if($tipo === 'toner'){
    // Registrar toner solicitado
    $stmt = $pdo->prepare("
        INSERT INTO toner_solicitacao 
        (chamado_id, equipamento_id, item_id, quantidade) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$chamado_id, $equipamento_id, $item_id, $quantidade]);

    // Baixa no estoque
    baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, "Solicitação de toner");

} elseif($tipo === 'material'){
    // Baixa no estoque
    baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, "Solicitação de material");
}

// --------------------
// Redirecionar
// --------------------
header("Location: index.php?success=1");
exit;
