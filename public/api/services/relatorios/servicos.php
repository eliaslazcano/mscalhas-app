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
  $query = "SELECT count(id) total FROM servicos WHERE data_finalizacao = current_timestamp";
  $servicos = $db->query($query, array('total'), true);
  $servicosDiario = $servicos['total'];

  //Período Mensal
  $mesAtual = date('Y-m-');
  $query = "SELECT count(id) total FROM servicos WHERE data_finalizacao LIKE '$mesAtual%'";
  $servicos = $db->query($query, array('total'), true);
  $servicosMensal = $servicos['total'];

  //Periodo Anual
  $anoAtual = date('Y-');
  $query = "SELECT count(id) total FROM servicos WHERE data_finalizacao LIKE '$anoAtual%'";
  $servicos = $db->query($query, array('total'), true);
  $servicosAnual = $servicos['total'];

  //Periodo Total
  $query = "SELECT count(id) total FROM servicos WHERE data_finalizacao IS NOT NULL";
  $servicos = $db->query($query, array('total'), true);
  $servicosTotal = $servicos['total'];

  $dados = array(
    "d"  => round($servicosDiario, 2),
    "m"  => round($servicosMensal, 2),
    "a"  => round($servicosAnual, 2),
    "t" => round($servicosTotal, 2)
  );
  HttpHelper::emitirJson($dados);
}