<?php
/**
 * Verificar status de pagamento
 */

header('Content-Type: application/json');
require_once 'config.php';

$transactionId = $_GET['transaction_id'] ?? '';

if (empty($transactionId)) {
    echo json_encode([
        'success' => false,
        'status' => 'invalid',
        'message' => 'ID de transação inválido'
    ]);
    exit;
}

// Verificar se está na pasta de pagos
$paidFile = PAID_DIR . '/' . $transactionId . '.json';
$pendingFile = PENDING_DIR . '/' . $transactionId . '.json';

if (file_exists($paidFile)) {
    // Pagamento confirmado
    $paymentData = json_decode(file_get_contents($paidFile), true);
    
    $tok = substr(hash('sha256', $transactionId . ACCESS_TOKEN_SALT), 0, 32);

    echo json_encode([
        'success' => true,
        'status' => 'paid',
        'tok' => $tok,
        'message' => 'Pagamento confirmado',
        'data' => [
            'transaction_id' => $transactionId,
            'amount' => $paymentData['valor'],
            'paid_at' => $paymentData['paid_at'] ?? null
        ]
    ]);
    
} elseif (file_exists($pendingFile)) {
    // Ainda pendente
    echo json_encode([
        'success' => true,
        'status' => 'pending',
        'message' => 'Aguardando pagamento'
    ]);
    
} else {
    // Não encontrado
    echo json_encode([
        'success' => false,
        'status' => 'not_found',
        'message' => 'Pagamento não encontrado'
    ]);
}
?>