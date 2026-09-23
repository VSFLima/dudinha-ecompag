<?php
/**
 * Página de Sucesso - PROTEGIDA
 */
require_once 'config.php';

$transaction_id = $_GET['transaction_id'] ?? '';
$amount         = $_GET['amount'] ?? '';

file_put_contents('success.log',
    date('Y-m-d H:i:s') . " - Tentativa | Transaction: {$transaction_id} | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') . PHP_EOL,
    FILE_APPEND
);

if (empty($transaction_id)) {
    header('Location: index.html'); exit;
}

$paidFile = PAID_DIR . '/' . $transaction_id . '.json';
if (!file_exists($paidFile)) {
    header('Location: index.html'); exit;
}

$paymentData = json_decode(file_get_contents($paidFile), true);
if (!isset($paymentData['status']) || $paymentData['status'] !== 'paid') {
    header('Location: index.html'); exit;
}

file_put_contents('success.log',
    date('Y-m-d H:i:s') . " - ACESSO LIBERADO | Transaction: {$transaction_id} | Email: " . ($paymentData['email'] ?? 'N/A') . PHP_EOL,
    FILE_APPEND
);

// Validar token de acesso
$tok_recebido  = $_GET['tok'] ?? '';
$tok_esperado  = substr(hash('sha256', $transaction_id . ACCESS_TOKEN_SALT), 0, 32);
$acesso_valido = hash_equals($tok_esperado, $tok_recebido);

$valor  = $paymentData['valor']  ?? $amount;
$plano  = $paymentData['plano']  ?? 'mensal';
$nome   = $paymentData['nome']   ?? 'Cliente';
$email  = $paymentData['email']  ?? '';

