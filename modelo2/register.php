<?php
// Iniciar sessão para gerenciamento de estado
session_start();

// Incluir configurações e conexão com banco de dados
require_once 'config/database.php';
require_once 'utils/security.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obter conexão com o banco
        $database = new Database();
        $pdo = $database->getConnection();

        // Sanitizar e validar email
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Validar senha
        $senha = $_POST['senha'];
        if (strlen($senha) < 6) {
            throw new Exception('A senha deve ter no mínimo 6 caracteres');
        }

        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            throw new Exception('Este email já está registrado');
        }

        // Criptografar senha usando algoritmo seguro
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

        // Preparar e executar inserção
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password, created_at) 
            VALUES (:email, :senha, CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([
            'email' => $email,
            'senha' => $senha_hash
        ]);

        // Definir mensagem de sucesso
        $_SESSION['success_message'] = 'Cadastro realizado com sucesso!';
        
        // Redirecionar para login
        header('Location: login.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="bg-black text-bg-primary">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card bg-secondary text-white w-100" style="max-width: 500px;">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">Cadastro</h2>

                <!-- Exibição de mensagens de erro/sucesso -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" 
                      method="POST" 
                      class="needs-validation" 
                      novalidate>
                    
                    <!-- Campo Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        <div class="invalid-feedback">
                            Por favor, insira um email válido.
                        </div>
                    </div>

                    <!-- Campo Senha -->
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" 
                               class="form-control" 
                               id="senha" 
                               name="senha" 
                               required
                               minlength="6">
                        <div class="invalid-feedback">
                            A senha deve ter no mínimo 6 caracteres.
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            Cadastrar
                        </button>
                        <a href="login.php" class="btn btn-outline-light">
                            Já tenho conta
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Validação do formulário -->
    <script>
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
    </script>
</body>
</html>