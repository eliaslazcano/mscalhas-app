<?php
/*
 * GET  => Obtem os pagamentos de um serviço (requer serviço ID).
 * POST => Registra um pagamento, obtem seu novo ID.
 */

require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST'));
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $servico = HttpHelper::validarParametro('servico');
  $db      = new DbMscalhas();
  $query   = "SELECT * FROM pagamentos WHERE servico = :servico";
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
  $servico = HttpHelper::validarParametro('servico');

  //TODO - COLOCAR NESTA LINHA UM "IF" PARA VER SE É UM CHEQUE, ENTÃO ADICIONA-LO PARA OBTER O ID
  $cheque = null; //APAGAR

  $db    = new DbMscalhas();
  $query = "INSERT INTO pagamentos (tipo, valor, servico, cheque) VALUES (:tipo, :valor, :servico, :cheque)";
  $statement = $db->prepare($query);
  $statement->bindValue(':tipo',    $tipo);
  $statement->bindValue(':valor',   $valor);
  $statement->bindValue(':servico', $servico);
  $statement->bindValue(':cheque',  $cheque);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($db->getConn()->lastInsertId());
}