<?php

require_once __DIR__.'/../../helper/HttpHelper.php';
require_once __DIR__.'/../../helper/AuthHelper.php';
require_once __DIR__.'/../../helper/ArrayHelper.php';
require_once __DIR__.'/../../helper/StringHelper.php';
require_once __DIR__.'/../../database/DbMscalhas.php';

HttpHelper::validarPost();
AuthHelper::sessionValidate();

$ano = HttpHelper::validarParametro('ano');
$db  = new DbMscalhas();

$query = "SELECT so.id socio, so.nome, SUM(IF(p.cheque IS NULL, p.valor, c.valor)) total, MONTH(IF(p.cheque IS NULL, p.data_pagamento, c.data_cheque)) mes FROM socios so INNER JOIN servicos se ON so.id = se.socio_responsavel INNER JOIN pagamentos p ON se.id = p.servico LEFT JOIN cheques c on p.cheque = c.id WHERE IF(p.cheque IS NULL, p.data_pagamento, c.data_cheque) LIKE :ano GROUP BY socio, mes ORDER BY socio, mes";
$statement = $db->prepare($query);
$statement->bindValue(':ano', "$ano-%");
if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

$dadosSeparadosPorId = ArrayHelper::group_by('socio', $result);
sort($dadosSeparadosPorId);
$dados = ArrayHelper::map($dadosSeparadosPorId, function ($item) {
  $faturamento = ArrayHelper::map($item, function ($x) { return array("mes" => intval($x['mes']), "valor" => floatval($x['total'])); });
  $fatX = array();
  for ($i = 1; $i <= 12; $i++) {
    $mes = $i < 10 ? '0'.strval($i) : strval($i);
    $dadosMes = ArrayHelper::find($faturamento, function ($item) use ($mes) { return $item['mes'] == $mes; });
    $fatX[$i-1] = $dadosMes ? $dadosMes['valor'] : 0;
  }
  return (object) array(
    "id" => intval($item[0]['socio']),
    "nome" => $item[0]['nome'],
    "faturamento" => $fatX
  );
});

HttpHelper::emitirJson($dados);