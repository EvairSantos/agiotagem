<?php
// Inicia uma nova sessão ou continua a existente
session_start();

// Inclui o arquivo de conexão com o banco de dados
require_once 'includes/db.php';

// Função para redirecionar o usuário após o login baseado no nível de acesso
function redirecionarPorNivel($role) {
    switch ($role) {
        case 'admin':
            header('Location: pages/admin_dashboard.php');
            exit;
        case 'cliente':
            header('Location: pages/cliente_dashboard.php');
            exit;
        case 'usuario':
            header('Location: pages/usuario_dashboard.php');
            exit;
        default:
            header('Location: login.php');
            exit;
    }
}

// Verifica se o formulário de login foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Por favor, insira o email e a senha.";
    } else {
        // Verifica na tabela 'clientes'
        $stmt = $pdo->prepare("SELECT id, email, senha, nome FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                redirecionarPorNivel($_SESSION['role']);
            } else {
                $error = "Email ou senha inválidos.";
            }
        } else if ($user && password_verify($password, $user['senha'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'cliente';
            $_SESSION['username'] = $user['nome'];

            redirecionarPorNivel($_SESSION['role']);
        } else {
            $error = "Email ou senha inválidos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Empréstimos</title>
    <link rel="stylesheet" href="assets/css/style_login.css?v=1.0">
    <style>
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2 class="title">Sistema de Empréstimos</h2>
    <img src="assets/images/login.png" alt="Imagem de Login" class="login-image">
    
    <?php if (isset($error)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="login">Email:</label>
            <input type="text" name="login" id="login" required>
        </div>

        <div class="form-group">
            <label for="password">Senha:</label>
            <div class="password-container">
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>
        </div>

        <button type="submit">Entrar</button>
    </form>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈'; // Olho fechado
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️'; // Olho aberto
    }
}
</script>
</body>
</html>
