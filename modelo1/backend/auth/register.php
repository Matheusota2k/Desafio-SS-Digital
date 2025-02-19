<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

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
    
    if (!empty($data->email) && !empty($data->password)) {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Limpar e validar email
            $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email inválido");
            }

            // Verificar se a conta está bloqueada
            if ($database->isAccountLocked($email)) {
                throw new Exception("Conta temporariamente bloqueada. Tente novamente mais tarde.");
            }

            // Buscar usuário
            $query = "SELECT id, email, password, is_active FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar se conta está ativa
                if (!$row['is_active']) {
                    $database->recordLoginAttempt($email, false);
                    throw new Exception("Conta não ativada. Por favor, verifique seu email.");
                }

                // Verificar senha
                if (password_verify($data->password, $row['password'])) {
                    // Registrar tentativa bem-sucedida
                    $database->recordLoginAttempt($email, true);

                    // Limpar tokens antigos
                    $cleanup_query = "DELETE FROM user_tokens WHERE user_id = :user_id OR expiry < NOW()";
                    $cleanup_stmt = $db->prepare($cleanup_query);
                    $cleanup_stmt->bindParam(":user_id", $row['id']);
                    $cleanup_stmt->execute();

                    // Gerar novo token se "lembrar-me" estiver ativo
                    $token = null;
                    if (isset($data->remember) && $data->remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        $token_query = "INSERT INTO user_tokens (user_id, token, expiry) 
                                      VALUES (:user_id, :token, :expiry)";
                        $token_stmt = $db->prepare($token_query);
                        $token_stmt->bindParam(":user_id", $row['id']);
                        $token_stmt->bindParam(":token", $token);
                        $token_stmt->bindParam(":expiry", $expiry);
                        $token_stmt->execute();
                    }

                    // Criar sessão
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['last_activity'] = time();

                    echo json_encode([
                        "status" => true,
                        "message" => "Login realizado com sucesso",
                        "user" => [
                            "id" => $row['id'],
                            "email" => $row['email']
                        ],
                        "token" => $token
                    ]);
                } else {
                    // Registrar tentativa falha
                    $database->recordLoginAttempt($email, false);
                    throw new Exception("Senha incorreta");
                }
            } else {
                // Registrar tentativa falha
                $database->recordLoginAttempt($email, false);
                throw new Exception("Usuário não encontrado");
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Email e senha são obrigatórios"
        ]);
    }
} else {
    echo json_encode([
        "status" => false,
        "message" => "Método não permitido"
    ]);
}
?>