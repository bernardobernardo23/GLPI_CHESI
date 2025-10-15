<?php
session_start();
require_once 'db_connection.php';

$mensagem = '';
$setor = ''; 



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_simples = $_POST['senha'] ?? '';
    $setor = trim($_POST['setor'] ?? '');

    // Validação de campos
    if (empty($nome) || empty($email) || empty($senha_simples) || empty($setor)) {
        $mensagem = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido.";
    
    } else {
        // Gera o hash da senha
        $senha_hash = password_hash($senha_simples, PASSWORD_DEFAULT);

        try {
            // Verifica se o e-mail já existe
            $check_sql = "SELECT id FROM usuarios WHERE email = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$email]);

            if ($check_stmt->rowCount() > 0) {
                $mensagem = "Erro: Este e-mail já está cadastrado.";
            } else {
                // Inserção Segura
                $sql = "INSERT INTO usuarios (nome, email, senha, setor) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $senha_hash, $setor]);

                $mensagem = "Cadastro realizado com sucesso! Você já pode fazer login.";

                // Redireciona para o login após 3 segundos
                header("Refresh: 3; url=index.php");
            }
        } catch (PDOException $e) {
            error_log("Erro de cadastro: " . $e->getMessage());
            $mensagem = "Ocorreu um erro no cadastro. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro - GLPI Simplificado</title>
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

    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl border border-gray-200">

        <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-8">
            Cadastrar Novo Usuário
        </h2>

        <?php if ($mensagem):
            // Define a cor da mensagem (verde para sucesso, vermelho para erro)
            $alert_class = (strpos($mensagem, 'sucesso') !== false)
                ? 'bg-green-100 border-green-400 text-green-700'
                : 'bg-red-100 border-red-400 text-red-700';
        ?>
            <div class="<?= $alert_class ?> px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $mensagem; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php" class="space-y-4">

            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Seu nome"
                    value="<?= htmlspecialchars($nome ?? '') ?>">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="seu.email@empresa.com"
                    value="<?= htmlspecialchars($email ?? '') ?>">
            </div>

            <div>
                <label for="setor" class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
                <select
                    id="setor"
                    name="setor"
                    required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="" disabled <?= empty($setor) ? 'selected' : '' ?>>Selecione seu setor</option>

                    <option value="Compras" <?= ($setor === 'Compras') ? 'selected' : '' ?>>Compras</option>
                    <option value="Comercial" <?= ($setor === 'Comercial') ? 'selected' : '' ?>>Comercial</option>
                    <option value="Financeiro" <?= ($setor === 'Financeiro') ? 'selected' : '' ?>>Financeiro</option>
                    <option value="Marketing" <?= ($setor === 'Marketing') ? 'selected' : '' ?>>Marketing</option>
                    <option value="Produção Aerosol" <?= ($setor === 'Produção Aerosol') ? 'selected' : '' ?>>Produção Aerosol</option>
                    <option value="Saneantes" <?= ($setor === 'Saneantes') ? 'selected' : '' ?>>Saneantes</option>
                    <option value="RH" <?= ($setor === 'RH') ? 'selected' : '' ?>>RH</option>
                    <option value="Contabilidade" <?= ($setor === 'Contabilidade') ? 'selected' : '' ?>>Contabilidade</option>
                    <option value="Formulação" <?= ($setor === 'Formulação') ? 'selected' : '' ?>>Formulação</option>
                    <option value="Logística" <?= ($setor === 'Logística') ? 'selected' : '' ?>>Logística</option>
                </select>
            </div>

            <div>
                <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="******">
            </div>

            <button
                type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-lg text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
                Cadastrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Já tem uma conta?
            <a href="index.php" class="font-medium text-blue-600 hover:text-blue-500 transition">
                Faça login aqui
            </a>
        </p>
    </div>
</body>

</html>