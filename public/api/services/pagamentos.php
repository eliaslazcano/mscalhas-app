<?php
/*
 * GET  => Obtem os pagamentos de um serviço (requer serviço ID).
 * POST => Registra um pagamento, obtem seu novo ID.
 */

require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST','DELETE'));
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $servico = HttpHelper::validarParametro('servico');
  $db      = new DbMscalhas();
  $query   = "SELECT id, tipo, valor, parcelas, cheque, data_registro, data_pagamento FROM pagamentos WHERE servico = :servico";
  $statement = $db->prepare($query);
  $statement->bindValue(':servico', $servico);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  $pagamentos = $statement->fetchAll(PDO::FETCH_ASSOC);
  $pagamentos = $db->serializeNumericColumns($pagamentos, array('id', 'tipo', 'valor', 'servico', 'cheque'));
  HttpHelper::emitirJson($pagamentos);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $tipo    = HttpHelper::validarParametro('tipo');
  $valor   = HttpHelper::validarParametro('valor');
  $parcelas= HttpHelper::obterParametro('parcelas');
  $data    = HttpHelper::validarParametro('data_pagamento');
  $servico = HttpHelper::validarParametro('servico');

  //TODO - COLOCAR NESTA LINHA UM "IF" PARA VER SE É UM CHEQUE, ENTÃO ADICIONA-LO PARA OBTER O ID, QUE É NECESSÁRIO ADIANTE.
  $cheque = null; //APAGAR

  $db    = new DbMscalhas();
  $query = "INSERT INTO pagamentos (tipo, valor, parcelas, servico, cheque, data_pagamento) VALUES (:tipo, :valor, :parcelas, :servico, :cheque, :data_pagamento)";
  $statement = $db->prepare($query);
  $statement->bindValue(':tipo',    $tipo);
  $statement->bindValue(':valor',   $valor);
  $statement->bindValue(':parcelas',$parcelas);
  $statement->bindValue(':servico', $servico);
  $statement->bindValue(':cheque',  $cheque);
  $statement->bindValue(':data_pagamento', $data);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($db->getConn()->lastInsertId());
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE')
{
  $id = HttpHelper::validarParametro('id');
  $db = new DbMscalhas();
  $query = "DELETE FROM pagamentos WHERE id = :id";
  $statement = $db->prepare($query);
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($statement->rowCount() > 0);
}