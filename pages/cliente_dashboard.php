<?php
require_once '../includes/db.php';

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e se é um cliente
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cliente' || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// ID do cliente logado
$cliente_id = $_SESSION['id'];

// Função para listar os users do cliente
$stmt = $pdo->prepare("SELECT * FROM users WHERE cliente_id = ?");
$stmt->execute([$cliente_id]);
$users = $stmt->fetchAll();

// Função para listar os empréstimos do cliente
$stmt_emp = $pdo->prepare("
    SELECT e.*, u.username 
    FROM emprestimos e 
    JOIN users u ON e.usuario_id = u.id 
    WHERE u.cliente_id = ?
");
$stmt_emp->execute([$cliente_id]);
$emprestimos = $stmt_emp->fetchAll();

// Função para gerar a senha automática
function generatePassword() {
    $numbers = '';
    for ($i = 0; $i < 4; $i++) {
        $numbers .= rand(0, 9);
    }
    $letters = '';
    for ($i = 0; $i < 2; $i++) {
        $letters .= chr(rand(65, 90)); // Letras maiúsculas A-Z
    }
    return $numbers . $letters;
}

/// Processa o cadastro de um novo user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['username'];
    $password = generatePassword(); // Use a senha gerada
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $localizacao = $_POST['localizacao'];

    // Verifica se o nome já existe
    $stmt_check = $pdo->prepare("SELECT * FROM users WHERE username = ? AND cliente_id = ?");
    $stmt_check->execute([$nome, $cliente_id]);
    
    if ($stmt_check->rowCount() > 0) {
        echo "<script>alert('User já existe!');</script>";
    } else {
        // Verifica se o email ou telefone já estão em uso
        $stmt_check_email = $pdo->prepare("SELECT * FROM users WHERE email = ? OR telefone = ?");
        $stmt_check_email->execute([$email, $telefone]);
        
        if ($stmt_check_email->rowCount() > 0) {
            echo "<script>alert('Email ou telefone já estão em uso!');</script>";
        } else {
            // Processa a imagem
            $foto = null;
            if (isset($_FILES['fotos']) && $_FILES['fotos']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['fotos']['tmp_name'];
                $foto = file_get_contents($file); // Lê o conteúdo da imagem
            }

            // Insere o novo user no banco de dados
            $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, role, cliente_id, email, telefone, localizacao, fotos) VALUES (?, ?, 'usuario', ?, ?, ?, ?, ?)");
            $stmt_insert->execute([$nome, $passwordHash, $cliente_id, $email, $telefone, $localizacao, $foto]);

// Gera o link do WhatsApp
$mensagem = "Novo Cadastro:\nNome: $nome\nSenha: $password\nEmail: $email\nTelefone: $telefone\nAcesse o site: cliente.meusite.com";
$linkWhatsApp = "https://api.whatsapp.com/send?phone=$telefone&text=" . urlencode($mensagem);

// Redireciona para o WhatsApp
echo "<script>window.location.href = '$linkWhatsApp';</script>";
exit;

        }
    }
}

// Processa a remoção de usuário
if (isset($_GET['remove_user_id'])) {
    $user_id = $_GET['remove_user_id'];
    
    // Remove o user
    $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ? AND cliente_id = ?");
    $stmt_delete->execute([$user_id, $cliente_id]);
    
    echo "<script>alert('User removido com sucesso!');</script>";
    header('Location: cliente_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard do Cliente</title>
    <link rel="stylesheet" href="../assets/css/cliente_dashboard.css">
    <script>
    // Função para capturar localização
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const link = `https://www.google.com/maps?q=${lat},${lon}`;
                document.getElementById('localizacao').value = link;
                console.log(`Localização capturada: ${link}`);
            }, function() {
                console.error("Erro ao obter localização. Tentando novamente...");
                // Tentar novamente
                getLocation();
            });
        } else {
            console.error("Geolocalização não é suportada por este navegador.");
        }
    }

    // Função para exibir/ocultar o formulário
    function toggleForm() {
        const form = document.getElementById('userForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    // Gera e preenche a senha no formulário
    function gerarSenha() {
        const senha = '<?php echo generatePassword(); ?>'; // Gera a senha do servidor
        document.getElementById('senha').value = senha; // Preenche o input da senha
    }
</script>

</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <h1>Dashboard do Cliente</h1>

    <button onclick="toggleForm()" class="toggle-btn">Cadastrar Novo User</button> <!-- Botão para mostrar/ocultar o formulário -->
    
    <form id="userForm" method="POST" enctype="multipart/form-data" style="display: none;" onsubmit="gerarSenha();"> <!-- Formulário oculto por padrão -->
        <input type="text" name="username" placeholder="Nome de User" required>
        <input type="text" id="senha" name="password" placeholder="Senha" readonly> <!-- Campo da senha agora é preenchido automaticamente -->
        <input type="email" name="email" placeholder="Email" required>
        <input type="tel" name="telefone" placeholder="Telefone" required pattern="\d{11}" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '');" inputmode="numeric">
        <input type="text" name="localizacao" id="localizacao" placeholder="Localização" onclick="getLocation();" readonly>
        <input type="file" name="fotos" accept="image/*" capture="camera">
        <button type="submit">Cadastrar</button>
    </form>
    
    <h2>Empréstimos</h2>
    <table>
        <tr>
            <th>Nome</th>
            <th>Valor</th>
            <th>Prazo (dias)</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($emprestimos as $emp): ?>
        <tr>
            <td><?php 
                $primeiro_nome = explode(' ', htmlspecialchars($emp['username']))[0]; 
                echo $primeiro_nome; 
            ?></td>
            <td><?php echo htmlspecialchars($emp['valor']); ?></td>
            <td><?php echo htmlspecialchars($emp['prazo']); ?></td>
            <td><?php echo htmlspecialchars($emp['status']); ?></td>
            <td>
                <a href="visualizar_user.php?id=<?php echo $emp['usuario_id']; ?>">Visualizar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
