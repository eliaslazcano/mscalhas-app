<?php
set_time_limit(0);

require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/DbMscalhas.php';
echo '<pre>';

//Conexão SQLite
const PATH_TO_SQLITE_FILE = __DIR__.'/legacy_database.db';
$pdo = new PDO("sqlite:".PATH_TO_SQLITE_FILE);

//Conexão MySQL
$db = new DbMscalhas();

//Migra contas de usuário
$statement = $pdo->query("SELECT * FROM Usuarios");
$usuarios = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($usuarios as $usuario) {
  $query = "INSERT INTO usuarios (login, senha, nome, desde, email) VALUES (:login, :senha, :nome, :desde, 'mscalhas7125@hotmail.com')";
  $statement = $db->prepare($query);
  $statement->bindValue(':login', StringHelper::toLowerCase($usuario['Nome']));
  $statement->bindValue(':senha', md5($usuario['Senha']));
  $statement->bindValue(':nome', StringHelper::toUpperCase($usuario['Nome']));
  $statement->bindValue(':desde', substr($usuario['DataCadastro'], 0, 4).'-'.substr($usuario['DataCadastro'], 4, 2).'-'.substr($usuario['DataCadastro'], 6, 2).' 00:00:00');
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falha na base de dados', 1, $statement->errorInfo());
}
echo "usuarios migrados...\n";

//Migra os sócios
$statement = $pdo->query("SELECT * FROM Funcionarios WHERE categoriaId = 1");
$socios = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($socios as $socio) {
  $query = "INSERT INTO socios (id, nome) VALUES (:id, :nome)";
  $statement = $db->prepare($query);
  $statement->bindValue(':id', intval($socio['FuncionarioId']), PDO::PARAM_INT);
  $statement->bindValue(':nome', StringHelper::toUpperCase($socio['Nome']));
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falha na base de dados', 2, $statement->errorInfo());
}
echo "sócios migrados...\n";

//Migra os serviços
$statement = $pdo->query("SELECT * FROM Servicos");
$servicos = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($servicos as $servico) {
  $logradouro = trim($servico['Endereco']);
  $numero = '';
  if ($logradouro) {
    if (StringHelper::contains($logradouro, ', ') !== false) {
      $pos = StringHelper::contains($logradouro, ', ');
      $numero = substr($logradouro, ($pos + 2));
      $logradouro = substr($logradouro, 0, $pos);
    }
  }

  $fone1 = '';
  $fone2 = '';
  if ($servico['FoneCelular'] && $servico['FoneFixo']) {
    $fone1 = StringHelper::extractNumbers($servico['FoneCelular']);
    $fone2 = StringHelper::extractNumbers($servico['FoneFixo']);
  } elseif ($servico['FoneCelular']) {
    $fone1 = StringHelper::extractNumbers($servico['FoneCelular']);
  } elseif ($servico['FoneFixo']) {
    $fone1 = StringHelper::extractNumbers($servico['FoneFixo']);
  }

  $query = "INSERT INTO Servicos 
    (id, socio_responsavel, valor, cliente_nome, cliente_cpfcnpj, endereco_numero, endereco_logradouro, endereco_bairro, endereco_cidade, endereco_uf, endereco_complemento, contato_email, contato_fone, contato_fone2, data_criacao, data_finalizacao, descricao, observacao) 
    VALUES 
    (:id, :socio_responsavel, :valor, :cliente_nome, :cliente_cpfcnpj, :endereco_numero, :endereco_logradouro, :endereco_bairro, :endereco_cidade, :endereco_uf, :endereco_complemento, :contato_email, :contato_fone, :contato_fone2, :data_criacao, :data_finalizacao, :descricao, :observacao)";
  $statement = $db->prepare($query);
  $statement->bindValue(':id', $servico['ServicoId']);
  $statement->bindValue(':socio_responsavel', $servico['ResponsavelID'] !== '-1' ? $servico['ResponsavelID'] : '1');
  $statement->bindValue(':valor', $servico['valor']);
  $statement->bindValue(':cliente_nome', StringHelper::toUpperCase($servico['Cliente']));
  $statement->bindValue(':cliente_cpfcnpj', $servico['CpfCnpj'] ? StringHelper::extractNumbers($servico['CpfCnpj']) : null);
  $statement->bindValue(':endereco_numero', $numero ? $numero : null);
  $statement->bindValue(':endereco_logradouro', $logradouro ? $logradouro : $numero);
  $statement->bindValue(':endereco_bairro', trim($servico['Bairro']) ? StringHelper::toUpperCase($servico['Bairro']) : null);
  $statement->bindValue(':endereco_cidade', trim($servico['Cidade']) ? StringHelper::toUpperCase($servico['Cidade']) : null);
  $statement->bindValue(':endereco_uf', $servico['EstadoUF'] ? substr($servico['EstadoUF'], 0 , 2) : null);
  $statement->bindValue(':endereco_complemento', trim($servico['Complemento']) ? StringHelper::toUpperCase($servico['Complemento']) : null);
  $statement->bindValue(':contato_email', trim($servico['Email']) ? trim($servico['Email']) : $numero);
  $statement->bindValue(':contato_fone', $fone1 ? $fone1 : null);
  $statement->bindValue(':contato_fone2', $fone2 ? $fone2 : null);
  $statement->bindValue(':data_criacao', substr($servico['DataCriacao'], 0, 4).'-'.substr($servico['DataCriacao'], 4, 2).'-'.substr($servico['DataCriacao'], 6, 2).' 00:00:00');
  $statement->bindValue(':data_finalizacao', ($servico['DataFinalizacao'] !== '0') ? substr($servico['DataFinalizacao'], 0, 4).'-'.substr($servico['DataFinalizacao'], 4, 2).'-'.substr($servico['DataFinalizacao'], 6, 2).' 00:00:00' : null);
  $statement->bindValue(':descricao', trim($servico['Descricao']) ? trim($servico['Descricao']) : null);
  $statement->bindValue(':observacao', trim($servico['Observacao']) ? trim($servico['Observacao']) : null);
  if (!$statement->execute()) HttpHelper::erroJson(500, 'Falha na base de dados', 1, array(
    "pdo_info" => $statement->errorInfo(),
    "socio" => $servico['ResponsavelID']
  ));
}
echo "serviços migrados...\n";

echo '</pre>';