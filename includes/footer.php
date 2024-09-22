<?php
/**
 * Inicia a sessão se ainda não estiver ativa.
 * Isso é necessário para que a sessão do usuário funcione corretamente
 * nas páginas que utilizam variáveis de sessão.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<footer>
    <!-- Mensagem de copyright exibindo o ano atual -->
    <p>&copy; <?php echo date("Y"); ?> Sistema de Empréstimos. Todos os direitos reservados.</p>
</footer>

<!-- Link para o arquivo JavaScript que contém scripts adicionais -->
<script src="assets/js/script.js"></script>

<style>
    /* Estilo para o footer */
footer {
    position: fixed; /* Posiciona o footer fixo na parte inferior da página */
    bottom: 0; /* Alinha na parte inferior */
    left: 0; /* Alinha à esquerda */
    width: 100%; /* Largura total */
    background-color: #0a192f; /* Cor de fundo azul escuro */
    text-align: center; /* Centraliza o texto */
    padding: 10px 0; /* Espaçamento interno */
    color: #ffffff; /* Cor do texto */
    font-size: 0.9rem; /* Tamanho da fonte */
    border-top: 2px solid #00a8e8; /* Borda superior para destaque */
}

footer p {
    margin: 0; /* Remove margens extras para um layout mais limpo */
}

/* Responsividade para dispositivos móveis */
@media (max-width: 768px) {
    footer {
        font-size: 0.8rem; /* Reduz o tamanho da fonte em dispositivos menores */
        padding: 8px 0; /* Ajusta o espaçamento interno */
    }
}
</style>
</body>
</html>
