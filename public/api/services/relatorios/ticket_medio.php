<?php
/*
 * GET  = Periodo pre-definido (dia/mes/ano).
 * POST = Periodo especifico (data_inicio e data_fim).
 */

require_once __DIR__.'/../../helper/HttpHelper.php';
require_once __DIR__.'/../../helper/AuthHelper.php';
require_once __DIR__.'/../../helper/StringHelper.php';
require_once __DIR__.'/../../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET'));
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $db = new DbMscalhas();

  //Período Diário
  $query = "SELECT s.id, SUM(p.valor) pago FROM servicos s LEFT JOIN pagamentos p ON s.id = p.servico WHERE p.data_pagamento = current_date GROUP BY s.id";
  $servicos = $db->query($query, array('id','pago'));
  $total = array_reduce($servicos, function ($carry, $item) { return $carry + $item['pago']; }, 0);
  $ticketDiario = count($servicos) > 0 ? $total / count($servicos) : 0;

  //Período Mensal
  $mesAtual = date('Y-m-');
  $query = "SELECT s.id, SUM(p.valor) pago FROM servicos s LEFT JOIN pagamentos p ON s.id = p.servico WHERE p.data_pagamento LIKE '$mesAtual%' GROUP BY s.id";
  $servicos = $db->query($query, array('id','pago'));
  $total = array_reduce($servicos, function ($carry, $item) { return $carry + $item['pago']; }, 0);
  $ticketMensal = count($servicos) > 0 ? $total / count($servicos) : 0;

  //Periodo Anual
  $anoAtual = date('Y-');
  $query = "SELECT s.id, SUM(p.valor) pago FROM servicos s LEFT JOIN pagamentos p ON s.id = p.servico WHERE p.data_pagamento LIKE '$anoAtual%' GROUP BY s.id";
  $servicos = $db->query($query, array('id','pago'));
  $total = array_reduce($servicos, function ($carry, $item) { return $carry + $item['pago']; }, 0);
  $ticketAnual = count($servicos) > 0 ? $total / count($servicos) : 0;

  //Periodo Total
  $query = "SELECT s.id, SUM(p.valor) pago FROM servicos s LEFT JOIN pagamentos p ON s.id = p.servico GROUP BY s.id";
  $servicos = $db->query($query, array('id','pago'));
  $total = array_reduce($servicos, function ($carry, $item) { return $carry + $item['pago']; }, 0);
  $ticketTotal = count($servicos) > 0 ? $total / count($servicos) : 0;

  $dados = array(
    "d"  => round($ticketDiario, 2),
    "m"  => round($ticketMensal, 2),
    "a"  => round($ticketAnual, 2),
    "t" => round($ticketTotal, 2)
  );
  HttpHelper::emitirJson($dados);
}