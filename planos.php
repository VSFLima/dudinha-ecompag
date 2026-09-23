<?php
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
?>
// ============================================================
//  CONFIGURAÇÃO DOS PLANOS — edite aqui para mudar preços
//
//  nome  → texto exibido no botão e no modal
//  preco → valor em reais (use ponto como separador decimal)
//
//  bonus → true  = exibe o badge "BÔNUS EXCLUSIVO HOJE"
//          false = não exibe o badge
// ============================================================
var _P = {
  mensal:     { nome: "30 DIAS",   preco: 32.99,  bonus: true  },
  semanal:    { nome: "7 Dias",    preco: 19.90,  bonus: false },
  trimestral: { nome: "3 Meses",   preco: 121.00, bonus: false },
  anual:      { nome: "1 ANO",     preco: 299.00, bonus: false },
  vitalicio:  { nome: "VITALICIO", preco: 525.50, bonus: false },

  // ── Notificações de bots ─────────────────────────────────────
  bots: {
    ativo: true,        // true = ativa as notificações | false = desativa
    intervalo_min: 3,   // segundos mínimos entre notificações
    intervalo_max: 8,  // segundos máximos entre notificações
    duracao: 6          // segundos que a notificação fica visível
  },

  // ── Página de desconto (desconto/index.html) ──────────────
  desconto: {
    preco:      9.90,   // preço promocional exibido
    precoAntes: 29.90,  // riscado "De R$ XX,XX"
    label:     "APENAS HOJE"
  }
};