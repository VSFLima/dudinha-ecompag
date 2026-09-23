<?php
/**
 * Webhook para receber notificações da Ecompag
 * Com PHPMailer para envio de emails
 */

// Iniciar output buffering
ob_start();

// Configurações
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');
ini_set('display_errors', 0);
error_reporting(0);

// Função de log
function logWebhook($msg, $data = null) {
    $log = "[" . date('Y-m-d H:i:s') . "] [IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') . "] $msg";
    if ($data) $log .= " | Dados: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @file_put_contents(__DIR__ . '/webhook.log', $log . "\n", FILE_APPEND);
}

logWebhook("========== NOVA REQUISICAO WEBHOOK ==========");

require_once 'config.php';

// Carregar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';
logWebhook("OK: PHPMailer carregado");

// Receber dados do webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

logWebhook("WEBHOOK: Dados recebidos", $data);

// Verificar se é uma notificação de pagamento PIX
if (isset($data['transactionType']) && $data['transactionType'] === 'RECEIVEPIX') {
    
    $transactionId = $data['transactionId'] ?? '';
    $status = $data['status'] ?? '';
    
    logWebhook("TIPO: RECEIVEPIX", ['transactionId' => $transactionId, 'status' => $status]);
    
    // Verificar se o pagamento foi confirmado
    if ($status === 'PAID') {
        
        logWebhook("STATUS: PAID - Processando pagamento");
        
        // Buscar dados do pagamento nos pendentes
        $pendingFile = PENDING_DIR . '/' . $transactionId . '.json';
        
        logWebhook("ARQUIVO: Buscando", ['path' => $pendingFile, 'exists' => file_exists($pendingFile)]);
        
        if (file_exists($pendingFile)) {
            
            // Carregar dados do pagamento
            $paymentData = json_decode(file_get_contents($pendingFile), true);
            logWebhook("DADOS: Pagamento carregado", $paymentData);
            
            // Atualizar status para pago
            $paymentData['status'] = 'paid';
            $paymentData['paid_at'] = date('Y-m-d H:i:s');
            $paymentData['webhook_data'] = $data;
            
            // Salvar no diretório de pagos
            $paidFile = PAID_DIR . '/' . $transactionId . '.json';
            file_put_contents($paidFile, json_encode($paymentData, JSON_PRETTY_PRINT));
            logWebhook("ARQUIVO: Salvo em paid", ['path' => $paidFile]);
            
            // Remover do pendentes
            unlink($pendingFile);
            logWebhook("ARQUIVO: Removido de pending");
            
            // Enviar email de confirmação
            logWebhook("EMAIL: Iniciando envio");
            $emailResult = enviarEmailConfirmacao($paymentData);
            
            // Log de sucesso
            $emailStatus = $emailResult ? 'EMAIL ENVIADO COM SUCESSO' : 'ERRO AO ENVIAR EMAIL';
            logWebhook("RESULTADO: $emailStatus");
            logWebhook("========== FIM WEBHOOK ==========\n");
            
            // Confirmar recebimento
            http_response_code(200);
            echo "OK";
            
        } else {
            logWebhook("AVISO: Pagamento nao encontrado em pending");
            logWebhook("========== FIM WEBHOOK ==========\n");
            
            http_response_code(200);
            echo "Payment not found";
        }
        
    } else {
        logWebhook("STATUS: " . $status . " - Ignorando");
        logWebhook("========== FIM WEBHOOK ==========\n");
        http_response_code(200);
        echo "Received";
    }
    
} else {
    logWebhook("TIPO: Outro tipo de notificacao - Ignorando");
    logWebhook("========== FIM WEBHOOK ==========\n");
    http_response_code(200);
    echo "Received";
}

/**
 * Função para enviar email de confirmação usando PHPMailer
 * SEM EMOJIS - COM PRIORIDADE ALTA
 */
