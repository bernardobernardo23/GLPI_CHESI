<?php
// ===============================================
// login.php
// Lógica e Formulário de Autenticação
// ===============================================
session_start();
require_once 'db_connection.php';

$mensagem = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    //trim remove espaços em branco do inicio e fim
    $senha_simples = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha_simples)) {
        $mensagem = "Por favor, preencha o e-mail e a senha.";
    } else {
        try {
            // buscar o hash armazenado pelo email
            $sql = "SELECT id, nome, senha FROM usuarios WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]); //se relaciona com a ? do sql
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // password_verify aplica o hash na senha simples e compara com o hash do banco
            if ($usuario && password_verify($senha_simples, $usuario['senha'])) {
                
                // senha válida: Inicia a sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                
                // redireciona
                header('Location: painel.php');
                exit;

            } else {
                // usuario não encontrado ou senha inválida
                $mensagem = "E-mail ou senha inválidos.";
            }

        } catch (PDOException $e) {
            $mensagem = "Erro ao tentar realizar login. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Login - Projeto Login Hash</title>
</head>
<body>
    <h2>Login de Usuário</h2>
    <?php if ($mensagem): ?>
        <p style="color: red; font-weight: bold;"><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>

        <button type="submit">Entrar</button>
    </form>
    <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a>.</p>
</body>
</html>