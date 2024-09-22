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

// ID do usuário a ser visualizado
$user_id = $_GET['id'];

// Busca as informações do usuário
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND cliente_id = ?");
$stmt->execute([$user_id, $_SESSION['id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "Usuário não encontrado.";
    exit;
}

// Busca os empréstimos do usuário, ordenando os ativos primeiro
$stmt_emprestimos = $pdo->prepare("
    SELECT * FROM emprestimos 
    WHERE usuario_id = ? 
    ORDER BY 
        CASE 
            WHEN status = 'ativo' THEN 1 
            ELSE 2 
        END
");
$stmt_emprestimos->execute([$user_id]);
$emprestimos = $stmt_emprestimos->fetchAll();

// Verifica se o usuário tem empréstimos ativos
$tem_emprestimos_ativos = false;
foreach ($emprestimos as $emprestimo) {
    if ($emprestimo['status'] === 'ativo') {
        $tem_emprestimos_ativos = true;
        break;
    }
}

// Se o botão de remoção foi acionado
if (isset($_POST['remover'])) {
    if ($tem_emprestimos_ativos) {
        $mensagem_erro = "Este cliente não pode ser removido porque possui empréstimos ativos.";
    } else {
        // Lógica para remover o cliente
        $stmt_remover = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt_remover->execute([$user_id])) {
            $_SESSION['mensagem_sucesso'] = "Usuário removido com sucesso!";
            header('Location: usuarios.php'); // Redireciona para usuarios.php
            exit;
        } else {
            $mensagem_erro = "Erro ao remover o usuário.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Usuário</title>
    <link rel="stylesheet" href="../assets/css/visualizar_user.css">
    <script>
        function confirmarRemocao() {
            return confirm("Você realmente deseja remover o cliente <?php echo htmlspecialchars($user['username']); ?>? Esta ação não pode ser desfeita.");
        }
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <h1>Informações do Usuário</h1>
    
    <div class="informacoes-usuario">
        <div class="usuario-info">
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
            <p><strong>Tel:</strong> <?php echo htmlspecialchars($user['telefone'] ?? 'N/A'); ?></p>
            <p><strong>Localização:</strong> <a class="btn-mapa" href="<?php echo htmlspecialchars($user['localizacao']); ?>" target="_blank">Ver no mapa</a></p>
            <p><strong>Data de Criação:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
        </div>

        <div class="usuario-foto">
            <?php if (!empty($user['fotos'])): ?>
                <p><strong>Foto:</strong></p>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['fotos']); ?>" alt="Foto do Usuário" />
            <?php else: ?>
                <p><strong>Foto:</strong> N/A</p>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div>
            <a href="editar_user.php?id=<?php echo $user['id']; ?>" class="btn-editar">Editar Cliente</a>
            <?php if (!$tem_emprestimos_ativos): ?>
                <form method="POST" onsubmit="return confirmarRemocao()" style="display: inline;">
                    <button type="submit" name="remover" class="btn-editar">Remover Cliente</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (isset($mensagem_erro)): ?>
            <p style="color: red;"><?php echo $mensagem_erro; ?></p>
        <?php endif; ?>

        <?php if (isset($mensagem_sucesso)): ?>
            <p style="color: green;"><?php echo $mensagem_sucesso; ?></p>
        <?php endif; ?>
    </div>

    <h2>Empréstimos</h2>
    <table>
        <thead>
            <tr>
                <th>Valor</th>
                <th>Prazo</th>
                <th>Data de Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Status</th>
                <th>Juros</th>
                <th>Total Devido</th>
                <th>Data Pagamento Real</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($emprestimos as $emprestimo): ?>
                <tr>
                    <td data-label="Valor"><?php echo htmlspecialchars($emprestimo['valor']); ?></td>
                    <td data-label="Prazo"><?php echo htmlspecialchars($emprestimo['prazo']); ?> dias</td>
                    <td data-label="Data de Empréstimo"><?php echo htmlspecialchars($emprestimo['data_emprestimo']); ?></td>
                    <td data-label="Data de Pagamento"><?php echo htmlspecialchars($emprestimo['data_pagamento']); ?></td>
                    <td data-label="Status"><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                    <td data-label="Juros"><?php echo htmlspecialchars($emprestimo['juros']); ?></td>
                    <td data-label="Total Devido"><?php echo htmlspecialchars($emprestimo['total_devido']); ?></td>
                    <td data-label="Data Pagamento Real"><?php echo htmlspecialchars($emprestimo['data_pagamento_real'] ?? 'NÃO PAGO❌'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
