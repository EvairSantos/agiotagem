<?php
// Inclui o arquivo de conexão com o banco de dados
require_once '../includes/db.php'; // Certifique-se de que o caminho está correto

try {
    // Dados do novo usuário
    $novoUsuario = 'admin_user'; // Nome do usuário a ser criado
    $senha = password_hash('senha123', PASSWORD_BCRYPT); // Criptografa a senha
    $role = 'admin'; // Definindo a role como admin
    $email = 'admin@admin.com'; // Email do usuário

    // Verificar se o usuário já existe
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
    $stmt->bindParam(':username', $novoUsuario);
    $stmt->execute();
    $existe = $stmt->fetchColumn();

    if ($existe) {
        echo "Usuário já existe!";
    } else {
        // Inserir o novo usuário
        $sql = 'INSERT INTO users (username, password, role, email) 
                VALUES (:username, :password, :role, :email)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $novoUsuario);
        $stmt->bindParam(':password', $senha);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        echo "Usuário admin criado com sucesso!";
    }

} catch (PDOException $e) {
    echo 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
}
?>
