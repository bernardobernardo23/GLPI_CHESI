<?php

session_start();
require_once 'db_connection.php';

$mensagem = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha_simples = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha_simples)) {
        $mensagem = "Por favor, preencha o e-mail e a senha.";
    } else {
        try {
            // buscar o hash armazenado pelo email
            $sql = "SELECT id, nome, senha, setor FROM usuarios WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]); //se relaciona com a ? do sql
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // password_verify aplica o hash na senha simples e compara com o hash do banco
            if ($usuario && password_verify($senha_simples, $usuario['senha'])) {
                
                // senha válida: Inicia a sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_setor'] = $usuario['setor'];
                
                // redireciona
                header('Location: painel.php');
                exit;

            } else {
                // usuario não encontrado ou senha inválida
                $mensagem = "E-mail ou senha inválidos.";
            }

        } catch (PDOException $e) {
            // Em ambiente de produção, logue o erro real e mostre uma mensagem genérica.
            error_log("Erro de login: " . $e->getMessage()); 
            $mensagem = "Erro ao tentar realizar login. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - GLPI Simplificado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Centraliza o conteúdo na tela */
        .min-h-screen-center {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen-center">
    
    <div class="w-full max-w-sm bg-white p-8 rounded-xl shadow-2xl border border-gray-200">
        
        <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-6">
            Acesso ao Sistema
        </h2>
        
        <?php if ($mensagem): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $mensagem; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php" class="space-y-6">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="seu.email@empresa.com"
                >
            </div>

            <div>
                <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="******"
                >
            </div>

            <button 
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150"
            >
                Entrar
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Não tem uma conta? 
            <a href="cadastro.php" class="font-medium text-blue-600 hover:text-blue-500 transition">
                Cadastre-se aqui
            </a>
        </p>
    </div>
</body>
</html>