function enviarEmailConfirmacao($paymentData) {
    $to = $paymentData['email'];
    $nome = $paymentData['nome'];
    $valor = number_format($paymentData['valor'], 2, ',', '.');
    $plano = $paymentData['plano'];
    $transactionId = $paymentData['transaction_id'];
    
    logWebhook("EMAIL: Preparando para", ['destinatario' => $to, 'nome' => $nome]);
    
    // Traduzir nome do plano
    $planos = [
        'teste' => 'Teste (24h)',
        'semanal' => 'Semanal',
        'mensal' => 'Mensal',
        'trimestral' => 'Trimestral',
        'anual' => 'Anual',
        'vitalicio' => 'Vitalicio'
    ];
    $planoNome = $planos[$plano] ?? 'Mensal';
    
    // Link de acesso configurável
    $accessLink = ACCESS_LINK;
    
    // Assunto
    $subject = EMAIL_SUBJECT;
    
    logWebhook("EMAIL: Dados processados", ['plano' => $planoNome, 'valor' => $valor]);
    
    // Corpo do email em HTML - SEM EMOJIS
    $emailHtml = '<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamento Confirmado</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f5f5f5">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f5f5f5;padding:20px 0">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1)">
          
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg, #ff7643, #ff6530);color:white;padding:40px 20px;text-align:center">
              <h1 style="margin:0;font-size:28px;font-weight:bold">Pagamento Confirmado!</h1>
            </td>
          </tr>
          
          <!-- Content -->
          <tr>
            <td style="padding:40px 30px">
              
              <!-- Success Badge -->
              <div style="text-align:center;margin-bottom:30px">
                <svg width="80" height="80" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="11" fill="#10b981"/>
                  <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                </svg>
              </div>
              
              <h2 style="color:#1a1a1a;font-size:24px;margin-bottom:20px;text-align:center">Ola, ' . htmlspecialchars($nome) . '!</h2>
              
              <p style="text-align:center;color:#666;font-size:16px;margin-bottom:30px;line-height:1.6">
                Seu pagamento foi confirmado com sucesso!<br>
                Agora voce tem acesso total ao conteudo exclusivo.
              </p>
              
              <!-- Info Box -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8f9fa;border-left:4px solid #ff7643;padding:20px;margin:20px 0;border-radius:5px">
                <tr>
                  <td>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                      <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #e0e0e0">
                          <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                              <td style="color:#666;font-weight:500;font-size:14px">Plano:</td>
                              <td align="right" style="color:#1a1a1a;font-weight:600;font-size:14px">Assinatura ' . htmlspecialchars($planoNome) . '</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #e0e0e0">
                          <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                              <td style="color:#666;font-weight:500;font-size:14px">Valor Pago:</td>
                              <td align="right" style="color:#1a1a1a;font-weight:600;font-size:14px">R$ ' . htmlspecialchars($valor) . '</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #e0e0e0">
                          <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                              <td style="color:#666;font-weight:500;font-size:14px">ID da Transacao:</td>
                              <td align="right" style="color:#1a1a1a;font-weight:600;font-size:14px">' . htmlspecialchars($transactionId) . '</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0">
                          <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                              <td style="color:#666;font-weight:500;font-size:14px">Data:</td>
                              <td align="right" style="color:#1a1a1a;font-weight:600;font-size:14px">' . date('d/m/Y H:i') . '</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
              
              <!-- Access Button -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="center" style="padding:30px 0">
                    <a href="' . htmlspecialchars($accessLink) . '" style="display:inline-block;background:linear-gradient(135deg, #ff7643, #ff6530);color:white;text-decoration:none;padding:18px 30px;border-radius:8px;font-weight:600;font-size:16px">Acessar Conteudo Agora</a>
                  </td>
                </tr>
              </table>
              
              <p style="text-align:center;color:#999;font-size:14px;margin-top:30px">
                Se voce tiver alguma duvida, entre em contato conosco.
              </p>
              
            </td>
          </tr>
          
          <!-- Footer -->
          <tr>
            <td style="background:#f8f9fa;padding:20px;text-align:center;color:#666;font-size:12px;line-height:1.6">
              <p style="margin:0 0 10px 0">
                2026 Privacy - Eduarda. Todos os direitos reservados.<br>
                Este e um email automatico, por favor nao responda.
              </p>
              <p style="margin:0">
                <a href="' . htmlspecialchars(SITE_URL) . '" style="color:#ff7643;text-decoration:none">Acessar Site</a> | 
                <a href="' . htmlspecialchars(SITE_URL) . '/suporte" style="color:#ff7643;text-decoration:none">Suporte</a>
              </p>
            </td>
          </tr>
          
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    // Corpo texto alternativo
    $emailText = "Pagamento Confirmado!\n\n";
    $emailText .= "Ola, {$nome}!\n\n";
    $emailText .= "Seu pagamento foi confirmado com sucesso!\n";
    $emailText .= "Agora voce tem acesso total ao conteudo exclusivo.\n\n";
    $emailText .= "Detalhes do Pagamento:\n";
    $emailText .= "- Plano: Assinatura {$planoNome}\n";
    $emailText .= "- Valor Pago: R$ {$valor}\n";
    $emailText .= "- ID da Transacao: {$transactionId}\n";
    $emailText .= "- Data: " . date('d/m/Y H:i') . "\n\n";
    $emailText .= "Acesse agora: {$accessLink}\n\n";
    $emailText .= "Se voce tiver alguma duvida, entre em contato conosco.\n\n";
    $emailText .= "2026 Privacy - Eduarda. Todos os direitos reservados.\n";
    $emailText .= "Este e um email automatico, por favor nao responda.";

    // Configurar PHPMailer
    $mail = new PHPMailer(true);

    try {
        logWebhook("SMTP: Configurando conexao");
        
        // Configurações SMTP do config.php
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->Timeout = 30;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        logWebhook("SMTP: Configurado", [
            'host' => SMTP_HOST, 
            'port' => SMTP_PORT, 
            'user' => SMTP_USER
        ]);
        
        // Remetente e destinatário
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to, $nome);
        
        // Prioridade ALTA
        $mail->Priority = 1;
        $mail->addCustomHeader('X-Priority', '1');
        $mail->addCustomHeader('X-MSMail-Priority', 'High');
        $mail->addCustomHeader('Importance', 'High');
        
        logWebhook("EMAIL: Headers configurados");
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $emailHtml;
        $mail->AltBody = $emailText;
        
        // Debug off
        $mail->SMTPDebug = 0;
        
        logWebhook("EMAIL: Enviando para", ['destinatario' => $to]);
        
        // Enviar
        $mail->send();
        
        logWebhook("SUCESSO: Email enviado com sucesso!", ['destinatario' => $to]);
        
        // Log extra no email.log
        file_put_contents('email.log',
            date('Y-m-d H:i:s') . " - Email para {$to}: ENVIADO COM SUCESSO - Plano: {$planoNome} - Valor: R$ {$valor}" . PHP_EOL,
            FILE_APPEND
        );
        
        return true;
        
    } catch (Exception $e) {
        logWebhook("ERRO: PHPMailer falhou", [
            'erro' => $mail->ErrorInfo, 
            'exception' => $e->getMessage(),
            'destinatario' => $to
        ]);
        
        // Log extra no email.log
        file_put_contents('email.log',
            date('Y-m-d H:i:s') . " - Email para {$to}: FALHOU - Erro: {$mail->ErrorInfo} - Exception: {$e->getMessage()}" . PHP_EOL,
            FILE_APPEND
        );
        
        return false;
    }
}