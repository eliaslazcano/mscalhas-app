<?php
/*
 * GET    = Lista socios
 * POST   = Adiciona
 * PUT    = Renomeia
 * DELETE = Remove
 */
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST','PUT','DELETE'));
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $socios = DbMscalhas::fastQuery("SELECT id, nome FROM socios WHERE ativo = 1 ORDER BY nome", array('id'));
  HttpHelper::emitirJson($socios);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $nome = HttpHelper::validarParametro('nome');
  $query = "INSERT INTO socios (nome) VALUES (:nome)";
  $db = new DbMscalhas();
  $statement = $db->prepare($query);
  $statement->bindValue(':nome', StringHelper::toUpperCase($nome));
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
}
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT')
{
  $id   = HttpHelper::validarParametro('id');
  $nome = HttpHelper::validarParametro('nome');
  $query = "UPDATE socios SET nome = :nome WHERE id = :id";
  $db = new DbMscalhas();
  $statement = $db->prepare($query);
  $statement->bindValue(':nome', StringHelper::toUpperCase($nome));
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE')
{
  $id    = HttpHelper::validarParametro('id');
  $query = "UPDATE socios SET ativo = 0 WHERE id = :id";
  $db = new DbMscalhas();
  $statement = $db->prepare($query);
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
}