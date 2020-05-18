<?php
/*
 * v1.2 2020-02-25
 * Sem dependencias.
 * Oferece metodos estaticos que facilitam tratar e responder a requisicao HTTP.
 */

class HttpHelper
{
  /**
   * Produz uma resposta HTTP padronizada pra quando ocorrer falhas na sua aplicacao.
   * @param int $codigoHttp
   * @param int $erroId
   * @param string $mensagem
   * @param string $dadosExtras
   */
  public static function erroJson($codigoHttp = 400, $mensagem = '', $erroId = 1, $dadosExtras = '')
  {
    if (function_exists("http_response_code")) http_response_code($codigoHttp);
    else header("HTTP/1.1 $codigoHttp", true, $codigoHttp);
    header('Content-Type: application/json; charset=utf-8', true);
    echo json_encode(array(
      "mensagem" => $mensagem,
      "erro" => $erroId,
      "dados" => $dadosExtras
    ));
    die();
  }

  /**
   * Confere se a requisição atendida é do tipo POST, caso contrário mata o script e responde HTTP 405.
   * @param bool $matarRequisicao A validacao mata o script PHP em caso de falhas. Ou então informa TRUE/FALSE.
   * @param string $allowOrigin Origens aceitas, separadas por virgula.
   * @param string $allowHeaders Cabecalhos aceitos, separados por virgula.
   * @return bool Se $matarRequisicao = false, o retorno informa se a validacao deu certo com TRUE/FALSE.
   */
  public static function validarPost($matarRequisicao = true, $allowOrigin = "*" ,$allowHeaders = "Authorization, Content-Type")
  {
    header("Access-Control-Allow-Origin: $allowOrigin", true);
    header('Access-Control-Allow-Methods: POST, OPTIONS', true);
    header("Access-Control-Allow-Headers: $allowHeaders", true);

    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo != 'POST' && $metodo != 'OPTIONS') {
      if ($matarRequisicao) self::erroJson(405, "Método não permitido");
      else return false;
    } elseif ($metodo == 'OPTIONS') {
      die();
    }
    return true;
  }

  /**
   * Confere se a requisição atendida é do tipo POST, caso contrário mata o script e responde HTTP 405.
   * @param bool $matarRequisicao A validacao mata o script PHP em caso de falhas. Ou então informa TRUE/FALSE.
   * @param string $allowOrigin Origens aceitas, separadas por virgula.
   * @param string $allowHeaders Cabecalhos aceitos, separados por virgula.
   * @return bool Se $matarRequisicao = false, o retorno informa se a validacao deu certo com TRUE/FALSE.
   */
  public static function validarGet($matarRequisicao = true, $allowOrigin = "*" ,$allowHeaders = "Authorization, Content-Type")
  {
    header("Access-Control-Allow-Origin: $allowOrigin", true);
    header('Access-Control-Allow-Methods: GET, OPTIONS', true);
    header("Access-Control-Allow-Headers: $allowHeaders", true);

    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo != 'GET' && $metodo != 'OPTIONS') {
      if ($matarRequisicao) self::erroJson(405, "Método não permitido");
      else return false;
    } elseif ($metodo == 'OPTIONS') {
      die();
    }
    return true;
  }

  /**
   * Confere se a requisição é do metodo especificado, caso contrário mata o script e responde HTTP 405. OPTIONS é validado.
   * @param string $metodo Metodo HTTP.
   * @param bool $matarRequisicao A validacao mata o script PHP em caso de falhas. Ou então informa TRUE/FALSE.
   * @param string $allowOrigin Origens aceitas, separadas por virgula.
   * @param string $allowHeaders Cabecalhos aceitos, separados por virgula.
   * @return bool Se $matarRequisicao = false, o retorno informa se a validacao deu certo com TRUE/FALSE.
   */
  public static function validarMetodo($metodo, $matarRequisicao = true, $allowOrigin = "*" , $allowHeaders = "Authorization, Content-Type")
  {
    $metodo = strtoupper($metodo);
    header("Access-Control-Allow-Origin: $allowOrigin", true);
    header("Access-Control-Allow-Methods: $metodo, OPTIONS", true);
    header("Access-Control-Allow-Headers: $allowHeaders", true);

    $metodoOriginal = $_SERVER['REQUEST_METHOD'];

    if ($metodoOriginal != $metodo && $metodoOriginal != 'OPTIONS') {
      if ($matarRequisicao) self::erroJson(405, "O método não permitido");
      else return false;
    } elseif ($metodoOriginal == 'OPTIONS') {
      die();
    }
    return true;
  }

  /**
   * @param array $metodos Array de string, com o nome dos metodos desejaveis para validar. OPTIONS é autoincluido.
   * @param bool $matarRequisicao A validacao mata o script PHP em caso de falhas. Ou então informa TRUE/FALSE.
   * @param string $allowOrigin Origens aceitas, separadas por virgula.
   * @param string $allowHeaders Cabecalhos aceitos, separados por virgula.
   * @return bool Se $matarRequisicao = false, o retorno informa se a validacao deu certo com TRUE/FALSE.
   */
  public static function validarMetodos($metodos = ['GET', 'POST', 'PUT', 'DELETE'], $matarRequisicao = true, $allowOrigin = "*" , $allowHeaders = "Authorization, Content-Type")
  {
    if (gettype($metodos) !== 'array') self::erroJson(400, "validarMetodos() precisa receber um array de string no primeiro parametro");
    if (count($metodos) === 0) self::erroJson(400, "validarMetodos() precisa receber ao menos 1 metodo http no array do primeiro parametro");

    $metodos = array_map(function ($metodo) { return strtoupper($metodo); }, $metodos); //Passa para caixa alta.
    $metodosString = count($metodos) > 1 ? implode(", ", $metodos) : $metodos[0]; //Une em string separado por virgula.

    header("Access-Control-Allow-Origin: $allowOrigin", true);
    header("Access-Control-Allow-Methods: $metodosString, OPTIONS", true);
    header("Access-Control-Allow-Headers: $allowHeaders", true);

    $metodoOriginal = $_SERVER['REQUEST_METHOD'];

    if (!in_array($metodoOriginal, $metodos) && $metodoOriginal != 'OPTIONS') {
      if ($matarRequisicao) self::erroJson(405, "O método não permitido");
      else return false;
    } elseif ($metodoOriginal == 'OPTIONS') {
      die();
    }
    return true;
  }

  /**
   * Responde a requisição com um JSON a partir da váriavel que você fornecer. Esta função encerra o script.
   * @param $dados
   * @param string $charset Charset da notacao JSON
   */
  public static function emitirJson($dados, $charset = "utf-8")
  {
    header('Content-Type: application/json; charset=utf-8', true);
    echo json_encode($dados);
    die();
  }

  /**
   * Emite o código HTTP da resposta
   * @param int $codigoHttp
   * @param bool $matarScript
   */
  public static function emitirHttp($codigoHttp, $matarScript = false)
  {
    if (function_exists("http_response_code")) http_response_code($codigoHttp);
    else header("HTTP/1.1 $codigoHttp", true, $codigoHttp);
    if ($matarScript) die();
  }

  /**
   * Verifica se a requisição contem um dado com o nome especificado no primeiro param.
   * Se o dado não existir, seu script é encerrado e a resposta HTTP 400 será retornada.
   * Se o dado existir, ele sera retornado
   * @param string $nome
   * @param string $mensagemErro
   * @return mixed|null
   */
  public static function validarParametro($nome, $mensagemErro = "Faltam dados na requisição")
  {
    switch ($_SERVER['REQUEST_METHOD']) {
      case "DELETE":
      case "GET":
        if (!isset($_GET[$nome])) self::erroJson(400, $mensagemErro, 0, $nome);
        else return $_GET[$nome];
        break;
      case "POST":
        if (!isset($_POST[$nome]) && !isset($_FILES[$nome])) self::erroJson(400, $mensagemErro, 0, $nome);
        else return (isset($_FILES[$nome])) ? $_FILES[$nome] : $_POST[$nome];
        break;
      case "PUT":
        $dados = self::getFormData();
        if (!isset($dados[$nome]) && !isset($_FILES[$nome])) self::erroJson(400, $mensagemErro, 0, $nome);
        else return (isset($_FILES[$nome])) ? $_FILES[$nome] : $dados[$nome];
    }
    return null;
  }

  /**
   * Verifica se a requisição contem os dados com os nomes especificados no primeiro param.
   * Se o dado não existir, seu script é encerrado e a resposta HTTP 400 será retornada.
   * @param array $nomes
   * @param string $mensagemErro
   */
  public static function validarParametros($nomes, $mensagemErro = "Faltam dados na requisição")
  {
    $valido = true;
    switch ($_SERVER['REQUEST_METHOD']) {
      case "DELETE":
      case "GET":
        foreach ($nomes as $nome) {
          $valido = (isset($_GET[$nome])) ? $valido : false;
        }
        break;
      case "POST":
        foreach ($nomes as $nome) {
          $valido = (isset($_POST[$nome]) || isset($_FILES[$nome])) ? $valido : false;
        }
        break;
      case "PUT":
        $dados = self::getFormData();
        foreach ($nomes as $nome) {
          $valido = (isset($dados[$nome]) || isset($_FILES[$nome])) ? $valido : false;
        }
    }
    if (!$valido) self::erroJson(400, $mensagemErro, 0, $nomes);
  }

  /**
   * Obtem um dado que esta contido na requisicao, se ele nao existir obterá null
   * @param string $nome
   * @return mixed|null
   */
  public static function obterParametro($nome)
  {
    switch ($_SERVER['REQUEST_METHOD']) {
      case "DELETE":
      case "GET":
        if (!isset($_GET[$nome])) return null;
        else return $_GET[$nome];
        break;
      case "POST":
        if (!isset($_POST[$nome]) && !isset($_FILES[$nome])) return null;
        else return (isset($_FILES[$nome])) ? $_FILES[$nome] : $_POST[$nome];
        break;
      case "PUT":
        $dados = self::getFormData();
        if (!isset($dados[$nome]) && !isset($_FILES[$nome])) return null;
        else return (isset($_FILES[$nome])) ? $_FILES[$nome] : $dados[$nome];
        break;
      default:
        return null;
    }
  }

  /**
   * Obtem o IP Publico do cliente, se ele estiver na Rede Local retorna seu IP Local.
   * @return string
   */
  public static function obterIp()
  {
    return $_SERVER['REMOTE_ADDR'];
  }

  /**
   * Tenta obter o valor de um cabecalho da requisicao (header)
   * @param string $cabecalho nome do cabecalho
   * @return string
   */
  public static function obterCabecalho($cabecalho)
  {
    if (isset($_SERVER['HTTP_'.mb_strtoupper($cabecalho,'UTF-8')])) return trim($_SERVER['HTTP_'.mb_strtoupper($cabecalho,'UTF-8')]);
    elseif (function_exists('apache_request_headers')) {
      $request_headers = apache_request_headers();
      $request_headers = array_combine(array_map('ucwords', array_keys($request_headers)), array_values($request_headers));
      if (isset($request_headers[$cabecalho])) return trim($request_headers[$cabecalho]);
      else return null;
    }
    elseif (isset($_SERVER[$cabecalho])) return trim($_SERVER[$cabecalho]);
    return null;
  }

  /**
   * Obtem os dados trafegados via FormData, util quando o metodo não é POST ou GET, pois o PHP não trata dados de outros metodos.
   * @return array Dados parseados em array com indice = key do FormData.
   */
  private static function getFormData()
  {
    $dados = array();
    $raw_data = file_get_contents('php://input');
    $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));
    if (!$boundary) return $dados;

    $parts = array_slice(explode($boundary, $raw_data), 1);

    foreach ($parts as $part) {
      if ($part == "--\r\n") break;

      $part = ltrim($part, "\r\n");
      list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

      $raw_headers = explode("\r\n", $raw_headers);
      $headers = array();
      foreach ($raw_headers as $header) {
        list($name, $value) = explode(':', $header);
        $headers[strtolower($name)] = ltrim($value, ' ');
      }

      if (isset($headers['content-disposition'])) {
        $filename = null;
        preg_match(
          '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
          $headers['content-disposition'],
          $matches
        );
        list(, $type, $name) = $matches;
        isset($matches[4]) and $filename = $matches[4];

        switch ($name) {
          case 'userfile':
            file_put_contents($filename, $body);
            break;
          default:
            $dados[$name] = substr($body, 0, strlen($body) - 2);
            break;
        }
      }
    }
    return $dados;
  }

  public static function var_dump($var, $matar_script = false)
  {
    echo "<pre>\n";
    var_dump($var);
    echo "</pre>\n";
    if ($matar_script) die();
  }
}
