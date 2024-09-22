<?php
session_start();
if (!isset($_SESSION['id'])) {
    die("Você não está logado.");
}

require_once '../includes/db.php';

// Obtém as informações do usuário
$stmt = $pdo->prepare("SELECT username, email, telefone, localizacao, fotos FROM users WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuário não encontrado.");
}

// Inicializa as variáveis para evitar erros
$valoresEmprestados = 0.0;
$valoresQuitados = 0.0;
$emprestimosEmAberto = [];
$emprestimosQuitados = [];

// Obtém os valores dos empréstimos em aberto
$emprestimosEmAbertoStmt = $pdo->prepare("SELECT id, valor, data_pagamento, total_devido FROM emprestimos WHERE usuario_id = ? AND status = 'ativo'");
$emprestimosEmAbertoStmt->execute([$_SESSION['id']]);
$emprestimosEmAberto = $emprestimosEmAbertoStmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($emprestimosEmAberto)) {
    $valoresEmprestados = array_sum(array_column($emprestimosEmAberto, 'valor'));
}

// Obtém os valores dos empréstimos quitados
$emprestimosQuitadosStmt = $pdo->prepare("SELECT valor, data_pagamento, total_devido FROM emprestimos WHERE usuario_id = ? AND status = 'pago'");
$emprestimosQuitadosStmt->execute([$_SESSION['id']]);
$emprestimosQuitados = $emprestimosQuitadosStmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($emprestimosQuitados)) {
    $valoresQuitados = array_sum(array_column($emprestimosQuitados, 'valor'));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Empréstimos</title>
    <link rel="stylesheet" href="../assets/css/usuario_dashboard.css">
    <style>
        .message {
            color: green;
            font-weight: bold;
            margin: 10px 0;
        }
        .error {
            color: red;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    
<?php require_once '../includes/header.php'; ?>
<div class="container">
    <h1>Bem-vindo, <?php echo htmlspecialchars($usuario['username'] ?? 'Usuário'); ?>!</h1>
    <div class="user-info">
    <?php if (!empty($usuario['fotos'])): ?>
        <p><strong>Foto Atual:</strong></p>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($usuario['fotos']); ?>" alt="Foto do Usuário" width="150" height="150">
    <?php else: ?>
        <p>Sem foto disponível</p>
    <?php endif; ?>

        <p>Email: <?php echo htmlspecialchars($usuario['email'] ?? 'Não disponível'); ?></p>
        <p>Telefone: <?php echo htmlspecialchars($usuario['telefone'] ?? 'Não disponível'); ?></p>
        <p>Localização: 
    <a class="btn btn-google-maps" href="<?php echo htmlspecialchars($usuario['localizacao'] ?? '#'); ?>" target="_blank">Ver no Google Maps</a>
</p>
 </div>

    <div class="info">
        <h3>Empréstimos em Aberto</h3>
        <p>Valores Emprestados: R$ <?php echo number_format($valoresEmprestados, 2, ',', '.'); ?></p>
        <table>
            <tr>
                <th>Valor do Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Total a Pagar</th>
                <th>Ações</th>
            </tr>
            <?php if (!empty($emprestimosEmAberto)): ?>
                <?php foreach ($emprestimosEmAberto as $emprestimo): ?>
                    <tr>
                        <td>R$ <?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['data_pagamento']); ?></td>
                        <td>R$ <?php echo number_format($emprestimo['total_devido'], 2, ',', '.'); ?></td>
                        <td>
                            <button onclick="gerarPix(<?= urlencode($emprestimo['total_devido']); ?>)" class="btn">Gerar PIX</button>
             
    <input type="file" id="comprovante-<?= $emprestimo['id']; ?>" name="comprovante" accept="image/*" required onchange="enviarComprovante(<?= $emprestimo['id']; ?>)" style="display: none;">
    <button onclick="document.getElementById('comprovante-<?= $emprestimo['id']; ?>').click();" class="btn">Pago</button>
    <div id="message-<?= $emprestimo['id']; ?>" class="message" style="display: none; color: green;"></div>
    <div id="error-<?= $emprestimo['id']; ?>" class="error" style="display: none; color: red;"></div>
</td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nenhum empréstimo em aberto encontrado.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div id="resultadoPix" class="info" style="display: none;">
        <h3>Código PIX Gerado</h3>
        <textarea class="form-control" id="brcodepix" rows="4" readonly></textarea>
        <button type="button" class="btn btn-primary" onclick="copiar()">Copiar Código</button>
        <img id="qrCodeImg" src="" alt="QR Code" style="display: none; margin-top: 10px;"/> 
    </div>

    <div class="info">
        <h3>Empréstimos Quitados</h3>
        <p>Valores Quitados: R$ <?php echo number_format($valoresQuitados, 2, ',', '.'); ?></p>
        <table>
            <tr>
                <th>Valor do Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Valor Pago</th>
            </tr>
            <?php if (!empty($emprestimosQuitados)): ?>
                <?php foreach ($emprestimosQuitados as $emprestimo): ?>
                    <tr>
                        <td>R$ <?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['data_pagamento']); ?></td>
                        <td>R$ <?php echo number_format($emprestimo['total_devido'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nenhum empréstimo quitado encontrado.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <section class="emprestimos">
        <h2>Nossas Modalidades</h2>
        <div class="cards">
            <div class="card">
                <h3>Empréstimo 20 dias</h3>
                <p>20% de juros sobre o valor emprestado. Ideal para curto prazo!</p>
                <a href="#simular" class="btn-simular" data-dias="20" data-juros="20" onclick="checkContratar(this);">contratar</a>
            </div>
            <div class="card">
                <h3>Empréstimo 30 dias</h3>
                <p>30% de juros. Para quem quer mais flexibilidade!</p>
                <a href="#simular" class="btn-simular" data-dias="30" data-juros="30" onclick="checkContratar(this);">contratar</a>
            </div>
        </div>
    </section>

    <div class="alert" id="alertContratar" style="display: none;">
        <p style="color: red;">Você possui empréstimos em aberto e não pode contratar novas modalidades.</p>
    </div>
</div>

<script>
function gerarPix(valor) {
    fetch(`../pix-main/index.php?valor=${valor}`)
        .then(response => response.text())
        .then(data => {
            const [pixCode, qrFile] = data.split('|');
            document.getElementById("brcodepix").value = pixCode;
            document.getElementById("resultadoPix").style.display = "block";

            const qrCodeImg = document.getElementById("qrCodeImg");
            qrCodeImg.src = qrFile;
            qrCodeImg.style.display = "block";
        })
        .catch(error => console.error('Erro ao gerar o PIX:', error));
}

function checkContratar(element) {
    const alertContratar = document.getElementById("alertContratar");
    const emprestimosEmAberto = <?php echo json_encode($emprestimosEmAberto); ?>;

    if (emprestimosEmAberto.length > 0) {
        alertContratar.style.display = "block";
    } else {
        window.location.href = element.href;
    }
}
function enviarComprovante(id) {
    const comprovanteInput = document.getElementById('comprovante-' + id);
    const messageElement = document.getElementById("message-" + id);
    const errorElement = document.getElementById("error-" + id);
    const clientName = "<?php echo htmlspecialchars($usuario['username']); ?>"; // Captura o nome do cliente

    // Verifica se um arquivo foi selecionado e se é uma imagem
    if (!comprovanteInput.files.length || !comprovanteInput.files[0].type.startsWith('image/')) {
        errorElement.textContent = "Por favor, escolha um arquivo de imagem.";
        errorElement.style.display = "block";
        messageElement.style.display = "none";
        return;
    }

    const formData = new FormData();
    formData.append('comprovante', comprovanteInput.files[0]);
    formData.append('emprestimo_id', id);
    formData.append('client_name', clientName); // Adiciona o nome do cliente ao FormData

    fetch('upload_comprovante.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageElement.textContent = data.message;
            messageElement.style.display = "block";
            errorElement.style.display = "none";
        } else {
            errorElement.textContent = data.message;
            errorElement.style.display = "block";
            messageElement.style.display = "none";
        }
    })
    .catch(error => {
        errorElement.textContent = "Erro ao enviar o comprovante.";
        errorElement.style.display = "block";
        messageElement.style.display = "none";
    });
}


</script>
</body>
</html>
