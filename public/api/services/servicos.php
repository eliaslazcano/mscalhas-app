<?php
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/AuthHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/../database/DbMscalhas.php';

HttpHelper::validarMetodos(array('GET','POST','PUT'));
AuthHelper::sessionValidate();

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
  $id = HttpHelper::obterParametro('id');
  if (!$id) {
    $servicos = DbMscalhas::fastQuery("SELECT s.id, s.socio_responsavel, so.nome socio_responsavel_nome, s.valor, s.cliente_nome, s.data_criacao, s.data_finalizacao FROM servicos s LEFT JOIN socios so ON s.socio_responsavel = so.id ORDER BY s.id DESC", array('id', 'socio_responsavel', 'valor'));
    HttpHelper::emitirJson($servicos);
  } else {
    $query = "SELECT s.*, x.nome socio_responsavel_nome FROM servicos s LEFT JOIN socios x ON s.socio_responsavel = x.id WHERE s.id = :id";
    $db = new DbMscalhas();
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id);
    if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
    $servico = $statement->fetchAll(PDO::FETCH_ASSOC);
    if (count($servico) === 0) HttpHelper::erroJson(400, "Serviço não encontrado", 1);
    $servico = $db->serializeNumericColumns($servico, array('id','socio_responsavel','valor'));
    $servico = $servico[0];
    $servico['pagamentos'] = $db->query("SELECT p.id, p.tipo, IF(p.cheque IS NULL, p.valor, c.valor) AS valor, p.parcelas, p.cheque, p.data_registro, IF(p.cheque IS NULL, p.data_pagamento, c.data_cheque) data_pagamento, IF(p.cheque IS NULL, NULL, c.data_compensado) AS data_compensado FROM pagamentos p LEFT JOIN cheques c ON p.cheque = c.id WHERE p.servico = ".$id, array('id', 'tipo', 'valor', 'parcelas', 'cheque'));
    HttpHelper::emitirJson($servico);
  }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $socio_responsavel   = HttpHelper::validarParametro('socio_responsavel');
  $valor               = HttpHelper::obterParametro('valor');
  $cliente_nome        = HttpHelper::validarParametro('cliente_nome');
  $cliente_cpfcnpj     = HttpHelper::obterParametro('cliente_cpfcnpj');
  $endereco_numero     = HttpHelper::obterParametro('endereco_numero');
  $endereco_logradouro = HttpHelper::obterParametro('endereco_logradouro');
  $endereco_bairro     = HttpHelper::obterParametro('endereco_bairro');
  $endereco_cidade     = HttpHelper::obterParametro('endereco_cidade');
  $endereco_uf         = HttpHelper::obterParametro('endereco_uf');
  $endereco_complemento= HttpHelper::obterParametro('endereco_complemento');
  $contato_email       = HttpHelper::obterParametro('contato_email');
  $contato_fone        = HttpHelper::obterParametro('contato_fone');
  $contato_fone2       = HttpHelper::obterParametro('contato_fone2');
  $descricao           = HttpHelper::obterParametro('descricao');
  $observacao          = HttpHelper::obterParametro('observacao');

  $query = "INSERT INTO servicos (endereco_cidade, observacao, endereco_logradouro, contato_fone2, contato_fone, valor, cliente_nome, socio_responsavel, cliente_cpfcnpj, descricao, contato_email, endereco_complemento, endereco_uf, endereco_numero, endereco_bairro) VALUES (:endereco_cidade, :observacao, :endereco_logradouro, :contato_fone2, :contato_fone, :valor, :cliente_nome, :socio_responsavel, :cliente_cpfcnpj, :descricao, :contato_email, :endereco_complemento, :endereco_uf, :endereco_numero, :endereco_bairro)";
  $db = new DbMscalhas();
  $statement = $db->prepare($query);
  $statement->bindValue(':socio_responsavel', $socio_responsavel);
  $statement->bindValue(':valor', $valor ? $valor : '0');
  $statement->bindValue(':cliente_nome', StringHelper::toUpperCase($cliente_nome));
  $statement->bindValue(':cliente_cpfcnpj', $cliente_cpfcnpj);
  $statement->bindValue(':endereco_numero', $endereco_numero);
  $statement->bindValue(':endereco_logradouro', StringHelper::toUpperCase($endereco_logradouro));
  $statement->bindValue(':endereco_bairro', StringHelper::toUpperCase($endereco_bairro));
  $statement->bindValue(':endereco_cidade', StringHelper::toUpperCase($endereco_cidade));
  $statement->bindValue(':endereco_uf', StringHelper::toUpperCase($endereco_uf));
  $statement->bindValue(':endereco_complemento', StringHelper::toUpperCase($endereco_complemento));
  $statement->bindValue(':contato_email', $contato_email);
  $statement->bindValue(':contato_fone', StringHelper::extractNumbers($contato_fone));
  $statement->bindValue(':contato_fone2', StringHelper::extractNumbers($contato_fone2));
  $statement->bindValue(':descricao', trim($descricao));
  $statement->bindValue(':observacao', trim($observacao));
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($db->getConn()->lastInsertId());
}
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT')
{
  $id                  = HttpHelper::validarParametro('id');
  $socio_responsavel   = HttpHelper::validarParametro('socio_responsavel');
  $valor               = HttpHelper::obterParametro('valor');
  $cliente_nome        = HttpHelper::validarParametro('cliente_nome');
  $cliente_cpfcnpj     = HttpHelper::obterParametro('cliente_cpfcnpj');
  $endereco_numero     = HttpHelper::obterParametro('endereco_numero');
  $endereco_logradouro = HttpHelper::obterParametro('endereco_logradouro');
  $endereco_bairro     = HttpHelper::obterParametro('endereco_bairro');
  $endereco_cidade     = HttpHelper::obterParametro('endereco_cidade');
  $endereco_uf         = HttpHelper::obterParametro('endereco_uf');
  $endereco_complemento= HttpHelper::obterParametro('endereco_complemento');
  $contato_email       = HttpHelper::obterParametro('contato_email');
  $contato_fone        = HttpHelper::obterParametro('contato_fone');
  $contato_fone2       = HttpHelper::obterParametro('contato_fone2');
  $descricao           = HttpHelper::obterParametro('descricao');
  $observacao          = HttpHelper::obterParametro('observacao');
  $data_finalizacao    = HttpHelper::obterParametro('data_finalizacao');

  $query = "UPDATE servicos SET socio_responsavel = :socio_responsavel, valor = :valor, cliente_nome = :cliente_nome, cliente_cpfcnpj = :cliente_cpfcnpj, endereco_numero = :endereco_numero, endereco_logradouro = :endereco_logradouro, endereco_bairro = :endereco_bairro, endereco_cidade = :endereco_cidade, endereco_uf = :endereco_uf, endereco_complemento = :endereco_complemento, contato_email = :contato_email, contato_fone = :contato_fone, contato_fone2 = :contato_fone2, descricao = :descricao, observacao = :observacao, data_finalizacao = :data_finalizacao WHERE id = :id";
  $db = new DbMscalhas();
  $statement = $db->prepare($query);
  $statement->bindValue(':socio_responsavel', $socio_responsavel);
  $statement->bindValue(':valor', $valor ? $valor : '0');
  $statement->bindValue(':cliente_nome', StringHelper::toUpperCase($cliente_nome));
  $statement->bindValue(':cliente_cpfcnpj', $cliente_cpfcnpj);
  $statement->bindValue(':endereco_numero', $endereco_numero);
  $statement->bindValue(':endereco_logradouro', StringHelper::toUpperCase($endereco_logradouro));
  $statement->bindValue(':endereco_bairro', StringHelper::toUpperCase($endereco_bairro));
  $statement->bindValue(':endereco_cidade', StringHelper::toUpperCase($endereco_cidade));
  $statement->bindValue(':endereco_uf', StringHelper::toUpperCase($endereco_uf));
  $statement->bindValue(':endereco_complemento', StringHelper::toUpperCase($endereco_complemento));
  $statement->bindValue(':contato_email', $contato_email);
  $statement->bindValue(':contato_fone', StringHelper::extractNumbers($contato_fone));
  $statement->bindValue(':contato_fone2', StringHelper::extractNumbers($contato_fone2));
  $statement->bindValue(':descricao', trim($descricao));
  $statement->bindValue(':observacao', trim($observacao));
  $statement->bindValue(':data_finalizacao', $data_finalizacao);
  $statement->bindValue(':id', $id);
  if (!$statement->execute()) HttpHelper::erroJson(500, "Falha na base de dados", 0, $statement->errorInfo());
  HttpHelper::emitirJson($statement->rowCount() > 0);
}