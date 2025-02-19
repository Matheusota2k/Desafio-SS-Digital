<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"));
        
        // Se houver token, remove do banco
        if (!empty($data->token)) {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "DELETE FROM user_tokens WHERE token = :token";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":token", $data->token);
            $stmt->execute();
        }

        // Destruir sessão
        session_unset();
        session_destroy();
        
        echo json_encode([
            "status" => true,
            "message" => "Logout realizado com sucesso"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "Erro ao realizar logout: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => false,
        "message" => "Método não permitido"
    ]);
}
?>