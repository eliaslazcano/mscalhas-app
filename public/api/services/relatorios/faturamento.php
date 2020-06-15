<?php

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
  $query = "SELECT SUM(valor) total FROM pagamentos WHERE data_pagamento = current_date";
  $pagamentos = $db->query($query, array('total'), true);
  $faturamentoDiario = $pagamentos['total'];

  //Período Mensal
  $mesAtual = date('Y-m-');
  $query = "SELECT SUM(valor) total FROM pagamentos WHERE data_pagamento LIKE '$mesAtual%'";
  $pagamentos = $db->query($query, array('total'), true);
  $faturamentoMensal = $pagamentos['total'];

  //Periodo Anual
  $anoAtual = date('Y-');
  $query = "SELECT SUM(valor) total FROM pagamentos WHERE data_pagamento LIKE '$anoAtual%'";
  $pagamentos = $db->query($query, array('total'), true);
  $faturamentoAnual = $pagamentos['total'];

  //Periodo Total
  $query = "SELECT SUM(valor) total FROM pagamentos";
  $pagamentos = $db->query($query, array('total'), true);
  $faturamentoTotal = $pagamentos['total'];

  $dados = array(
    "d"  => round($faturamentoDiario, 2),
    "m"  => round($faturamentoMensal, 2),
    "a"  => round($faturamentoAnual, 2),
    "t" => round($faturamentoTotal, 2)
  );
  HttpHelper::emitirJson($dados);
}