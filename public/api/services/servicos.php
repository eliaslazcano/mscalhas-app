<?php
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET')); //,'POST','PUT','DELETE'
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $servicos = DbMscalhas::fastQuery("SELECT se.id, se.status, se.socio_responsavel socio_responsavel_id, so.nome socio_responsavel_nome, se.valor, se.cliente_nome, se.data_criacao FROM servicos se LEFT JOIN socios so ON se.socio_responsavel = so.id ORDER BY se.id DESC", array('id', 'status', 'socio_responsavel_id', 'valor'));
  HttpHelper::emitirJson($servicos);
}