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

// Função helper para tratar valores nulos
function safe_html($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Busca todos os usuários do cliente logado
$stmt = $pdo->prepare("SELECT * FROM users WHERE cliente_id = ?");
$stmt->execute([$_SESSION['id']]);
$usuarios = $stmt->fetchAll();

// Captura mensagem de sucesso, se existir
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? '';
unset($_SESSION['mensagem_sucesso']); // Limpa a mensagem após exibir
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Usuários</title>
    <link rel="stylesheet" href="../assets/css/usuarios.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <h1>Usuários</h1>

    <?php if ($mensagem_sucesso): ?>
        <p style="color: green;"><?php echo $mensagem_sucesso; ?></p>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Nome de Usuário</th>
                <th>Email</th>
                <th>Telefone</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr onclick="window.location.href='visualizar_user.php?id=<?php echo safe_html($usuario['id']); ?>';">
                    <td data-label="Nome de Usuário"><?php echo safe_html($usuario['username']); ?></td>
                    <td data-label="Email"><?php echo safe_html($usuario['email']); ?></td>
                    <td data-label="Telefone"><?php echo safe_html($usuario['telefone']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
