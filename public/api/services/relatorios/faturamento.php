<?php
/*
 * GET  = Faturamento resumido em dia/mes/ano/total
 * POST  = Faturamento anual de Jan á Dez. Informando o ano.
 */
require_once __DIR__.'/../../helper/HttpHelper.php';
require_once __DIR__.'/../../helper/AuthHelper.php';
require_once __DIR__.'/../../helper/StringHelper.php';
require_once __DIR__.'/../../database/DbMscalhas.php';
//TODO => Cheques são pagamentos do tipo 4, e o valor não é armazenado na tabela "pagamentos". Favor buscar na tabela "cheques".
HttpHelper::validarMetodos(array('GET', 'POST'));
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
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $ano = HttpHelper::validarParametro('ano');
  $db = new DbMscalhas();
  $faturamento = array();

  //Metodo 1 - Desempenho depende do PHP:
  $query = "SELECT valor, data_pagamento FROM pagamentos WHERE data_pagamento LIKE '$ano-__-__'";
  $dados = $db->query($query, array('valor'));
  for ($i = 1; $i <= 12; $i++) {
    $mes = $i < 10 ? '0'.strval($i) : strval($i);
    $dadosMes = array_filter($dados, function ($item) use ($ano, $mes) { return StringHelper::startsWith($item['data_pagamento'], $ano.'-'.$mes); });
    $total = array_reduce($dadosMes, function ($carry, $item) { return $carry + $item['valor']; }, 0);
    $faturamento[$i-1] = $total;
  }

  //Metodo 2 - Desempenho depende do banco:
//  for ($i = 1; $i <= 12; $i++) {
//    $mes = $i < 10 ? '0'.strval($i) : strval($i);
//    $query = "SELECT COALESCE(SUM(valor), 0) total FROM pagamentos WHERE data_pagamento LIKE '$ano-$mes-__'";
//    $dados = $db->query($query, array('total'), true);
//    $faturamento[$i-1] = $dados['total'];
//  }

  HttpHelper::emitirJson($faturamento);
}