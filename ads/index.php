<?php /* index_gate.php — página de entrada com captcha */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Plataforma Privacy - Eduarda</title>

<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1253561740209342');
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1253561740209342&ev=PageView&noscript=1"/>
</noscript>

<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
body{
  margin:0;
  font-family:Arial, sans-serif;
  background:#111;
  color:#fff;
  text-align:center;
}
.container{
  max-width:420px;
  margin:auto;
  padding:25px;
}
img{
  width:100%;
  border-radius:14px;
  margin-top:20px;
}
h1{
  font-size:26px;
  margin-top:20px;
}
p{
  font-size:18px;
  opacity:.9;
}

/* reCAPTCHA Container */
.captcha-container{
  display:flex;
  justify-content:center;
  margin:25px auto;
  transform:scale(0.95);
  transform-origin:center;
}

.button{
  display:block;
  margin:20px auto;
  padding:18px;
  background:#ff2d55;
  color:#fff;
  text-decoration:none;
  font-size:20px;
  border-radius:12px;
  font-weight:bold;
  box-shadow:0 8px 25px rgba(255,45,85,.4);
  border:none;
  cursor:pointer;
  width:100%;
  max-width:350px;
  transition:all 0.3s ease;
}

.button:disabled{
  background:#555;
  cursor:not-allowed;
  opacity:0.5;
  box-shadow:none;
}

.button:not(:disabled):hover{
  background:#ff1744;
  transform:translateY(-2px);
  box-shadow:0 12px 30px rgba(255,45,85,.5);
}

.button:not(:disabled):active{
  transform:translateY(0);
}

.verify-text{
  font-size:14px;
  color:#ff2d55;
  margin:10px 0;
  opacity:0;
  transition:opacity 0.3s;
}

.verify-text.show{
  opacity:1;
}

.footer{
  font-size:12px;
  opacity:.6;
  margin-top:40px;
}

/* Mobile adjustments */
@media (max-width: 480px) {
  .captcha-container{
    transform:scale(0.85);
  }
}

    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
      -webkit-box-shadow: 0 0 0px 1000px #111 inset !important;
      -webkit-text-fill-color: #fff !important;
      transition: background-color 5000s ease-in-out 0s;
    }
    * { -webkit-tap-highlight-color: transparent; }

</style>
</head>
<body>
<div class="container">
<h1>Privacy - Eduarda</h1>
<p>Plataforma de conteúdo exclusivo com acesso controlado e seguro.</p>
<img src="presell.jpg" alt="Privacy Eduarda">

<!-- reCAPTCHA -->
<div class="captcha-container">
  <div class="g-recaptcha" 
       data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"
       data-callback="enableButton"
       data-expired-callback="disableButton"></div>
</div>

<p class="verify-text" id="verify-text">⚠️ Marque a caixa acima para continuar</p>

<button class="button" id="access-button" onclick="redirectToProfile()" disabled>
ACESSAR PLATAFORMA
</button>

<p>Conteúdo destinado exclusivamente para maiores de 18 anos</p>

<div class="footer">
Privacy © 2026 • Todos os direitos reservados
</div>
</div>

<script>
// Função chamada quando reCAPTCHA é verificado
function enableButton() {
  const button = document.getElementById('access-button');
  const verifyText = document.getElementById('verify-text');
  
  button.disabled = false;
  verifyText.classList.remove('show');
  
  // Facebook Pixel - Lead Event
  if (typeof fbq !== 'undefined') {
    fbq('track', 'Lead', {
      content_name: 'Platform Access Verified',
      content_category: 'Security Verification'
    });
  }
  
  
}

// Função chamada quando reCAPTCHA expira
function disableButton() {
  const button = document.getElementById('access-button');
  const verifyText = document.getElementById('verify-text');
  
  button.disabled = true;
  verifyText.classList.add('show');
  
  
}

// Função de redirecionamento
function redirectToProfile() {
  var button = document.getElementById('access-button');
  if (button.disabled || button._carregando) return;
  button._carregando = true;

  if (typeof fbq !== 'undefined') {
    fbq('track', 'ViewContent', {
      content_name: 'Platform Access Initiated',
      content_category: 'Platform Entry'
    });
  }

  // Feedback visual durante os 5 segundos
  var textoOriginal = button.textContent;
  var contador = 5;
  button.textContent = 'AGUARDE ' + contador + 's...';
  button.style.background = '#333';
  button.style.boxShadow = 'none';
  button.style.cursor = 'not-allowed';

  var tick = setInterval(function() {
    contador--;
    if (contador > 0) {
      button.textContent = 'AGUARDE ' + contador + 's...';
    } else {
      clearInterval(tick);
      button.textContent = 'ENTRANDO...';
      // Redireciona para a raiz do site
      window.location.href = '/';
    }
  }, 1000);
}

// Mostrar mensagem se botão desabilitado for clicado
document.getElementById('access-button').addEventListener('click', function(e) {
  if (this.disabled) {
    e.preventDefault();
    const verifyText = document.getElementById('verify-text');
    verifyText.classList.add('show');
    
    // Piscar a mensagem
    setTimeout(() => {
      verifyText.style.transform = 'scale(1.1)';
      setTimeout(() => {
        verifyText.style.transform = 'scale(1)';
      }, 200);
    }, 50);
  }
});

// Inicialmente mostrar mensagem de verificação após 1 segundo
setTimeout(() => {
  const button = document.getElementById('access-button');
  if (button.disabled) {
    document.getElementById('verify-text').classList.add('show');
  }
}, 1000);
</script>
</body>
</html>