# 💳 Dudinha Ecompag

> **Checkout de pagamento PIX** integrado ao gateway Ecompag — página de planos, QR Code e confirmação via webhook.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PIX](https://img.shields.io/badge/PIX-BRL-00A859?style=for-the-badge)
![Status](https://img.shields.io/badge/status-privado-6e5494?style=for-the-badge)

---

## 🚀 Visão Geral

Landing de **pagamento/recebimento via PIX** com o gateway **Ecompag**: o
visitante escolhe um plano, gera o QR Code PIX, e o sistema confirma o
pagamento em tempo real (consulta + webhook). Inclui pixels de rastreamento,
scripts de desconto e automação de mensagens.

## ✨ Funcionalidades

| Módulo | Descrição |
|---|---|
| 💳 **Checkout PIX** | `checkout.html` + `api-pix.php` (QR Code) |
| 🔄 **Confirmação** | `check-payment.php` e `webhook.php` |
| 📦 **Planos** | Página de planos/assinaturas (`planos.php`) |
| 🎯 **Pixel/Tracking** | `pixel.js`, `conteudo.js` |
| 🤖 **Bots** | `bots.json` (automação) |
| 🔀 **Redirect** | `redirect.php` + páginas 403/404/500 |

## 🛠️ Stack

- **Backend:** PHP (`api-pix.php`, `vendor/`)
- **Frontend:** HTML/CSS/JS
- **Pagamento:** gateway Ecompag (PIX)

## ⚙️ Configuração

1. Apontar credenciais Ecompag em `config.php`.

---

> 🔒 **Repositório privado.**