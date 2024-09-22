<?php
session_start();
require_once '../includes/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'cliente' && $_SESSION['role'] !== 'usuario')) {
    header('Location: ../login.php');
    exit;
}

// ID do usuário logado
$usuario_id = $_SESSION['id'] ?? null;

// Processa o cadastro de um novo empréstimo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $valor = $_POST['valor'];
    $prazo = $_POST['prazo'];
    $juros = $_POST['juros'];
    $usuario = $_POST['usuario'];
    $data_emprestimo = date('Y-m-d');
    $data_pagamento = date('Y-m-d', strtotime("+$prazo days"));
    $total_devido = $valor + ($valor * ($juros / 100));

    $insert_stmt = $pdo->prepare("INSERT INTO emprestimos (usuario_id, valor, prazo, data_emprestimo, data_pagamento, juros, total_devido) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->execute([$usuario, $valor, $prazo, $data_emprestimo, $data_pagamento, $juros, $total_devido]);

    header('Location: emprestimos.php');
    exit;
}

// Lista os empréstimos do cliente logado
$stmt = $pdo->prepare("SELECT e.*, u.cliente_id FROM emprestimos e JOIN users u ON e.usuario_id = u.id WHERE u.cliente_id = ?");
$stmt->execute([$_SESSION['id']]);
$emprestimos = $stmt->fetchAll();

// Lista os usuários cadastrados para seleção
$usuarios_stmt = $pdo->prepare("SELECT * FROM users WHERE cliente_id = ?");
$usuarios_stmt->execute([$_SESSION['id']]);
$usuarios = $usuarios_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Empréstimos</title>
    <link rel="stylesheet" href="../assets/css/emprestimos.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .form-hidden {
            display: none; /* Esconde o formulário inicialmente */
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h2>Cadastrar Novo Empréstimo</h2>
    
    <button id="cadastrar-emprestimo">Cadastrar Empréstimo</button>
    
    <form id="emprestimo-form" class="form-hidden" method="POST" action="emprestimos.php">
        <div class="form-group">
            <select name="usuario" required>
                <option value="">Selecione um Usuário</option>
                <?php foreach ($usuarios as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <input type="number" name="valor" placeholder="Valor" step="0.01" required>
        </div>
        <div class="form-group">
            <input type="number" name="prazo" placeholder="Prazo (dias)" required>
        </div>
        <div class="form-group">
            <input type="number" name="juros" placeholder="Juros (%)" required>
        </div>
        <div class="form-group">
            <button type="submit">Cadastrar Empréstimo</button>
        </div>
    </form>

    <h2>Empréstimos Cadastrados</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Usuário</th>
            <th>Valor</th>
            <th>Prazo (dias)</th>
            <th>Data do Empréstimo</th>
            <th>Data de Pagamento</th>
            <th>Juros (%)</th>
            <th>Total Devido</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($emprestimos as $emp): ?>
        <tr id="emprestimo-<?php echo $emp['id']; ?>">
            <td><?php echo htmlspecialchars($emp['id']); ?></td>
            <td>
                <?php
                $user_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                $user_stmt->execute([$emp['usuario_id']]);
                $user = $user_stmt->fetch();
                echo htmlspecialchars($user['username']);
                ?>
            </td>
            <td><?php echo htmlspecialchars($emp['valor']); ?></td>
            <td><?php echo htmlspecialchars($emp['prazo']); ?></td>
            <td><?php echo htmlspecialchars($emp['data_emprestimo']); ?></td>
            <td><?php echo htmlspecialchars($emp['data_pagamento_real'] ?? 'Não Pago'); ?></td>
            <td><?php echo htmlspecialchars($emp['juros']); ?></td>
            <td><?php echo htmlspecialchars($emp['total_devido']); ?></td>
            <td><?php echo htmlspecialchars($emp['status']); ?></td>
            <td>
    <?php if ($emp['status'] === 'ativo'): ?>
        <button class="mark-pago" data-id="<?php echo $emp['id']; ?>">Marcar como Pago</button>
    <?php endif; ?>
    <?php if ($emp['status'] === 'pago'): ?>
        <button class="remove-emprestimo" data-id="<?php echo $emp['id']; ?>">Remover</button>
    <?php endif; ?>
</td>

        </tr>
        <?php endforeach; ?>
    </table>

    <script>
        $(document).ready(function() {
            $('#cadastrar-emprestimo').click(function() {
                $(this).hide(); // Esconde o botão de cadastrar
                $('#emprestimo-form').removeClass('form-hidden').fadeIn(); // Mostra o formulário
            });

            $('.mark-pago').click(function() {
                const emprestimoId = $(this).data('id');
                if (confirm('Tem certeza que deseja marcar este empréstimo como pago?')) {
                    $.post('emprestimos.php', { action: 'mark_pago', emprestimo_id: emprestimoId }, function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            $('#emprestimo-' + emprestimoId).find('td:nth-child(6)').text(new Date().toLocaleString());
                            alert('Empréstimo marcado como pago.');
                        } else {
                            alert('Erro ao marcar o empréstimo como pago: ' + data.error);
                        }
                    }).fail(function() {
                        alert('Erro de conexão ao marcar o empréstimo como pago.');
                    });
                }
            });

            $('.remove-emprestimo').click(function() {
                const emprestimoId = $(this).data('id');
                if (confirm('Tem certeza que deseja remover este empréstimo?')) {
                    $.post('emprestimos.php', { action: 'remove_emprestimo', emprestimo_id: emprestimoId }, function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            $('#emprestimo-' + emprestimoId).remove();
                            alert('Empréstimo removido com sucesso.');
                        } else {
                            alert('Erro ao remover o empréstimo: ' + data.error);
                        }
                    }).fail(function() {
                        alert('Erro de conexão ao remover o empréstimo.');
                    });
                }
            });
        });
    </script>

    <?php
    // Processa ações AJAX
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'mark_pago' && isset($_POST['emprestimo_id'])) {
                $stmt = $pdo->prepare("UPDATE emprestimos SET status = 'pago', data_pagamento_real = NOW() WHERE id = ?");
                $success = $stmt->execute([$_POST['emprestimo_id']]);
                echo json_encode(['success' => $success]);
                exit;
            }

            if ($_POST['action'] === 'remove_emprestimo' && isset($_POST['emprestimo_id'])) {
                $stmt = $pdo->prepare("DELETE FROM emprestimos WHERE id = ?");
                $success = $stmt->execute([$_POST['emprestimo_id']]);
                echo json_encode(['success' => $success]);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    ?>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
