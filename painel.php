<?php

session_start();

// verifica se a sessão do usuário existe ou manda pro login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$nome_usuario = $_SESSION['usuario_nome'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Painel</title>
</head>
<body>
    <h2>bem vindo <?php echo htmlspecialchars($nome_usuario); ?>!</h2>
    <p>Seu login com senha hash está funcionando corretamente</p>
    
    <p><a href="logout.php">Sair (Logout)</a></p>
</body>
</html>