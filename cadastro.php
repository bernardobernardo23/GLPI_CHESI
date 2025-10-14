<?php
session_start();
require_once 'db_connection.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_simples = $_POST['senha'] ?? '';
    $setor = trim($_POST['setor'] ?? ''); 
    //todos os campos do form
    if (empty($nome) || empty($email) || empty($senha_simples) || empty($setor)) {
        $mensagem = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //filter_var valida o formato do email
        $mensagem = "Formato de e-mail inválido.";
    } else {
        // PASSWORD_DEFAULT usa o algoritmo Bcrypt e gera um salt automático.
        $senha_hash = password_hash($senha_simples, PASSWORD_DEFAULT);

        try {
            // inserção Segura (PDO Prepared Statement) ---
            $sql = "INSERT INTO usuarios (nome, email, senha, setor) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $senha_hash, $setor]); //respectivamente à ?

            $mensagem = "Cadastro realizado com sucesso! Você já pode fazer login.";
            // redireciona para o login após 3 segundos
            header("Refresh: 3; url=index.php"); 
            
        } catch (PDOException $e) {
            // verifica se é um erro de duplicidade de e-mail
            if ($e->getCode() == '23000') { 
                //23000 é o código SQLSTATE para violação de chave única
                $mensagem = "Erro: Este e-mail já está cadastrado.";
            } else {
                $mensagem = "Ocorreu um erro no cadastro. Tente novamente: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Cadastro - Projeto Login Hash</title>
</head>
<body>
    <h2>Cadastro de Novo Usuário</h2>
    <?php if ($mensagem): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form method="POST" action="cadastro.php">
        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="setor">Setor:</label><br>
        <input type="text" id="setor" name="setor" required><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>

        <button type="submit">Cadastrar</button>
    </form>
    <p>Já tem uma conta? <a href="index.php">Faça login aqui</a>.</p>
</body>
</html>