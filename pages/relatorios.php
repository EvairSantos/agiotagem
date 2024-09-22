<?php
require_once '../includes/db.php';

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é um cliente
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cliente') {
    header('Location: login.php');
    exit;
}

// Obtém o ID do cliente logado
$cliente_id = $_SESSION['id'] ?? null;

if ($cliente_id === null) {
    echo "Erro: Cliente ID não encontrado.";
    exit;
}

// Processa a pesquisa e filtros
$search = $_GET['search'] ?? '';

// Data atual
$current_date = new DateTime();
$warning_3_days = (clone $current_date)->modify('+3 days');
$warning_7_days = (clone $current_date)->modify('+7 days');

// Consulta dos usuários do cliente
$query_users = "SELECT id FROM users WHERE cliente_id = ?";
$stmt_users = $pdo->prepare($query_users);
$stmt_users->execute([$cliente_id]);
$usuarios = $stmt_users->fetchAll(PDO::FETCH_COLUMN);

// Verifica se existem usuários
if (empty($usuarios)) {
    $emprestimos = []; // Se não houver usuários, não há empréstimos
} else {
    // Consulta dos empréstimos dos usuários do cliente
    $query = "SELECT e.*, u.username FROM emprestimos e JOIN users u ON e.usuario_id = u.id WHERE e.usuario_id IN (" . implode(',', $usuarios) . ") AND u.username LIKE ?";
    $params = ["%$search%"];

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Divide os empréstimos conforme a situação
$atrasados = [];
$vencendo_3_dias = [];
$vencendo_7_dias = [];
$clientes_quitados = [];

foreach ($emprestimos as $emprestimo) {
    $data_pagamento = new DateTime($emprestimo['data_pagamento']);
    
    // Atrasados
    if ($data_pagamento < $current_date && $emprestimo['status'] !== 'pago') {
        $atrasados[] = $emprestimo;
    }
    // Vencendo em até 3 dias
    elseif ($data_pagamento >= $current_date && $data_pagamento <= $warning_3_days && $emprestimo['status'] !== 'pago') {
        $vencendo_3_dias[] = $emprestimo;
    }
    // Vencendo em até 7 dias
    elseif ($data_pagamento > $warning_3_days && $data_pagamento <= $warning_7_days && $emprestimo['status'] !== 'pago') {
        $vencendo_7_dias[] = $emprestimo;
    }
    // Clientes quitados
    elseif ($emprestimo['status'] === 'pago') {
        $clientes_quitados[] = $emprestimo;
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatórios de Empréstimos</title>
    <link rel="stylesheet" href="../assets/css/usuarios.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <h1>Relatórios de Empréstimos</h1>

    <form method="GET">
        <input type="text" name="search" placeholder="Pesquisar usuário" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Pesquisar</button>
    </form>

    <h2>Atrasados</h2>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Valor do Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Valor a Pagar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($atrasados as $emprestimo): ?>
                <tr>
                    <td data-label="Nome de Usuário"><?php echo htmlspecialchars($emprestimo['username']); ?></td>
                    <td data-label="Valor emprestado"><?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></td>
                    <td data-label="Data do pagamento"><?php echo date('d/m/Y', strtotime($emprestimo['data_pagamento'])); ?></td>
                    <td data-label="Valor devido"><?php echo number_format($emprestimo['total_devido'], 2, ',', '.'); ?></td>
                    <td data-label="Status"><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Vencem em até 3 dias</h2>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Valor do Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Valor a Pagar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vencendo_3_dias as $emprestimo): ?>
                <tr>
                    <td data-label="Nome de Usuário"><?php echo htmlspecialchars($emprestimo['username']); ?></td>
                    <td data-label="Valor emprestado"><?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></td>
                    <td data-label="Data do pagamento"><?php echo date('d/m/Y', strtotime($emprestimo['data_pagamento'])); ?></td>
                    <td data-label="Valor devido"><?php echo number_format($emprestimo['total_devido'], 2, ',', '.'); ?></td>
                    <td data-label="Status"><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Vencem em até 7 dias</h2>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Valor do Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Valor a Pagar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vencendo_7_dias as $emprestimo): ?>
                <tr>
                    <td data-label="Nome de Usuário"><?php echo htmlspecialchars($emprestimo['username']); ?></td>
                    <td data-label="Valor emprestado"><?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></td>
                    <td data-label="Data do pagamento"><?php echo date('d/m/Y', strtotime($emprestimo['data_pagamento'])); ?></td>
                    <td data-label="Valor devido"><?php echo number_format($emprestimo['total_devido'], 2, ',', '.'); ?></td>
                    <td data-label="Status"><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Clientes Quitados</h2>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Valor do Empréstimo</th>
                <th>Data de Pagamento</th>
                <th>Valor Pago</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes_quitados as $emprestimo): ?>
                <tr>
                    <td data-label="Nome de Usuário"><?php echo htmlspecialchars($emprestimo['username']); ?></td>
                    <td data-label="Valor emprestado"><?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></td>
                    <td data-label="Data do pagamento"><?php echo date('d/m/Y', strtotime($emprestimo['data_pagamento'])); ?></td>
                    <td data-label="Valor devido"><?php echo number_format($emprestimo['total_devido'], 2, ',', '.'); ?></td>
                    <td data-label="Status"><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
