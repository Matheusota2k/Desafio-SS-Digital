<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers necessários
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tratamento do preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->token)) {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Limpar tokens expirados
            $cleanup_query = "DELETE FROM user_tokens WHERE expiry < NOW()";
            $db->exec($cleanup_query);

            // Verificar token
            $query = "SELECT ut.*, u.email, u.is_active 
                     FROM user_tokens ut 
                     JOIN users u ON ut.user_id = u.id 
                     WHERE ut.token = :token 
                     AND ut.expiry > NOW()";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":token", $data->token);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar se conta está ativa
                if (!$row['is_active']) {
                    echo json_encode([
                        "status" => false,
                        "message" => "Conta não está ativa"
                    ]);
                    exit;
                }

                // Atualizar última atividade
                $update_query = "UPDATE user_tokens 
                               SET last_used = NOW() 
                               WHERE token = :token";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(":token", $data->token);
                $update_stmt->execute();

                // Iniciar sessão
                session_start();
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['last_activity'] = time();

                echo json_encode([
                    "status" => true,
                    "message" => "Token válido",
                    "user" => [
                        "id" => $row['user_id'],
                        "email" => $row['email']
                    ]
                ]);
            } else {
                echo json_encode([
                    "status" => false,
                    "message" => "Token inválido ou expirado"
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => false,
                "message" => "Erro: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Token não fornecido"
        ]);
    }
} else {
    echo json_encode([
        "status" => false,
        "message" => "Método não permitido"
    ]);
}
?>