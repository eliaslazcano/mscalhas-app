<?php
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET')); //,'POST','PUT','DELETE'
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $cheques = DbMscalhas::fastQuery("SELECT id, numcheque, cliente, banco, agencia, conta, servico, tipo, valor FROM cheques ORDER BY id DESC", array('id', 'servico', 'tipo', 'valor'));
  HttpHelper::emitirJson($cheques);
}