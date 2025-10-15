<?php
session_start();
require_once '../db_connection.php'; 

// --------------------
// Função: baixa de estoque e registro de movimentação (MANTIDA)
// --------------------
function baixarEstoque($pdo, $item_id, $quantidade, $usuario_id, $motivo) {
    $quantidade = (int) $quantidade;
    if ($quantidade <= 0) {
        error_log("Tentativa de baixar quantidade inválida ({$quantidade}) para Item ID {$item_id}");
        return; 
    }
    
    $stmt = $pdo->prepare("SELECT quantidade FROM itens WHERE id = ?");
    $stmt->execute([$item_id]);
    $estoque = (int) $stmt->fetchColumn();

    if ($estoque < $quantidade) {
        error_log("Estoque insuficiente: Item ID {$item_id}, Qtd solicitada {$quantidade}, Estoque {$estoque}");
        return; 
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
// 1. Verificação de Acesso e Dados
// --------------------
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_setor'] !== 'TI') {
    die("Acesso negado.");
}

$chamado_id = $_POST['id'] ?? 0;
$novo_status = $_POST['status'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

if (!$chamado_id || !$novo_status) {
    die("Dados inválidos.");
}


// --------------------
// 2. Busca o status ATUAL e tipo do chamado (Não precisamos mais das colunas de solicitação na tabela chamados)
// --------------------
$stmt = $pdo->prepare("
    SELECT status, tipo 
    FROM chamados 
    WHERE id = ?
");
$stmt->execute([$chamado_id]);
$chamado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chamado) {
    die("Chamado não encontrado.");
}

$status_anterior = $chamado['status'];
$tipo_chamado = $chamado['tipo'];


// --------------------
// 3. Lógica PRINCIPAL: Baixa de Estoque ao FECHAR
// --------------------
if ($novo_status === 'Fechado' && $status_anterior !== 'Fechado') {
    
    // --- TONER: Busca na tabela de relacionamento toner_solicitacao ---
    if ($tipo_chamado === 'toner') {
        $stmt = $pdo->prepare("SELECT item_id, quantidade FROM toner_solicitacao WHERE chamado_id = ?");
        $stmt->execute([$chamado_id]);
        $solicitacao = $stmt->fetch();

        if ($solicitacao) {
            baixarEstoque(
                $pdo, 
                $solicitacao['item_id'], 
                $solicitacao['quantidade'], 
                $usuario_id, 
                "Fechamento de Chamado de Toner #{$chamado_id}"
            );
        }
    }
    
    // --- MATERIAL: Busca na nova tabela material_solicitacao ---
    elseif ($tipo_chamado === 'material') {
        
        // A busca agora é feita na tabela material_solicitacao
        $stmt = $pdo->prepare("SELECT item_id, quantidade FROM material_solicitacao WHERE chamado_id = ?");
        $stmt->execute([$chamado_id]);
        $solicitacao = $stmt->fetch(); 
        
        if ($solicitacao) {
            baixarEstoque(
                $pdo, 
                $solicitacao['item_id'], 
                $solicitacao['quantidade'], 
                $usuario_id, 
                "Fechamento de Chamado de Material #{$chamado_id}"
            );
        } else {
             // Aviso: O chamado de material foi fechado, mas não havia registro de solicitação.
             error_log("AVISO: Chamado de Material #{$chamado_id} fechado, mas nenhum registro em material_solicitacao.");
        }
    }
}


// --------------------
// 4. Atualiza o Status e data de fechamento
// --------------------
$stmt = $pdo->prepare("
    UPDATE chamados 
    SET status = ?, 
        dt_fechamento = (CASE WHEN ? = 'Fechado' THEN NOW() ELSE NULL END) 
    WHERE id = ?
");
$stmt->execute([$novo_status, $novo_status, $chamado_id]);


// --------------------
// 5. Redirecionamento
// --------------------
header("Location: detalhesChamado.php?id=$chamado_id");
exit;