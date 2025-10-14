<?php
session_start();
require_once '../db_connection.php';

// Verifica se usuário é admin pelo setor
$usuario_id = $_SESSION['usuario_id'];
$usuario_setor = $_SESSION['usuario_setor'] ?? '';
if($usuario_setor !== 'TI'){
    die("Acesso negado");
}

// Dados do formulário
$chamado_id = $_POST['chamado_id'] ?? null;
$descricao = $_POST['descricao'] ?? '';
$imagem_path = null;

if(!$chamado_id || !$descricao){
    die("Campos obrigatórios faltando");
}

// Upload da imagem (opcional)
if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0){
    // Certifica-se que a pasta uploads existe
    $uploadDir = __DIR__ . '/../uploads/';
    if(!is_dir($uploadDir)){
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid().'.'.$ext;
    $destino = $uploadDir . $nomeArquivo;

    if(move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)){
        $imagem_path = '../uploads/' . $nomeArquivo; // Caminho relativo para exibir no HTML
    } else {
        die("Erro ao salvar a imagem");
    }
}

// Inserir atualização no banco
$stmt = $pdo->prepare("
    INSERT INTO chamado_atualizacoes 
    (chamado_id, descricao, imagem_path, usuario_id) 
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$chamado_id, $descricao, $imagem_path, $usuario_id]);

header("Location: detalhesChamado.php?id=$chamado_id&success=1");
exit;
