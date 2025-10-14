<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'chesi_glpi'); 
define('DB_USER', 'root');             
define('DB_PASS', '');                 

try {
    // cria a conexão PDO
    //PDO ==> objeto de dados php -> ponte entre banco e codigo 
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    
    //exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erro na Conexão com o Banco de Dados: " . $e->getMessage());
}
?> 