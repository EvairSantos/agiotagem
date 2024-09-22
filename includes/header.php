<?php
/**
 * Inicia uma sessão e verifica se o usuário está logado.
 *
 * Esta seção do código também verifica o tempo de inatividade do usuário e
 * redireciona para a página de login após 5 minutos de inatividade.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado com base na presença do ID na sessão
$isLoggedIn = isset($_SESSION['id']);

// Define a variável $role com base na sessão para determinar o nível de acesso do usuário
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Tempo de inatividade em segundos (5 minutos)
$timeoutDuration = 300;

/**
 * Verifica se o tempo de última atividade foi definido e se excedeu o tempo limite.
 * Se sim, destrói a sessão e redireciona para a página de login.
 */
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeoutDuration) {
    session_unset();     // Limpa as variáveis da sessão
    session_destroy();   // Destrói a sessão
    header("Location: ../login.php"); // Redireciona para a página de login
    exit;
}

// Atualiza o tempo da última atividade
$_SESSION['LAST_ACTIVITY'] = time();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Empréstimos</title>
    <link rel="stylesheet" href="assets/css/style_header.css"> <!-- Link para a folha de estilo -->
    <style>
        /* Reset e configurações básicas */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif; /* Define a fonte padrão */
        }

        body {
            background-color: #0a192f; /* Azul escuro */
            color: #ffffff; /* Cor do texto */
            font-size: 18px; /* Tamanho da fonte padrão */
        }

        /* Estilo do Header */
        header {
            background-color: #0a192f; /* Cor do fundo do cabeçalho */
            padding: 10px 20px; /* Espaçamento interno */
            border-bottom: 2px solid #00a8e8; /* Borda inferior */
        }

        .nav-container {
            display: flex; /* Utiliza flexbox para layout */
            justify-content: space-between; /* Espaça os itens horizontalmente */
            align-items: center; /* Alinha os itens verticalmente */
        }

        .logo-link a {
            color: #ffffff; /* Cor do texto do logo */
            font-size: 1.5rem; /* Tamanho da fonte do logo */
            font-weight: bold; /* Negrito */
            text-decoration: none; /* Remove o sublinhado */
            letter-spacing: 1px; /* Espaçamento entre letras */
        }

        /* Menu Toggle (Hambúrguer) */
        .menu-button {
            font-size: 2rem; /* Tamanho da fonte do botão de menu */
            color: #ffffff; /* Cor do texto do botão */
            background: none; /* Sem fundo */
            border: none; /* Sem borda */
            cursor: pointer; /* Cursor de mão ao passar o mouse */
            display: none; /* Oculto no desktop */
        }

        /* Estilo dos links do menu */
        .nav-list {
            display: flex; /* Utiliza flexbox para layout */
            list-style: none; /* Remove marcadores da lista */
        }

        .nav-list li {
            margin-left: 20px; /* Espaçamento entre os itens do menu */
        }

        .nav-list li a {
            text-decoration: none; /* Remove o sublinhado */
            color: #ffffff; /* Cor do texto dos links */
            padding: 10px; /* Espaçamento interno dos links */
            transition: color 0.3s; /* Transição suave para mudança de cor */
        }

        .nav-list li a:hover {
            color: #00a8e8; /* Cor de destaque no hover */
        }

        /* Estilo da informação do usuário logado */
        .user-info-container {
            margin-top: 10px; /* Espaçamento superior */
            text-align: right; /* Alinha o texto à direita */
            color: #00a8e8; /* Cor do texto do usuário */
            font-size: 1rem; /* Tamanho da fonte da informação do usuário */
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .nav-list {
                flex-direction: column; /* Muda a direção para coluna em telas pequenas */
                align-items: center; /* Centraliza os itens */
                display: none; /* Esconde o menu */
            }

            .nav-list.active {
                display: flex; /* Mostra o menu ao clicar */
            }

            .nav-list li {
                margin: 5px 0; /* Espaçamento entre itens */
            }

            /* Botão hambúrguer visível em telas menores */
            .menu-button {
                display: block; /* Mostra o botão em telas pequenas */
            }

            /* Estilo do botão ao ser clicado */
            .menu-button.active {
                color: #00a8e8; /* Cor ao clicar */
            }
        }
    </style>
</head>
<body>
<header>
    <div class="nav-container">
        <div class="logo-link">
            <a href="../index.php">Sistema de Empréstimos</a> <!-- Logo do sistema -->
        </div>
        <button class="menu-button">&#9776;</button> <!-- Botão de menu hambúrguer -->
        <ul class="nav-list">
            <?php if ($role === 'cliente'): ?>
                <li><a href="cliente_dashboard.php">Dashboard</a></li>
                <li><a href="usuarios.php">Visualizar Cliente</a></li>
                <li><a href="emprestimos.php">Gerenciar Empréstimos</a></li>
                <li><a href="relatorios.php">Relatórios</a></li>
            <?php elseif ($role === 'admin'): ?>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
            <?php elseif ($role === 'usuario'): ?>
                <li><a href="usuario_dashboard.php">Dashboard</a></li>
                <li><a href="promocoes.php">Promoções</a></li>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
                <li><a href="logout.php">Sair</a></li> <!-- Link para logout -->
            <?php else: ?>
                <li><a href="../login.php">Login</a></li> <!-- Link para login -->
            <?php endif; ?>
        </ul>
    </div>

    <?php if ($isLoggedIn): ?>
        <div class="user-info-container">
            <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p> <!-- Mensagem de boas-vindas -->
        </div>
    <?php endif; ?>
</header>

<script>
    // Seleciona elementos do DOM para manipulação
    const menuButton = document.querySelector('.menu-button');
    const navList = document.querySelector('.nav-list');

    // Adiciona evento de clique para alternar o menu
    menuButton.addEventListener('click', () => {
        navList.classList.toggle('active');
        menuButton.classList.toggle('active');
    });

    // Atualiza o tempo de atividade ao clicar ou mover o mouse
    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('click', resetTimer);
    document.addEventListener('keypress', resetTimer);

    let timer; // Variável para armazenar o temporizador

    /**
     * Função para redefinir o timer de inatividade
     * Redireciona para a página de logout após 5 minutos (300000 ms) de inatividade.
     */
    function resetTimer() {
        clearTimeout(timer); // Limpa o timer anterior
        timer = setTimeout(() => {
            window.location.href = 'logout.php'; // Redireciona para logout
        }, 300000); // 5 minutos em milissegundos
    }

    resetTimer(); // Inicializa o timer ao carregar a página
</script>

</body>
</html>
