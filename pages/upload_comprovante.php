<?php
session_start();
if (!isset($_SESSION['id'])) {
    die("Você não está logado.");
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
        $emprestimo_id = $_POST['emprestimo_id'];
        $fileTmpPath = $_FILES['comprovante']['tmp_name'];
        $fileName = $_FILES['comprovante']['name'];
        $fileSize = $_FILES['comprovante']['size'];
        $fileType = $_FILES['comprovante']['type'];
        $fileNameCmps = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'comprovante_' . $emprestimo_id . '.' . $fileNameCmps;

        $uploadFileDir = '../comprovantes/';
        $dest_path = $uploadFileDir . $newFileName;

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true); // Cria a pasta se não existir
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Enviar foto e nome do cliente para o bot no Telegram
            $client_name = $_POST['client_name']; // Certifique-se de que o nome do cliente é enviado via POST

            $telegramToken = '7674815109:AAHgexGraKX8VIMp9FiM0i1LUb5B7U7ZK9w';
            $chatId = '571923494';

            $message = "Novo comprovante enviado!\nNome do Cliente: $client_name\nID do Empréstimo: $emprestimo_id";
            $url = "https://api.telegram.org/bot$telegramToken/sendPhoto";

            $postFields = [
                'chat_id' => $chatId,
                'photo' => new CURLFile(realpath($dest_path)), // Envia o arquivo
                'caption' => $message,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            // Excluir o arquivo após o envio
            if (file_exists($dest_path)) {
                unlink($dest_path); // Remove o arquivo do servidor
            }

            echo json_encode(['success' => true, 'message' => 'Comprovante enviado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar o comprovante.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi enviado.']);
    }
}
?>
