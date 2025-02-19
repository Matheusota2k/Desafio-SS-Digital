<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'utils/security.php';

// Inicializar variáveis
$erro = '';
$sucesso = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos obrigatórios
        if (empty($_POST['email']) || empty($_POST['senha'])) {
            throw new Exception('Por favor, preencha todos os campos.');
        }

        // Sanitizar e validar email
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido.');
        }

        // Obter senha
        $senha = $_POST['senha'];

        // Conectar ao banco de dados
        $database = new Database();
        $pdo = $database->getConnection();

        // Buscar usuário
        $stmt = $pdo->prepare("
            SELECT id, email, password, is_active 
            FROM users 
            WHERE email = :email
        ");
        
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se usuário existe e senha está correta
        if (!$usuario || !password_verify($senha, $usuario['password'])) {
            // Registrar tentativa de login falha
            Security::registerLoginAttempt($email, false);
            throw new Exception('Email ou senha incorretos.');
        }

        // Verificar se conta está ativa
        if (!$usuario['is_active']) {
            throw new Exception('Conta inativa. Por favor, verifique seu email.');
        }

        // Verificar bloqueio por tentativas falhas
        if (Security::isAccountLocked($email)) {
            throw new Exception('Conta temporariamente bloqueada. Tente novamente mais tarde.');
        }

        // Login bem-sucedido
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['last_activity'] = time();

        // Se "lembrar-me" estiver marcado
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $stmt = $pdo->prepare("
                INSERT INTO user_tokens (user_id, token, expiry) 
                VALUES (:user_id, :token, :expiry)
            ");
            
            $stmt->execute([
                'user_id' => $usuario['id'],
                'token' => $token,
                'expiry' => $expiry
            ]);

            // Configurar cookie seguro
            setcookie(
                'remember_token',
                $token,
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }

        // Registrar login bem-sucedido
        Security::registerLoginAttempt($email, true);

        // Redirecionar para dashboard
        header('Location: dashboard.php');
        exit();

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .login-card {
            background: rgba(33, 37, 41, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-login {
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-dark text-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="login-card p-4 w-100" style="max-width: 450px;">
            <!-- Título -->
            <h2 class="text-center mb-4">
                <i class="fas fa-lock me-2"></i>Login
            </h2>

            <!-- Mensagens de erro -->
            <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($erro); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulário de Login -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <input type="email" 
                           class="form-control bg-dark text-light" 
                           id="email" 
                           name="email" 
                           required
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="invalid-feedback">
                        Por favor, insira um email válido.
                    </div>
                </div>

                <!-- Senha -->
                <div class="mb-3">
                    <label for="senha" class="form-label">
                        <i class="fas fa-key me-2"></i>Senha
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control bg-dark text-light" 
                               id="senha" 
                               name="senha" 
                               required
                               minlength="6">
                        <button class="btn btn-outline-secondary" 
                                type="button" 
                                id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        A senha deve ter no mínimo 6 caracteres.
                    </div>
                </div>

                <!-- Lembrar-me -->
                <div class="mb-3 form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="remember" 
                           name="remember">
                    <label class="form-check-label" for="remember">
                        Manter conectado
                    </label>
                </div>

                <!-- Botões -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Entrar
                    </button>
                    <a href="register.php" class="btn btn-outline-light">
                        <i class="fas fa-user-plus me-2"></i>Criar conta
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    // Validação do formulário
    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const senha = document.getElementById('senha');
        const type = senha.getAttribute('type') === 'password' ? 'text' : 'password';
        senha.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    </script>
</body>
</html>