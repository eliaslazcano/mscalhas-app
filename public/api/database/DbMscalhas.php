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

  private $conn;

  /**
   * Obtem a conexão usada com o banco de dados
   * @return PDO
   */
  public function getConn(): PDO
  {
    return $this->conn;
  }

  /**
   * DbMscalhas constructor.
   */
  public function __construct()
  {
    $this->conn = $this->createConn();
  }

  private function createConn(){
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
   * @param string $sql A query da consulta.
   * @param array $numericColumns Os nomes das colunas que a serem convertidas para um resultado númerico (INT|FLOAT). Um array vazio tentará converter todas.
   * @param bool $returnFirstLine Se verdadeiro, irá retornar a primeira linha do resultado, senão NULL.
   * @return array Linhas resultantes
   */
  public function query($sql, $numericColumns = array(), $returnFirstLine = false) {
    $statement = $this->conn->query($sql);
    if (!$statement) HttpHelper::erroJson(500, "Falha ao consultar base de dados", 0, $statement->errorInfo());
    $result = $this->serializeNumericColumns($statement->fetchAll(PDO::FETCH_ASSOC), $numericColumns);
    if ($returnFirstLine) return (count($result) > 0) ? $result[0] : null;
    else return $result;
  }

  /**
   * Realiza um PREPARE do PDO, retornando o Statement.
   * @param $sql
   * @return bool|PDOStatement
   */
  public function prepare($sql): PDOStatement
  {
    return $this->conn->prepare($sql);
  }

  /**
   * Executa uma instrução SQL e retornar o número de linhas afetadas. Ideal para um rápido INSERT, UPDATE ou DELETE.
   * @param $sql
   * @return false|int
   */
  public function exec($sql)
  {
    return $this->conn->exec($sql);
  }

    /**
     * Adapta resultados do PDO, que colunas numéricas passem de string para INT|DOUBLE. O conversor irá ignorar strings que não contenham dígito numérico.
     * @param array $lines Matriz de $matriz[nLinha][nomeColuna]
     * @param array $numericColumns Os nomes das colunas que a serem convertidas para um resultado númerico (INT|FLOAT). Um array vazio tentará converter todas.
     * @return array
     */
  public function serializeNumericColumns($lines, $numericColumns = []) {
    foreach ($lines as $index => $line) {
      foreach ($line as $columnName => $value) {
        if (in_array($columnName, $numericColumns) || count($numericColumns) === 0) {
          if (is_numeric($value)) $lines[$index][$columnName] = $value + 0;
        }
      }
    }
    return $lines;
  }

  /**
   * Cria uma nova conexão com o banco e realiza uma consulta, encerrando a conexão imediatamente.
   * @param string $sql A query da consulta.
   * @param array $numericColumns Os nomes das colunas que a serem convertidas para um resultado númerico (INT|FLOAT). Um array vazio tentará converter todas.
   * @param bool $returnFirstLine Se verdadeiro, irá retornar a primeira linha do resultado, senão NULL.
   * @return array Linhas resultantes
   */
  public static function fastQuery($sql, $numericColumns = array(), $returnFirstLine = false)
  {
    $db = new self();
    $result = $db->query($sql, $numericColumns);
    if ($returnFirstLine) return (count($result) > 0) ? $result[0] : null;
    else return $result;
  }
}