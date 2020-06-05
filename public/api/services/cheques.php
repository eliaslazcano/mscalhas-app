<?php
/*
 * GET  - Listagem
 * POST - Insere
 * PUT  - Atualiza
 * DELETE - Apaga
 */
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST','PUT','DELETE'));
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
  $numero  = HttpHelper::obterParametro('numcheque');
  $valor   = HttpHelper::validarParametro('valor');
  $data    = HttpHelper::validarParametro('data_cheque');
  $servico = HttpHelper::obterParametro('servico');

  $db = new DbMscalhas();
  $query = "INSERT INTO cheques (numcheque, cliente, banco, agencia, conta, servico, tipo, valor, data_cheque) VALUES (:numero, :cliente, :banco, :agencia, :conta, :servico, :tipo, :valor, :data)";
  $statement = $db->prepare($query);
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
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $id         = HttpHelper::validarParametro('id');
  $cliente    = HttpHelper::obterParametro('cliente');
  $banco      = HttpHelper::obterParametro('banco');
  $agencia    = HttpHelper::obterParametro('agencia');
  $conta      = HttpHelper::obterParametro('conta');
  $tipo       = HttpHelper::obterParametro('tipo');
  $numero     = HttpHelper::obterParametro('numcheque');
  $valor      = HttpHelper::obterParametro('valor');
  $data       = HttpHelper::obterParametro('data_cheque');
  $compensado = HttpHelper::obterParametro('data_compensado');
  $servico    = HttpHelper::obterParametro('servico');

  $db = new DbMscalhas();
  $statement = $db->prepare("SELECT id, cliente, banco, agencia, conta, tipo, numcheque, valor, data_cheque, data_compensado, servico FROM cheques WHERE id = :id");
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  $cheque = $statement->fetch(PDO::FETCH_ASSOC);
  if (!$cheque) HttpHelper::erroJson(400, "Cheque não encontrado no servidor", 1);

  $query = "UPDATE cheques SET cliente = :cliente, banco = :banco, agencia = :agencia, conta = :conta, tipo = :tipo, numcheque = :numcheque, valor = :valor, data_cheque = :data_cheque, data_compensado = :data_compensado, servico = :servico WHERE id = :id";
  $statement = $db->prepare($query);
  $statement->bindValue(':cliente',   $cliente ? $cliente : $cheque['cliente']);
  $statement->bindValue(':banco',     $banco ? $banco : $cheque['banco']);
  $statement->bindValue(':agencia',   $agencia ? $agencia : $cheque['agencia']);
  $statement->bindValue(':conta',     $conta ? $conta : $cheque['conta']);
  $statement->bindValue(':tipo',      $tipo ? $tipo : $cheque['tipo']);
  $statement->bindValue(':numcheque', $numero ? $numero : $cheque['numcheque']);
  $statement->bindValue(':valor',     $valor ? $valor : $cheque['valor']);
  $statement->bindValue(':data_cheque',     $data ? $data : $cheque['data_cheque']);
  $statement->bindValue(':data_compensado', $compensado ? $compensado : $cheque['data_compensado']);
  $statement->bindValue(':servico',         $servico ? $servico : $cheque['servico']);
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 2, $statement->errorInfo());
  HttpHelper::emitirJson($statement->rowCount() > 0);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE')
{
  $id = HttpHelper::validarParametro('id');
  $db = new DbMscalhas();
  $query = "DELETE FROM cheques WHERE id = :id";
  $statement = $db->prepare($query);
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($statement->rowCount() > 0);
}
