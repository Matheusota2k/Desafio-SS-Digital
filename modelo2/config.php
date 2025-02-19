<?php
$endereco = 'localhost';
$banco = 'matheus';
$usuario = 'postgres';
$senha = 'Farias200414'; // Coloque sua senha do PostgreSQL aqui

try {
    $pdo = new PDO(
        "pgsql:host=$endereco;port=5432;dbname=$banco",
        $usuario,
        $senha,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // echo "Conectado ao banco de dados!";
} catch (PDOException $e) {
    echo 'Falha ao conectar com o banco de dados';
    die($e->getMessage());
}
?>