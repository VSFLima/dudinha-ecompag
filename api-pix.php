<?php
/**
 * API PIX - Gerar QR Code usando Ecompag
 */

header('Content-Type: application/json');
require_once 'config.php';

// Receber dados do POST
$data = json_decode(file_get_contents('php://input'), true);

// Validar dados recebidos
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
    exit;
}

// Extrair e validar campos
$nome = sanitize($data['name'] ?? '');
$email = sanitize($data['email'] ?? '');
$cpf = preg_replace('/[^0-9]/', '', $data['cpf'] ?? '');
$phone = sanitize($data['phone'] ?? '');
$valor = floatval($data['amount'] ?? 0);
$plano = sanitize($data['plan'] ?? 'monthly');

// Validações
$errors = [];

if (empty($nome) || strlen($nome) < 3) {
    $errors[] = 'Nome inválido';
}

if (!validarEmail($email)) {
    $errors[] = 'Email inválido';
}

if (!validarCPF($cpf)) {
    $errors[] = 'CPF inválido';
}

if ($valor <= 0) {
    $errors[] = 'Valor inválido';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro de validação',
        'errors' => $errors
    ]);
    exit;
}

// Descrição do pagamento baseado no plano
$descricoes = [
    'monthly' => 'Assinatura Mensal - Privacy Eduarda',
    'quarterly' => 'Assinatura Trimestral - Privacy Eduarda',
    'yearly' => 'Assinatura Anual - Privacy Eduarda'
];
$descricao = $descricoes[$plano] ?? 'Assinatura Privacy - Eduarda';

// Preparar dados para API Ecompag
$postData = [
    'client_id' => ECOMPAG_CLIENT_ID,
    'client_secret' => ECOMPAG_CLIENT_SECRET,
    'nome' => $nome,
    'cpf' => $cpf,
    'valor' => $valor,
    'descricao' => $descricao,
    'urlnoty' => WEBHOOK_URL
];

// Fazer requisição para Ecompag
$ch = curl_init('https://api.ecompag.com/v2/pix/qrcode.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Log da resposta para debug
file_put_contents('api-pix.log', 
    date('Y-m-d H:i:s') . " - Response: " . $response . PHP_EOL, 
    FILE_APPEND
);

if ($curlError) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar com o gateway de pagamento'
    ]);
    exit;
}

$apiResponse = json_decode($response, true);

if ($httpCode === 200 && isset($apiResponse['qrcode'])) {
    // Salvar pagamento como pendente
    $paymentId = generateUniqueId();
    $transactionId = $apiResponse['transactionId'];
    
    $paymentData = [
        'payment_id' => $paymentId,
        'transaction_id' => $transactionId,
        'status' => 'pending',
        'nome' => $nome,
        'email' => $email,
        'cpf' => $cpf,
        'phone' => $phone,
        'valor' => $valor,
        'plano' => $plano,
        'descricao' => $descricao,
        'qrcode' => $apiResponse['qrcode'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Salvar em arquivo JSON
    $filename = PENDING_DIR . '/' . $transactionId . '.json';
    file_put_contents($filename, json_encode($paymentData, JSON_PRETTY_PRINT));
    
    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'QR Code gerado com sucesso',
        'transaction_id' => $transactionId,
        'qrcode' => $apiResponse['qrcode'],
        'amount' => $valor,
        'reference_code' => $apiResponse['reference_code'] ?? $transactionId
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => $apiResponse['message'] ?? 'Erro ao gerar QR Code PIX',
        'details' => $apiResponse
    ]);
}
?>
