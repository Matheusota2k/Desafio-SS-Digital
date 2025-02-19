<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Headers necessários
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tratamento do preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (isset($_SESSION['user_id'])) {
        // Verificar se o usuário ainda existe no banco
        $query = "SELECT id, email FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode([
                "status" => true,
                "message" => "Sessão válida",
                "user" => [
                    "id" => $user['id'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            // Usuário não existe mais, destruir sessão
            session_destroy();
            echo json_encode([
                "status" => false,
                "message" => "Usuário não encontrado"
            ]);
        }
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Sessão inválida"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Erro: " . $e->getMessage()
    ]);
}
?>