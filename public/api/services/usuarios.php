<?php
/*
 * GET    = Lista socios
 * POST   = Adiciona
 * PUT    = Atualiza
 * DELETE = Desativa/Reativa
 */
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../helper/EmailHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST','PUT','DELETE'));
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $socios = DbMscalhas::fastQuery("SELECT id, login, nome, email, desde, ativo FROM usuarios ORDER BY nome", array('id','ativo'));
  HttpHelper::emitirJson($socios);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $login = HttpHelper::validarParametro('login');
  $nome  = HttpHelper::validarParametro('nome');
  $email = HttpHelper::validarParametro('email');
  $senha = strval(mt_rand(1010, 9090));
  $query = "INSERT INTO usuarios (login, nome, email, senha) VALUES (:login, :nome, :email, :senha)";
  $db = new DbMscalhas();
  $statement = $db->getConn()->prepare($query);
  $statement->bindValue(':login', trim($login));
  $statement->bindValue(':nome' , StringHelper::toUpperCase($nome));
  $statement->bindValue(':email', trim($email));
  $statement->bindValue(':senha', md5($senha));
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
  $mail = new EmailHelper("Bem vindo ao sistema da MS Calhas LTDA", "Estas são suas credenciais de acesso:<br>Login: ".trim($login)."<br>Senha: ".$senha);
  $mail->addDestinatario($email, $nome);
  $mail->enviar();
}
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT')
{
  $id    = HttpHelper::validarParametro('id');
  $reset = HttpHelper::obterParametro('reset');
  if ($reset) {
    //Reset de senha
    $senha = strval(mt_rand(1010, 9090));
    $query = "UPDATE usuarios SET senha = :senha WHERE id = :id";
    $db = new DbMscalhas();
    $statement = $db->getConn()->prepare($query);
    $statement->bindValue(':senha', md5($senha));
    $statement->bindValue(':id', $id);
    if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
    if ($statement->rowCount() > 0) {
      $usuario = $db->query("SELECT nome, email FROM usuarios WHERE id = ".$id, array(), true);
      if ($usuario) {
        $mail = new EmailHelper("Sua senha foi resetada", "Sua nova senha de acesso: ".$senha);
        $mail->addDestinatario($usuario['email'], $usuario['nome']);
        $mail->enviar();
      }
    }
  } else {
    $login = HttpHelper::validarParametro('login');
    $nome  = HttpHelper::validarParametro('nome');
    $email = HttpHelper::validarParametro('email');
    $query = "UPDATE usuarios SET login = :login, nome = :nome, email = :email WHERE id = :id";
    $db = new DbMscalhas();
    $statement = $db->getConn()->prepare($query);
    $statement->bindValue(':login', trim($login));
    $statement->bindValue(':nome' , StringHelper::toUpperCase($nome));
    $statement->bindValue(':email', trim($email));
    $statement->bindValue(':id', $id);
    if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
  }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE')
{
  $id    = HttpHelper::validarParametro('id');
  $re    = HttpHelper::obterParametro('reativar');
  $query = "UPDATE usuarios SET ativo = :ativo WHERE id = :id";
  $db = new DbMscalhas();
  $statement = $db->getConn()->prepare($query);
  $statement->bindValue(':ativo', ($re === 'S') ? '1' : '0');
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falhas na base de dados');
}