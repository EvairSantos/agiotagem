<?php
/**
 * Arquivo: db.php
 * Descrição: Arquivo de conexão com o banco de dados MySQL usando PDO.
 *             Este arquivo é incluído em todas as páginas que precisam acessar o banco.
 */

// Carregar o arquivo .env com as variáveis de ambiente (caso esteja usando)
require_once __DIR__ . '/../vendor/autoload.php';

// Verificar se o arquivo .env existe
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Definindo as credenciais do banco de dados
$host     = $_ENV['DB_HOST'] ?? 'localhost';   // Endereço do servidor de banco de dados
$dbname   = $_ENV['DB_NAME'] ?? 'sistema_emprestimos'; // Nome do banco de dados
$username = $_ENV['DB_USER'] ?? 'root';        // Nome de usuário do banco de dados
$password = $_ENV['DB_PASS'] ?? '';            // Senha do banco de dados (deixe vazio para localhost)

// Opções para configurar o comportamento do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Define o modo de retorno para associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativa a emulação de consultas preparadas
];

// Tenta estabelecer a conexão com o banco de dados
try {
    // Instância do PDO que representa a conexão com o banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    // Debug: Mensagem de sucesso (use apenas para testes)
    // echo "Conexão com o banco de dados estabelecida com sucesso!";
    
} catch (PDOException $e) {
    // Em caso de erro, captura a exceção e exibe a mensagem
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

?>
