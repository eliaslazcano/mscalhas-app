<?php
require_once __DIR__ . '/HttpHelper.php';
require_once __DIR__ . '/JwtHelper.php';
require_once __DIR__ . '/StringHelper.php';
require_once __DIR__ . '/../database/DbMscalhas.php';

class AuthHelper
{
  /**
   * Registra uma sessão de login no banco de dados, obtem o token JWT.
   * @param int $userId
   * @param bool $emitError
   * @return bool|string
   */
  public static function newSession(int $userId, bool $emitError = true)
  {
    //Get the token data
    $db = new DbMscalhas();
    $statement = $db->getConn()->prepare("SELECT id, login, senha, nome FROM usuarios WHERE id = :id");
    $statement->bindValue(':id', $userId, PDO::PARAM_INT);
    if (!$statement->execute()) {
      if ($emitError) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
      else return false;
    }
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
      if ($emitError) HttpHelper::erroJson(400, "Usuário não encontrado.", 1, $statement->errorInfo());
      else return false;
    }

    //Token construction
    $key = md5(uniqid(mt_rand() . mt_rand(), true), false);
    $loginTime = date('Y-m-d H:i:s');
    $data = array(
      "id" => $userId,
      "loginTime" => $loginTime,
      "ip" => HttpHelper::obterIp(),
      "name" => $user['nome']
    );
    $token = new JwtHelper($key, $data);

    //Token registry
    $statement = $db->getConn()->prepare("INSERT INTO sessoes (chave, usuario, ip, datahora) VALUES (:chave, :usuario, :ip, :datahora)");
    $statement->bindValue(':chave', $key);
    $statement->bindValue(':usuario', $userId, PDO::PARAM_INT);
    $statement->bindValue(':ip', HttpHelper::obterIp());
    $statement->bindValue(':datahora', $loginTime);
    if (!$statement->execute()) {
      if ($emitError) HttpHelper::erroJson(500, "Falha na base de dados", 2, $statement->errorInfo());
      else return false;
    }
    return $token->getToken();
  }

  /**
   * Através de um token JWT, busca validar a sessão do usuário no sistema. Se nenhum token for passado, será buscado no Header "Authorization"
   * @param bool $emitError
   * @param string|null $token Token JWT, obtido quando o usuário logou
   * @param bool $lastSessionOnly
   * @return bool|null true = autenticado, false = não está autenticado, null = erro
   */
  public static function sessionValidate(bool $emitError = true, string $token = null, bool $lastSessionOnly = true)
  {
    //Get token
    if (!$token) $token = HttpHelper::obterCabecalho('Authorization');
    if (!$token) {
      if ($emitError) HttpHelper::erroJson(401, "Não autorizado", 0);
      else return null;
    }
    $token = StringHelper::startsWith($token, 'Bearer ', true) ? substr($token, 7) : trim($token);

    //Validate token payload
    $data = JwtHelper::obterDados($token);
    if (!property_exists($data, 'id') || !property_exists($data, 'loginTime')) {
      if ($emitError) HttpHelper::erroJson(403, "Seu acesso não é autêntico", 1);
      else return null;
    }

    //Validate token session
    $query = "SELECT chave FROM sessoes WHERE usuario = :id AND datahora = :datahora LIMIT 1";
    $db = new DbMscalhas();
    $statement = $db->getConn()->prepare($query);
    $statement->bindValue(':id', $data->id, PDO::PARAM_INT);
    $statement->bindValue(':datahora', $data->loginTime);
    if (!$statement->execute()) {
      if ($emitError) HttpHelper::erroJson(500, "Falha na base de dados", 2);
      else return null;
    }
    $access = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$access) {
      if ($emitError) HttpHelper::erroJson(403, "O sistema não encontrou sua sessão de acesso", 3);
      else return null;
    }
    $authorized = JwtHelper::validarToken($token, $access['chave']);
    if (!$authorized && $emitError) HttpHelper::erroJson(401, "Acesso ilegal", 4);

    //If last token emitted
    if ($authorized && $lastSessionOnly) {
      $query = "SELECT chave FROM sessoes WHERE usuario = :id ORDER BY id DESC LIMIT 1";
      $statement = $db->getConn()->prepare($query);
      $statement->bindValue(':id', $data->id, PDO::PARAM_INT);
      if (!$statement->execute()) {
        if ($emitError) HttpHelper::erroJson(500, "Falha na base de dados", 5);
        else return null;
      }
      $access = $statement->fetch(PDO::FETCH_ASSOC);
      if (!$access) { //Teoricamente é impossivel disparar este IF, devido a validação ocorrida anteriormente. Precaução.
        if ($emitError) HttpHelper::erroJson(500, "O sistema não reconhece seu acesso", 6);
        else return null;
      }
      $authorized = JwtHelper::validarToken($token, $access['chave']);
      if (!$authorized && $emitError) HttpHelper::erroJson(401, "Seu login expirou", 7);
    }

    return $authorized;
  }
}
