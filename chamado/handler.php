<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado. Faça login novamente.");
}

$usuario_id = $_SESSION['usuario_id'];

// --------------------
// Função: baixa de estoque e registro de movimentação (mantida, pois está correta)
// --------------------
function baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, $motivo)
{
    $quantidade = (int)$quantidade;
    if ($quantidade <= 0) return;

    // Verifica o estoque
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque = (int)$stmt->fetchColumn();

    if ($estoque < $quantidade) {
        die("Erro: quantidade solicitada ($quantidade) maior que o estoque disponível ($estoque).");
    }

    // Atualiza estoque
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
        $imagem_path = "uploads/" . $nomeArquivo;
    } else {
        die("Erro ao mover arquivo de imagem.");
    }
}

// --------------------
// Tipo do chamado
// --------------------
$tipo = $_POST['tipo'] ?? null;
if (!$tipo || !in_array($tipo, ['toner', 'material', 'geral'])) {
    die("Erro: tipo de chamado inválido.");
}

$titulo = '';
$descricao = '';
$status_inicial = 'Aberto';

// ==================================================================
// CHAMADO DE TONER
// ==================================================================
if ($tipo === 'toner') {
    $equipamento_id = isset($_POST['equipamento_id']) ? (int)$_POST['equipamento_id'] : 0;
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    
    // MUDANÇA: Busca a quantidade do campo 'quantidade_toner'
    $quantidade = isset($_POST['quantidade_toner']) ? (int)$_POST['quantidade_toner'] : 1; 

    // Validação
    if ($equipamento_id <= 0) {
        die("Erro: impressora não selecionada.");
    }
    if ($item_id <= 0) {
        die("Erro: toner não selecionado.");
    }

    // Busca cor do toner vinculado
    $stmt = $pdo->prepare("
    SELECT cor FROM impressora_tonner
    WHERE equipamento_id = ? AND item_id = ? AND ativo = 1
");
    $stmt->execute([$equipamento_id, $item_id]);
    $cor_toner = $stmt->fetchColumn() ?: null;

    // Verifica estoque (mantido)
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque_disponivel = (int)$stmt->fetchColumn();

    if ($estoque_disponivel < $quantidade) {
        die("Erro: quantidade solicitada ({$quantidade}) é maior que o estoque disponível ({$estoque_disponivel}).");
    }

    // Busca nomes (mantido)
    $stmt = $pdo->prepare("SELECT descricao FROM equipamentos WHERE id = ?");
    $stmt->execute([$equipamento_id]);
    $equipamento_nome = $stmt->fetchColumn() ?: 'Desconhecido';

    $stmt = $pdo->prepare("SELECT nome FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_nome = $stmt->fetchColumn() ?: 'Toner não encontrado';

    // Monta título e descrição (mantido)
    $titulo = "Solicitação de Toner: $item_nome";
    $descricao = "Impressora: $equipamento_nome\nToner: $item_nome\nCor: $cor_toner\nQuantidade: $quantidade";

    $status_inicial = 'Aberto';

    try {
        $pdo->beginTransaction();

        // Insere chamado principal
        $stmt = $pdo->prepare("
        INSERT INTO chamados (tipo, titulo, descricao, autor_id, status)
        VALUES (?, ?, ?, ?, ?)
    ");
        $stmt->execute(['toner', $titulo, $descricao, $usuario_id, $status_inicial]);
        $chamado_id = $pdo->lastInsertId();

        // Insere vínculo com toner
        $stmt = $pdo->prepare("
        INSERT INTO toner_solicitacao (chamado_id, equipamento_id, item_id, cor_toner, quantidade, status)
        VALUES (?, ?, ?, ?, ?, 'Pendente')
    ");
        $stmt->execute([$chamado_id, $equipamento_id, $item_id, $cor_toner, $quantidade]);

        $pdo->commit();

        // Redireciona com sucesso
        header("Location: listarChamado.php?success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao salvar chamado de toner: " . $e->getMessage());
    }
}

// ==================================================================
// CHAMADO DE MATERIAL
// ==================================================================
elseif ($tipo === 'material') {
    // MUDANÇA: Busca o ID do campo 'material_item_id'
    $item_id = isset($_POST['material_item_id']) ? (int)$_POST['material_item_id'] : 0;
    
    // MUDANÇA: Busca a quantidade do campo 'quantidade_material'
    $quantidade = isset($_POST['quantidade_material']) ? (int)$_POST['quantidade_material'] : 1;
    
    // Valida se o item foi selecionado
    if ($item_id <= 0) {
        header("Location: novo.php?error=material_nao_selecionado");
        exit;
    }

    // Verifica estoque (mantido)
    $stmt = $pdo->prepare("SELECT nome, quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        die("Erro: item não encontrado no banco de dados.");
    }

    $estoque_disponivel = (int)$item['quantidade'];
    $item_nome = $item['nome'];

    if ($estoque_disponivel < $quantidade) {
        die("Erro: quantidade solicitada ({$quantidade}) é maior que o estoque disponível ({$estoque_disponivel}).");
    }

    // Define título e descrição (mantido)
    $titulo = "Solicitação de Material: {$item_nome}";
    $descricao = "Item: {$item_nome}\nQuantidade: {$quantidade}";

    try {
        $pdo->beginTransaction();

        // Insere o chamado principal
        $stmt = $pdo->prepare("
            INSERT INTO chamados (tipo, titulo, descricao, imagem_path, autor_id, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'material',
            $titulo,
            $descricao,
            $imagem_path,
            $usuario_id,
            $status_inicial
        ]);
        $chamado_id = $pdo->lastInsertId();

        // Insere a solicitação específica
        $stmt = $pdo->prepare("
            INSERT INTO material_solicitacao (chamado_id, item_id, quantidade)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$chamado_id, $item_id, $quantidade]);

        $pdo->commit();

        // Redireciona
        header("Location: listarChamado.php?success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao salvar chamado de material: " . $e->getMessage());
    }
}

// --------------------
// CHAMADO GERAL (mantido)
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
header("Location: listarChamado.php?success=1");
exit;