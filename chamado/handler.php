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
// Função: baixa de estoque e registro de movimentação (MANTIDA, mas NÃO USADA AQUI)
// --------------------
function baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, $motivo) {
    // ... [Esta função permanece inalterada, pois só é chamada no updateStatus.php] ...
    $quantidade = (int) $quantidade;
    if ($quantidade <= 0) {
        return; 
    }
    
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque = (int) $stmt->fetchColumn();

    if ($estoque < $quantidade) {
        die("Erro: quantidade solicitada ($quantidade) maior que o estoque disponível ($estoque).");
    }

    $stmt = $pdo->prepare("UPDATE itens SET quantidade = quantidade - ? WHERE id = ?");
    $stmt->execute([$quantidade, $item_id]);

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
// Tipo do chamado e validação
// --------------------
$tipo = $_POST['tipo'] ?? null;
if (!$tipo || !in_array($tipo, ['toner', 'material', 'geral'])) {
    die("Erro: tipo de chamado inválido.");
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

    // Busca o item_id vinculado, se necessário
    if (!$item_id) {
        $stmt = $pdo->prepare("SELECT item_id FROM impressora_tonner WHERE equipamento_id = ?");
        $stmt->execute([$equipamento_id]);
        $item_id = $stmt->fetchColumn();

        if (!$item_id) {
            die("Erro: nenhum toner vinculado encontrado para esta impressora.");
        }
    }
    
    // <-- NOVO: VERIFICAÇÃO DE ESTOQUE PARA TONER -->
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque_disponivel = (int) $stmt->fetchColumn();

    if ($estoque_disponivel < $quantidade) {
        die("ERRO DE ESTOQUE: A quantidade solicitada ({$quantidade}) é maior que o estoque disponível ({$estoque_disponivel}).");
    }
    // <-- FIM DA VERIFICAÇÃO -->

    // Busca nomes
    $stmt = $pdo->prepare("SELECT descricao FROM equipamentos WHERE id = ?");
    $stmt->execute([$equipamento_id]);
    $equipamento_nome = $stmt->fetchColumn() ?: 'Desconhecido';

    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn() ?: 'Toner não encontrado';

    $titulo = "Solicitação de Toner: $item_nome";
    $descricao = "Impressora: $equipamento_nome\nToner: $item_nome\nQuantidade: $quantidade";
    
    // Inicia transação para garantir que ambas as tabelas sejam salvas
    $pdo->beginTransaction(); 

    try {
        // 1. Insere o chamado
        $stmt = $pdo->prepare("
            INSERT INTO chamados (tipo, titulo, descricao, imagem_path, autor_id, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$tipo, $titulo, $descricao, $imagem_path, $usuario_id, $status_inicial]);
        $chamado_id = $pdo->lastInsertId();

        // 2. Relaciona toner e impressora (toner_solicitacao)
        $stmt = $pdo->prepare("
            INSERT INTO toner_solicitacao (chamado_id, equipamento_id, item_id, quantidade)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$chamado_id, $equipamento_id, $item_id, $quantidade]);
        
        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao salvar chamado de toner: " . $e->getMessage());
    }
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
    
    // <-- NOVO: VERIFICAÇÃO DE ESTOQUE PARA MATERIAL -->
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque_disponivel = (int) $stmt->fetchColumn();

    if ($estoque_disponivel < $quantidade) {
        die("ERRO DE ESTOQUE: A quantidade solicitada ({$quantidade}) é maior que o estoque disponível ({$estoque_disponivel}).");
    }
    // <-- FIM DA VERIFICAÇÃO -->

    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn() ?: 'Desconhecido';

    $titulo = "Solicitação de Material: $item_nome";
    $descricao = "Item: $item_nome\nQuantidade: $quantidade";
    
    // Inicia transação para garantir que ambas as tabelas sejam salvas
    $pdo->beginTransaction(); 

    try {
        // 1. Insere o chamado
        $stmt = $pdo->prepare("
            INSERT INTO chamados (tipo, titulo, descricao, imagem_path, autor_id, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$tipo, $titulo, $descricao, $imagem_path, $usuario_id, $status_inicial]);
        $chamado_id = $pdo->lastInsertId();

        // 2. Insere os detalhes do material na nova tabela (material_solicitacao)
        $stmt = $pdo->prepare("
            INSERT INTO material_solicitacao (chamado_id, item_id, quantidade)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$chamado_id, $item_id, $quantidade]);
        
        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao salvar chamado de material: " . $e->getMessage());
    }
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
// Redireciona com sucesso
// --------------------
header("Location: listar.php?success=1");
exit;