<?php

require_once __DIR__.'/../database/DbMscalhas.php';
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';

HttpHelper::validarMetodos(['POST']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = HttpHelper::validarParametro('login');
  $senha = HttpHelper::validarParametro('senha');

  $query = "SELECT id, senha, ativo FROM usuarios WHERE login = :login";
  $db = new DbMscalhas();
  $statement = $db->conn->prepare($query);
  $statement->bindValue(':login', $login);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  $account = $statement->fetch(PDO::FETCH_ASSOC);
  if (!$account) HttpHelper::erroJson(400, "Não há conta de usuário com este nome", 1);
  if ($account['senha'] !== md5($senha)) HttpHelper::erroJson(400, "Senha incorreta.", 2);
  if (!intval($account['ativo'])) HttpHelper::erroJson(400, "Sua conta está desativada.", 3);

  $token = AuthHelper::newSession(intval($account['id']));
  HttpHelper::emitirJson($token);
}