<?php
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../helper/HttpHelper.php';

class DbMscalhas
{
  //==================== Configurações para conexão ====================//
  private $_host      = Config::DATABASE_HOST;      // IP ou Host do Banco de Dados
  private $_database  = Config::DATABASE_NAME;      // Base de Dados
  private $_usuario   = Config::DATABASE_USER;      // Usuario
  private $_senha     = Config::DATABASE_PASSWD;    // Senha
  private $_charset   = Config::DATABASE_CHARSET;   // Pode ser null
  private $_timezone  = Config::DATABASE_TIMEZONE;  // Pode ser null
  //====================================================================//

  public $conn;

  /**
   * DbMscalhas constructor.
   */
  public function __construct()
  {
    $this->conn = $this->getConn();
  }

  private function getConn(){
    try {
      $conn = new PDO('mysql:host='.$this->_host.';dbname='.$this->_database.($this->_charset ? ';charset='.$this->_charset : ''), $this->_usuario, $this->_senha);
      if ($this->_timezone) $conn->exec("SET time_zone='".$this->_timezone."';");
      return $conn;
    } catch (PDOException $e) {
      HttpHelper::erroJson(500, "Falha ao tentar conectar na base de dados", 0, $e->getMessage());
    }
  }

  /**
   * Faz uma consulta simples (SELECT).
   * @param string $sql
   * @param array $columnsNotNumber
   * @return array Linhas resultantes
   */
  public function query($sql, $columnsNotNumber = []) {
    $statement = $this->conn->query($sql);
    if (!$statement) HttpHelper::erroJson(500, "Falha ao consultar base de dados", 0, $statement->errorInfo());
    return $this->serializeNumericColumns($statement->fetchAll(PDO::FETCH_ASSOC), $columnsNotNumber);
  }

  /**
   * Adapta resultados do PDO, que colunas numéricas passem de string para INT|DOUBLE
   * @param array $lines
   * @param array $columnsNotNumber
   * @return array
   */
  public function serializeNumericColumns($lines, $columnsNotNumber = []) {
    foreach ($lines as $n => $linha) {
      foreach ($linha as $coluna => $v) {
        if (!in_array($coluna, $columnsNotNumber)) {
          if (is_numeric($v)) $lines[$n][$coluna] = $v + 0;
        }
      }
    }
    return $lines;
  }
}