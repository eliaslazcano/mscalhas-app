<?php
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET')); //,'POST','PUT','DELETE'
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $servicos = DbMscalhas::fastQuery("SELECT s.id, s.socio_responsavel socio_responsavel_id, so.nome socio_responsavel_nome, s.valor, s.cliente_nome, s.data_criacao, s.data_finalizacao FROM servicos s LEFT JOIN socios so ON s.socio_responsavel = so.id ORDER BY s.id DESC", array('id', 'socio_responsavel_id', 'valor'));
  HttpHelper::emitirJson($servicos);
}