<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'utils/security.php';

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    // Se não estiver autenticado, verificar token de "lembrar-me"
    if (isset($_COOKIE['remember_token'])) {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            // Verificar se o token é válido
            $stmt = $pdo->prepare("
                SELECT user_id, email 
                FROM user_tokens ut 
                JOIN users u ON u.id = ut.user_id 
                WHERE token = :token AND expiry > CURRENT_TIMESTAMP
            ");
            
            $stmt->execute(['token' => $_COOKIE['remember_token']]);
            
            if ($row = $stmt->fetch()) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['email'] = $row['email'];
            } else {
                // Token inválido ou expirado
                header('Location: login.php');
                exit();
            }
        } catch (Exception $e) {
            header('Location: login.php');
            exit();
        }
    } else {
        // Sem sessão e sem token
        header('Location: login.php');
        exit();
    }
}

// Sanitizar email para exibição
$email_display = htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard - Sistema</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .user-welcome {
            background: linear-gradient(45deg, #1a1a1a, #2c2c2c);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn {
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
        }
    </style>
</head>

<body class="bg-dark text-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-black mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-2"></i><?php echo $email_display; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="container">
        <div class="row justify-content-center">
            <!-- Card de Boas-vindas -->
            <div class="col-md-8 mb-4">
                <div class="user-welcome p-4 text-center">
                    <h2 class="mb-3">
                        <i class="fas fa-hand-wave me-2"></i>
                        Bem-vindo ao Sistema
                    </h2>
                    <p class="lead mb-0">
                        Você está logado como: <?php echo $email_display; ?>
                    </p>
                </div>
            </div>
            
            <!-- Botão de Logout -->
            <div class="col-md-8 text-center">
                <a href="logout.php" class="btn btn-danger logout-btn px-4 py-2">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Sair do Sistema
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para confirmar logout -->
    <script>
    document.querySelector('.logout-btn').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Tem certeza que deseja sair?')) {
            window.location.href = this.href;
        }
    });

    // Verificar sessão periodicamente
    setInterval(function() {
        fetch('check_session.php')
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    window.location.href = 'login.php';
                }
            });
    }, 60000); // Verificar a cada minuto
    </script>
</body>
</html>