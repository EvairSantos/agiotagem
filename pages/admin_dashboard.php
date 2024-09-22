<?php
// Inicia a sessão
session_start();

// Inclui o arquivo de conexão com o banco de dados
require_once '../includes/db.php';

// Inicializa as variáveis
$cliente_nome = '';
$cliente_email = '';
$cliente_telefone = '';

// Função para cadastrar novos clientes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_cliente'])) {
    $cliente_nome = trim($_POST['cliente_nome']);
    $cliente_email = trim($_POST['cliente_email']);
    $cliente_telefone = trim($_POST['cliente_telefone']);
    $cliente_senha = password_hash(trim($_POST['cliente_senha']), PASSWORD_DEFAULT); // Criptografa a senha

    // Verifica se já existe um cliente com o mesmo nome, e-mail ou telefone
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome = ? OR email = ? OR telefone = ?");
    $stmt->execute([$cliente_nome, $cliente_email, $cliente_telefone]);
    $existing_client = $stmt->fetch();

    if ($existing_client) {
        $error = "Já existe um cliente com o mesmo nome, e-mail ou telefone!";
    } else {
        if (!empty($cliente_nome) && !empty($cliente_email) && !empty($cliente_telefone) && !empty($cliente_senha)) {
            // Insere o cliente no banco de dados
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cliente_nome, $cliente_email, $cliente_telefone, $cliente_senha]);
            $success = "Cliente cadastrado com sucesso!";
            
            // Limpa os campos do formulário
            $cliente_nome = $cliente_email = $cliente_telefone = '';
        } else {
            $error = "Todos os campos são obrigatórios!";
        }
    }
}

// Função para listar todos os clientes
$stmt = $pdo->prepare("SELECT * FROM clientes");
$stmt->execute();
$clientes = $stmt->fetchAll();

// Função para listar todos os usuários de cada cliente
function getUsers($pdo, $cliente_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE cliente_id = ?");
    $stmt->execute([$cliente_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Atualizar senha do cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_senha'])) {
    $cliente_id = intval($_POST['cliente_id']);
    $nova_senha = password_hash(trim($_POST['nova_senha']), PASSWORD_DEFAULT);

    if (!empty($nova_senha)) {
        $stmt = $pdo->prepare("UPDATE clientes SET senha = ? WHERE id = ?");
        $stmt->execute([$nova_senha, $cliente_id]);
        $success = "Senha atualizada com sucesso!";
    } else {
        $error = "A nova senha é obrigatória!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Sistema de Empréstimos</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css"> <!-- Inclui o CSS -->
    <style>
        .users-list {
            display: none;
            margin-left: 20px;
            list-style: none;
        }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>
    <div class="admin-dashboard">

        <!-- Exibe mensagens de sucesso ou erro -->
        <?php if (isset($success)): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Formulário para cadastrar novos clientes -->
        <h2>Cadastrar Novo Cliente</h2>
        <form action="admin_dashboard.php" method="POST">
            <div class="form-group">
                <label for="cliente_nome">Nome do Cliente:</label>
                <input type="text" name="cliente_nome" id="cliente_nome" value="<?php echo htmlspecialchars($cliente_nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="cliente_email">E-mail:</label>
                <input type="email" name="cliente_email" id="cliente_email" value="<?php echo htmlspecialchars($cliente_email); ?>" required>
            </div>
            <div class="form-group">
                <label for="cliente_telefone">Telefone:</label>
                <input type="text" name="cliente_telefone" id="cliente_telefone" value="<?php echo htmlspecialchars($cliente_telefone); ?>" required>
            </div>
            <div class="form-group">
                <label for="cliente_senha">Senha:</label>
                <input type="password" name="cliente_senha" id="cliente_senha" required>
            </div>
            <button type="submit" name="add_cliente">Cadastrar Cliente</button>
        </form>

        <!-- Lista de Clientes e Acompanhamento -->
        <h2>Clientes Cadastrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                <tr class="cliente-row" data-cliente-id="<?php echo $cliente['id']; ?>">
                    <td><?php echo $cliente['nome']; ?></td>
                    <td><?php echo $cliente['email']; ?></td>
                    <td><?php echo $cliente['telefone']; ?></td>
                    <td><?php echo "Mensal"; ?></td>
                    <td>
                        <button type="button" onclick="showUpdateForm(<?php echo $cliente['id']; ?>)">SENHA</button>
                    </td>
                </tr>
                <!-- Formulário para atualizar a senha -->
                <tr id="update-form-<?php echo $cliente['id']; ?>" style="display: none;">
                    <td colspan="6">
                        <form action="admin_dashboard.php" method="POST">
                            <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha:</label>
                                <input type="password" name="nova_senha" required>
                            </div>
                            <button type="submit" name="update_senha">Atualizar Senha</button>
                        </form>
                    </td>
                </tr>
                <!-- Lista de usuários associados ao cliente -->
                <tr class="users-row" id="users-row-<?php echo $cliente['id']; ?>">
                    <td colspan="6">
                        <ul class="users-list" id="users-list-<?php echo $cliente['id']; ?>">
                            <?php 
                            $users = getUsers($pdo, $cliente['id']);
                            if (count($users) > 0):
                                foreach ($users as $user): ?>
                                    <li><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</li>
                                <?php endforeach;
                            else: ?>
                                <li>Nenhum user cadastrado para este cliente.</li>
                            <?php endif; ?>
                        </ul>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Função para mostrar/ocultar usuários ao clicar em um cliente
        document.querySelectorAll('.cliente-row').forEach(row => {
            row.addEventListener('click', function() {
                var clienteId = this.getAttribute('data-cliente-id');
                var usersList = document.getElementById('users-list-' + clienteId);
                usersList.style.display = usersList.style.display === 'none' || usersList.style.display === '' ? 'block' : 'none';
            });
        });

        // Função para mostrar o formulário de atualização de senha
        function showUpdateForm(clienteId) {
            var updateForm = document.getElementById('update-form-' + clienteId);
            updateForm.style.display = updateForm.style.display === 'none' || updateForm.style.display === '' ? 'table-row' : 'none';
        }
    </script>
</body>
</html>
