document.getElementById('contactForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Previne o envio padrão do formulário

    const nome = this.nome.value.trim();
    const telefone = this.telefone.value.trim();
    const mensagem = this.mensagem.value.trim();

    // Valida os campos
    if (nome === '' || telefone === '' || mensagem === '') {
        alert('Por favor, preencha todos os campos.');
        return;
    }

    // Se tudo estiver correto, envia o formulário
    this.submit();
});
