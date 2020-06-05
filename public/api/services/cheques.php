<?php
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST')); //,'POST','PUT','DELETE'
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $cheques = DbMscalhas::fastQuery("SELECT id, numcheque, cliente, banco, agencia, conta, servico, tipo, valor, data_cheque, data_compensado, IF(data_compensado IS NULL, DATEDIFF(data_cheque, CURRENT_DATE), null) AS dias_restantes FROM cheques ORDER BY id DESC", array('id', 'servico', 'tipo', 'valor', 'dias_restantes'));
  HttpHelper::emitirJson($cheques);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $cliente = HttpHelper::validarParametro('cliente');
  $banco   = HttpHelper::obterParametro('banco');
  $agencia = HttpHelper::obterParametro('agencia');
  $conta   = HttpHelper::obterParametro('conta');
  $tipo    = HttpHelper::validarParametro('tipo');
  $numero  = HttpHelper::obterParametro('numero');
  $valor   = HttpHelper::validarParametro('valor');
  $data    = HttpHelper::validarParametro('data');
  $servico = HttpHelper::obterParametro('servico');

  $db = new DbMscalhas();
  $query = "INSERT INTO cheques (numcheque, cliente, banco, agencia, conta, servico, tipo, valor, data_cheque) VALUES (:numero, :cliente, :banco, :agencia, :conta, :servico, :tipo, :valor, :data)";
  $statement = $db->getConn()->prepare($query);
  $statement->bindValue(':numero', $numero);
  $statement->bindValue(':cliente', $cliente);
  $statement->bindValue(':banco', $banco);
  $statement->bindValue(':agencia', $agencia);
  $statement->bindValue(':conta', $conta);
  $statement->bindValue(':servico', $servico);
  $statement->bindValue(':tipo', $tipo);
  $statement->bindValue(':valor', $valor);
  $statement->bindValue(':data', $data);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($db->getConn()->lastInsertId());
}