$planos = [
    'semanal'        => '7 Dias',
    'mensal'         => '30 Dias',
    'trimestral'     => '3 Meses',
    'anual'          => '1 Ano',
    'vitalicio'      => 'Vitalício',
    'video-exclusivo'=> 'Vídeo Exclusivo',
];
$planoNome = $planos[$plano] ?? 'Mensal';
$dataHora  = date('d/m/Y \à\s H:i');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado — Privacy Eduarda</title>
    <link rel="icon" type="image/png" href="images/219-images-favicon.png">
    <script src="pixel.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

        :root {
            --orange: #ff6b35;
            --orange-deep: #e8551e;
            --green:  #2ecc71;
            --green-dark: #27ae60;
            --bg:     #0b0b0b;
            --surface: #161616;
            --surface2: #1e1e1e;
            --border: rgba(255,255,255,0.07);
            --text:   #f0f0f0;
            --muted:  #888;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 56px 20px 60px;
            -webkit-user-select: none;
            user-select: none;
            overflow-x: hidden;
        }

        /* ── Fundo animado ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 40% at 20% 10%, rgba(255,107,53,0.12) 0%, transparent 70%),
                radial-gradient(ellipse 50% 35% at 80% 90%, rgba(0,200,83,0.08) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        /* ── HEADER ── */
        .success-header {
            text-align: center;
            margin-bottom: 36px;
            animation: fadeDown 0.6s ease both;
        }

        @keyframes fadeDown {
            from { opacity:0; transform:translateY(-20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .check-ring {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 0 0 0 rgba(46,204,113,0.5);
            animation: pulseGreen 2.5s ease infinite;
        }

        @keyframes pulseGreen {
            0%   { box-shadow: 0 0 0 0   rgba(46,204,113,0.5); }
            60%  { box-shadow: 0 0 0 22px rgba(46,204,113,0);   }
            100% { box-shadow: 0 0 0 0   rgba(46,204,113,0);    }
        }

        .success-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 0px;
            line-height: 1.15;
            margin-bottom: 10px;
        }

        .success-header p {
            font-size: 15px;
            color: var(--muted);
            font-weight: 300;
            letter-spacing: 0.3px;
        }

        /* ── RECIBO ── */
        .receipt {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            animation: fadeUp 0.5s ease 0.1s both;
            box-shadow: 0 32px 80px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.04);
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .receipt-top {
            background: linear-gradient(135deg, #ff7a45, #e84d1c);
            padding: 24px 28px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .receipt-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .receipt-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            pointer-events: none;
            -webkit-user-drag: none;
        }

        .receipt-brand-text {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: white;
        }

        .receipt-badge {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        /* Linha tracejada */
        .receipt-divider {
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 0;
        }

        .receipt-circle-left,
        .receipt-circle-right {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--bg);
            flex-shrink: 0;
            margin: 0 -9px;
            z-index: 1;
        }

        .receipt-dashes {
            flex: 1;
            border-top: 2px dashed var(--border);
        }

        .receipt-rows {
            padding: 24px 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .row-label {
            font-size: 14px;
            color: var(--muted);
            font-weight: 400;
        }

        .row-value {
            font-size: 14px;
            color: var(--text);
            font-weight: 600;
            text-align: right;
        }

        .row-value.green {
            color: var(--green);
        }

        .row-value.amount {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 900;
            color: var(--orange);
        }

        .row-sep {
            border: none;
            border-top: 1px solid var(--border);
            margin: 2px 0;
        }

        /* ID transação truncado */
        .tx-id {
            font-size: 12px;
            color: var(--muted);
            font-family: monospace;
            word-break: break-all;
            text-align: right;
            max-width: 220px;
        }

        /* ── BOTÃO ACESSAR ── */
        .btn-access {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 20px 20px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            border-bottom: 4px solid #27ae60;
            border-radius: 14px;
            cursor: pointer;
            margin-top: 24px;
            box-shadow: 0 12px 36px rgba(46,204,113,0.3);
            transition: all 0.15s;
            animation: fadeUp 0.5s ease 0.25s both;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-access:active {
            transform: translateY(3px);
            border-bottom-width: 1px;
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--muted);
            font-size: 13px;
            text-decoration: none;
            animation: fadeUp 0.5s ease 0.3s both;
            transition: color 0.2s;
        }

        .btn-back:hover { color: var(--text); }

        /* ── POPUP MODAL ── */
        .popup {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            z-index: 2000;
            align-items: flex-end;
            justify-content: center;
            backdrop-filter: blur(6px);
            padding: 16px;
        }

        .popup.active {
            display: flex;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn { from{opacity:0} to{opacity:1} }

        .popup-card {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px 24px 24px;
            width: 100%;
            max-width: 460px;
            text-align: center;
            animation: slideSheet 0.3s cubic-bezier(0.34,1.56,0.64,1) both;
            box-shadow: 0 -20px 60px rgba(0,0,0,0.5);
        }

        @keyframes slideSheet {
            from { transform: translateY(40px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .popup-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--orange), var(--orange-deep));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .popup-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .popup-card p {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .popup-btn-confirm {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--orange), var(--orange-deep));
            color: white;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
            border-bottom: 4px solid #b83c10;
            border-radius: 12px;
            cursor: pointer;
            margin-bottom: 10px;
            transition: all 0.15s;
        }

        .popup-btn-confirm:active {
            transform: translateY(3px);
            border-bottom-width: 1px;
        }

        .popup-btn-cancel {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .popup-btn-cancel:active { opacity: 0.6; }

        /* ── RESPONSIVO ── */
        @media (max-width: 480px) {
            .success-header h1 { font-size: 22px; }
            .receipt-top { padding: 16px 18px 14px; }
            .receipt-rows { padding: 16px 18px; gap: 12px; }
            .receipt-divider { padding: 0 18px; }
            .row-value.amount { font-size: 16px; }
            .btn-access { font-size: 14px; padding: 15px; }
        }

        @media (max-width: 360px) {
            body { padding: 16px 12px 32px; }
            .check-ring { width: 60px; height: 60px; }
            .success-header h1 { font-size: 20px; }
            .tx-id { font-size: 10px; max-width: 160px; }
        }

        /* ── Proteção de mídia ── */
        img, video { -webkit-user-drag:none; user-drag:none; pointer-events:none; }
        button, a { pointer-events:all; }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- HEADER -->
    <div class="success-header">
        <div class="check-ring">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h1>Pagamento Confirmado!</h1>
        <p>Seu acesso foi liberado com sucesso</p>
    </div>

    <!-- RECIBO -->
    <div class="receipt">

        <div class="receipt-top">
            <div class="receipt-brand">
                <img src="images/images-logo.webp" alt="Privacy">
                <span class="receipt-brand-text">Privacy Eduarda</span>
            </div>
            <span class="receipt-badge">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:3px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                PAGO
            </span>
        </div>

        <div class="receipt-divider">
            <div class="receipt-circle-left"></div>
            <div class="receipt-dashes"></div>
            <div class="receipt-circle-right"></div>
        </div>

        <div class="receipt-rows">
            <div class="row">
                <span class="row-label">Cliente</span>
                <span class="row-value"><?php echo htmlspecialchars($nome); ?></span>
            </div>
            <div class="row">
                <span class="row-label">E-mail</span>
                <span class="row-value" style="font-size:12px"><?php echo htmlspecialchars($email); ?></span>
            </div>
            <div class="row">
                <span class="row-label">Plano</span>
                <span class="row-value"><?php echo htmlspecialchars($planoNome); ?></span>
            </div>
            <div class="row">
                <span class="row-label">Data</span>
                <span class="row-value"><?php echo $dataHora; ?></span>
            </div>
            <hr class="row-sep">
            <div class="row">
                <span class="row-label">Método</span>
                <span class="row-value" style="display:flex;align-items:center;gap:5px">
                    <svg width="14" height="14" viewBox="0 0 512 512"><path fill="#32BCAD" d="M242.4 292.5C247.8 287.1 257.1 287.1 262.5 292.5L339.5 369.5C353.7 383.7 372.6 391.5 392.6 391.5H407.7L310.6 488.6C280.3 518.1 231.1 518.1 200.8 488.6L103.3 391.2H112.6C132.6 391.2 151.5 383.4 165.7 369.2L242.4 292.5zM262.5 218.9C256.1 224.4 247.9 224.5 242.4 218.9L165.7 142.2C151.5 127.1 132.6 120.2 112.6 120.2H103.3L200.7 22.76C231.1-7.586 280.3-7.586 310.6 22.76L407.8 119.9H392.6C372.6 119.9 353.7 127.7 339.5 141.9L262.5 218.9zM112.6 142.7C126.4 142.7 139.1 148.3 149.7 158.1L226.4 234.8C233.6 241.1 243 245.6 252.5 245.6C261.9 245.6 271.3 241.1 278.5 234.8L355.5 157.8C365.3 148.1 378.8 142.5 392.6 142.5H430.3L488.6 200.8C518.9 231.1 518.9 280.3 488.6 310.6L430.3 368.9H392.6C378.8 368.9 365.3 363.3 355.5 353.5L278.5 276.5C264.6 262.6 240.3 262.6 226.4 276.6L149.7 353.2C139.1 363 126.4 368.6 112.6 368.6H80.78L22.76 310.6C-7.586 280.3-7.586 231.1 22.76 200.8L80.78 142.7H112.6z"/></svg>
                    PIX
                </span>
            </div>
            <div class="row">
                <span class="row-label">Status</span>
                <span class="row-value green" style="display:flex;align-items:center;gap:5px">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Aprovado
                </span>
            </div>
            <hr class="row-sep">
            <div class="row">
                <span class="row-label">Valor pago</span>
                <span class="row-value amount">R$ <?php echo number_format($valor, 2, ',', '.'); ?></span>
            </div>
            <div class="row" style="align-items:flex-start">
                <span class="row-label">ID transação</span>
                <span class="tx-id"><?php echo htmlspecialchars($transaction_id); ?></span>
            </div>
        </div>

    </div>

    <!-- BOTÃO ACESSAR -->
    <?php if ($acesso_valido): ?>
    <button class="btn-access" onclick="abrirPopup()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        ACESSAR CONTEÚDO AGORA
    </button>

    <?php else: ?>
    <div style="margin-top:24px;padding:18px 20px;background:#1a1a1a;border:1px solid rgba(255,255,255,0.08);border-radius:14px;text-align:center;font-size:14px;color:#666;line-height:1.6;">
        Este link de comprovante já foi utilizado ou é inválido.<br>
        <span style="color:#555;font-size:12px;">Se precisar de ajuda, entre em contato.</span>
    </div>
    <?php endif; ?>
    <a href="<?php echo SITE_URL; ?>" class="btn-back">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px"><polyline points="15 18 9 12 15 6"/></svg>
        Voltar ao início
    </a>

</div>

<?php if ($acesso_valido): ?>
<!-- POPUP CONFIRMAR ACESSO -->
<div class="popup" id="popup">
    <div class="popup-card">
        <div class="popup-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        </div>
        <h3>Acessar conteúdo?</h3>
        <p>Você será redirecionado para o conteúdo exclusivo. Guarde este comprovante antes de sair.</p>
        <button class="popup-btn-confirm" onclick="irParaConteudo()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Sim, acessar agora
        </button>
        <button class="popup-btn-cancel" onclick="fecharPopup()">Cancelar</button>
    </div>
</div>
<?php endif; ?>

<script>
    // Pixel Purchase
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {
            value: <?php echo floatval($valor); ?>,
            currency: 'BRL',
            transaction_id: '<?php echo addslashes($transaction_id); ?>',
            content_name: '<?php echo addslashes($planoNome); ?>'
        });
    }

    function abrirPopup() {
        document.getElementById('popup').classList.add('active');
    }

    function fecharPopup() {
        document.getElementById('popup').classList.remove('active');
    }

    function irParaConteudo() {
        window.location.href = 'redirect.php';
    }

    // Fechar popup clicando fora
    document.getElementById('popup').addEventListener('click', function(e) {
        if (e.target === this) fecharPopup();
    });

    // Anti-DevTools
    document.addEventListener('keydown', function(e) {
        if (
            e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && ['I','J','C'].includes(e.key.toUpperCase())) ||
            (e.ctrlKey && e.key.toUpperCase() === 'U')
        ) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    (function devToolsDetect() {
        var threshold = 160;
        setInterval(function() {
            if (
                window.outerWidth  - window.innerWidth  > threshold ||
                window.outerHeight - window.innerHeight > threshold
            ) {
                document.body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;font-size:1.1rem;color:#ea580c;text-align:center;padding:20px;">Acesso bloqueado.<br>Feche as ferramentas do desenvolvedor.</div>';
            }
        }, 1000);
    })();

    document.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'IMG' || e.target.tagName === 'VIDEO') {
            e.preventDefault();
        }
    });
</script>
</body>
</html>