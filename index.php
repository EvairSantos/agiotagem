<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empréstimos</title>
    <link rel="stylesheet" href="assets/css/style_index.css?v=1.1">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo" class="logo-img">
            </div>
            <nav>
                <ul>
                    <li><a href="login.php" class="btn-login">Acessar a Conta</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="hero">
        <div class="hero-content">
            <h2>Empréstimos com as melhores condições</h2>
            <p>Simule e escolha a melhor opção para você.</p>
            <a href="#simular" class="cta-button">Simular Agora</a>
        </div>
    </section>
    <section class="emprestimos">
        <h2>Nossas Modalidades</h2>
        <div class="cards">
            <div class="card">
                <h3>Empréstimo 20 dias</h3>
                <p>20% de juros sobre o valor emprestado. Ideal para curto prazo!</p>
                <a href="#simular" class="btn-simular" data-dias="20" data-juros="20">Simular</a>
            </div>
            <div class="card">
                <h3>Empréstimo 30 dias</h3>
                <p>30% de juros. Para quem quer mais flexibilidade!</p>
                <a href="#simular" class="btn-simular" data-dias="30" data-juros="30">Simular</a>
            </div>
        </div>
    </section>
    <section class="contato" id="contato">
        <h2>Contato</h2>
        <form action="contato_site.php" method="post" id="contactForm">
            <input type="text" name="nome" placeholder="Seu Nome" required>
            <input type="tel" name="telefone" placeholder="Seu Telefone" required>
            <textarea name="mensagem" placeholder="Sua Mensagem" required></textarea>
            <button type="submit" class="btn-submit">Enviar Mensagem</button>
        </form>
    </section>
    <footer>
        <p>&copy; 2024 Empréstimos</p>
    </footer>
    <script src="assets/js/script.js"></script>
</body>
</html>
