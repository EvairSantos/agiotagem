<?php
require_once '../includes/db.php';

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e tem a permissão correta
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cliente' || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// ID do usuário a ser editado
$user_id = $_GET['id'];

// Busca as informações do usuário
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND cliente_id = ?");
$stmt->execute([$user_id, $_SESSION['id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "Usuário não encontrado.";
    exit;
}

// Processa a atualização do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $localizacao = $_POST['localizacao'];
    $password = $_POST['password'];

    // Verifica se uma nova senha foi enviada
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Cria um hash da nova senha
        $stmt_update = $pdo->prepare("UPDATE users SET username = ?, email = ?, telefone = ?, localizacao = ?, password = ? WHERE id = ?");
        $stmt_update->execute([$username, $email, $telefone, $localizacao, $hashed_password, $user_id]);
    } else {
        // Atualiza sem alterar a senha
        $stmt_update = $pdo->prepare("UPDATE users SET username = ?, email = ?, telefone = ?, localizacao = ? WHERE id = ?");
        $stmt_update->execute([$username, $email, $telefone, $localizacao, $user_id]);
    }

    // Verifica se um novo arquivo de foto foi enviado
    if (isset($_FILES['fotos']) && $_FILES['fotos']['error'] === UPLOAD_ERR_OK) {
        $foto = file_get_contents($_FILES['fotos']['tmp_name']);
        $stmt_update_foto = $pdo->prepare("UPDATE users SET fotos = ? WHERE id = ?");
        $stmt_update_foto->execute([$foto, $user_id]);
    }

    echo "<script>alert('Usuário atualizado com sucesso!');</script>";
    header('Location: visualizar_user.php?id=' . $user_id);
    exit;
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../assets/css/editar_user.css?=1.0">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <h1>Editar Usuário</h1>
    
    <?php if (!empty($user['fotos'])): ?>
        <p><strong>Foto Atual:</strong></p>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['fotos']); ?>" alt="Foto do Usuário" width="150" height="150">
    <?php else: ?>
        <p>Sem foto disponível</p>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
    <label for="username">Nome de Usuário</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    
    <label for="email">Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
    
    <label for="telefone">Telefone</label>
    <input type="text" name="telefone" 
       value="<?php echo htmlspecialchars($user['telefone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
       placeholder="Telefone" 
       required 
       pattern="\d{11}" 
       maxlength="11" 
       oninput="this.value = this.value.replace(/[^0-9]/g, '');" 
       inputmode="numeric">

    <label for="localizacao">Localização</label>
    <input type="text" name="localizacao" value="<?php echo htmlspecialchars($user['localizacao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <label for="password">Nova Senha</label>
    <input type="password" name="password" placeholder="Digite uma nova senha (opcional)">
    
    <label for="fotos">Foto do Usuário</label>
    <input type="file" name="fotos" accept="image/*">

    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <button type="submit">Atualizar</button>
        </div>
</form>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
