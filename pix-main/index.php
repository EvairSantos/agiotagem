<?php
session_start();

if (!isset($_SESSION['id'])) {
    die("Você não está logado.");
}

require_once '../includes/db.php';

$valor_pix = "0.00";

if (isset($_GET["valor"]) && is_numeric($_GET["valor"])) {
    $valor_pix = preg_replace("/[^0-9.]/", "", $_GET["valor"]);
    
    $chave_pix = "pix@techxx.com.br"; // Valor padrão
    $beneficiario_pix = "Evair Santos de Andrade"; // Valor padrão
    $cidade_pix = "SANTARÉM"; // Valor padrão
    $identificador = "***"; // Valor padrão

    include "phpqrcode/qrlib.php"; 
    include "funcoes_pix.php";

    $px[00] = "01"; // Payload Format Indicator
    $px[26][00] = "br.gov.bcb.pix"; // Arranjo específico
    $px[26][01] = $chave_pix;
    
    $px[52] = "0000"; // Merchant Category Code
    $px[53] = "986"; // Moeda - BRL
    if ($valor_pix > 0) {
        $px[54] = $valor_pix; // Valor a pagar
    }
    $px[58] = "BR"; // Código de país
    $px[59] = $beneficiario_pix; // Nome do beneficiário
    $px[60] = $cidade_pix; // Cidade do beneficiário
    $px[62][05] = $identificador; // Identificador

    $pix = montaPix($px);
    $pix .= "6304"; // Adiciona o campo do CRC
    $pix .= crcChecksum($pix); // Calcula o checksum

    // Gera o QR Code e salva em um arquivo
    $qrFile = '../pix-main/qrcodes/pix_' . time() . '.png'; // Define o nome do arquivo
    QRcode::png($pix, $qrFile, QR_ECLEVEL_L, 4); // Gera o QR Code

    // Retorna a linha do PIX e o caminho do QR Code
    echo htmlspecialchars($pix) . '|' . $qrFile; 
    exit;
}

?>
