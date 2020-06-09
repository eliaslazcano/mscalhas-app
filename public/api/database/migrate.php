<?php
set_time_limit(0);
?>
<style>
  * {
    white-space: pre-wrap !important;
  }
</style>
<?php
require_once __DIR__.'/../Config.php';
require_once __DIR__.'/../helper/HttpHelper.php';
require_once __DIR__.'/../helper/StringHelper.php';
require_once __DIR__.'/DbMscalhas.php';

//Script para limpar tudo em caso de erro
function truncateAll(DbMscalhas $db) {
  $databaseName = Config::DATABASE_NAME;
  $db->exec("SET FOREIGN_KEY_CHECKS=0");
  $result = $db->query("SELECT Concat('TRUNCATE TABLE ',table_schema,'.',TABLE_NAME, ';') AS cmd FROM INFORMATION_SCHEMA.TABLES where  table_schema in ('$databaseName')");
  foreach ($result as $line) {
    $db->exec($line["cmd"]);
  }
  $db->exec("SET FOREIGN_KEY_CHECKS=1");
}

//Conexão SQLite
const PATH_TO_SQLITE_FILE = __DIR__.'/legacy_database.db';
$pdo = new PDO("sqlite:".PATH_TO_SQLITE_FILE);
if ($pdo) echo "Conectado ao SQLite\n";

//Conexão MySQL
$db = new DbMscalhas();
if ($db) echo "Conectado ao MySQL\n";

//Limpa a base para receber os dados
truncateAll($db);

//Migra contas de usuário
echo "Inserindo usuarios...\n";
$statement = $pdo->query("SELECT * FROM Usuarios");
$usuarios = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($usuarios as $usuario) {
  $query = "INSERT INTO usuarios (login, senha, nome, desde, email) VALUES (:login, :senha, :nome, :desde, 'mscalhas7125@hotmail.com')";
  $statement = $db->prepare($query);
  $statement->bindValue(':login', StringHelper::toLowerCase($usuario['Nome']));
  $statement->bindValue(':senha', md5($usuario['Senha']));
  $statement->bindValue(':nome', StringHelper::toUpperCase($usuario['Nome']));
  $statement->bindValue(':desde', substr($usuario['DataCadastro'], 0, 4).'-'.substr($usuario['DataCadastro'], 4, 2).'-'.substr($usuario['DataCadastro'], 6, 2).' 00:00:00');
  if (!$statement->execute()) {
    truncateAll($db);
    HttpHelper::erroJson(500, 'Falha na base de dados', 1, $statement->errorInfo());
  }
}
echo "Usuarios inseridos.\n";

//Migra os sócios
echo "Inserindo socios...\n";
$statement = $pdo->query("SELECT * FROM Funcionarios WHERE categoriaId = 1");
$socios = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($socios as $socio) {
  $query = "INSERT INTO socios (id, nome) VALUES (:id, :nome)";
  $statement = $db->prepare($query);
  $statement->bindValue(':id', intval($socio['FuncionarioId']), PDO::PARAM_INT);
  $statement->bindValue(':nome', StringHelper::toUpperCase($socio['Nome']));
  if (!$statement->execute()) {
    truncateAll($db);
    HttpHelper::erroJson(500, 'Falha na base de dados', 2, $statement->errorInfo());
  }
}
echo "Socios inseridos.\n";

//Migra os serviços
echo "Inserindo servicos...\n";
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

  $query = "INSERT INTO servicos
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
  $statement->bindValue(':endereco_logradouro', $logradouro ? $logradouro : null);
  $statement->bindValue(':endereco_bairro', trim($servico['Bairro']) ? StringHelper::toUpperCase($servico['Bairro']) : null);
  $statement->bindValue(':endereco_cidade', trim($servico['Cidade']) ? StringHelper::toUpperCase($servico['Cidade']) : null);
  $statement->bindValue(':endereco_uf', $servico['EstadoUF'] ? substr($servico['EstadoUF'], 0 , 2) : null);
  $statement->bindValue(':endereco_complemento', trim($servico['Complemento']) ? StringHelper::toUpperCase($servico['Complemento']) : null);
  $statement->bindValue(':contato_email', trim($servico['Email']) ? trim($servico['Email']) : null);
  $statement->bindValue(':contato_fone', $fone1 ? $fone1 : null);
  $statement->bindValue(':contato_fone2', $fone2 ? $fone2 : null);
  $statement->bindValue(':data_criacao', substr($servico['DataCriacao'], 0, 4).'-'.substr($servico['DataCriacao'], 4, 2).'-'.substr($servico['DataCriacao'], 6, 2).' 00:00:00');
  $statement->bindValue(':data_finalizacao', ($servico['DataFinalizacao'] !== '0') ? substr($servico['DataFinalizacao'], 0, 4).'-'.substr($servico['DataFinalizacao'], 4, 2).'-'.substr($servico['DataFinalizacao'], 6, 2).' 00:00:00' : null);
  $statement->bindValue(':descricao', trim($servico['Descricao']) ? trim($servico['Descricao']) : null);
  $statement->bindValue(':observacao', trim($servico['Observacao']) ? trim($servico['Observacao']) : null);
  if (!$statement->execute()) {
    truncateAll($db);
    HttpHelper::erroJson(500, 'Falha na base de dados', 1, array(
      "pdo_info" => $statement->errorInfo(),
      "socio" => $servico['ResponsavelID']
    ));
  }
}
echo "Servicos inseridos.\n";

//Registra o pagamento dos serviços
echo "Inserindo pagamentos...\n";
foreach ($servicos as $servico) {
  $formaPagamento = intval($servico['FormaPagamento']);
  $dataPagamento = substr($servico['DataPagamento'], 0, 4).'-'.substr($servico['DataPagamento'], 4, 2).'-'.substr($servico['DataPagamento'], 6, 2);

  //Se for Dinheiro ou Débito...
  if ($formaPagamento === 0 || $formaPagamento === 4) {
    $pagtoTipo = $formaPagamento === 0 ? 0 : 1;
    $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (:tipo, :valor, :servico, :data_pagamento)");
    $statement->bindValue(':tipo', $pagtoTipo, PDO::PARAM_INT);
    $statement->bindValue(':valor', $servico['valor']);
    $statement->bindValue(':servico', $servico['ServicoId']);
    $statement->bindValue(':data_pagamento', $dataPagamento);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 4, $statement->errorInfo());
    }
  }
  //Se for Cheque...
  elseif ($formaPagamento === 1) {
    $statement = $pdo->query("SELECT * FROM Cheques WHERE ServicoId = ".$servico['ServicoId']);
    $cheque = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$cheque) HttpHelper::erroJson(500, "Cheque não encontrado", 0, $servico['ServicoId']);

    $query = "INSERT INTO cheques (id, cliente, banco, agencia, conta, tipo, numcheque, valor, data_cheque, data_compensado, servico) VALUES (:id, :cliente, :banco, :agencia, :conta, :tipo, :numcheque, :valor, :data_cheque, :data_compensado, :servico)";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $cheque['ChequeId']);
    $statement->bindValue(':cliente', null); //O nome deve ser buscado no serviço, não daqui.
    $statement->bindValue(':banco', $cheque['NomeBanco'] ? StringHelper::toUpperCase($cheque['NomeBanco']) : null);
    $statement->bindValue(':agencia', $cheque['Agencia'] ? StringHelper::toUpperCase($cheque['Agencia']) : null);
    $statement->bindValue(':conta', $cheque['Conta'] ? StringHelper::toUpperCase($cheque['Conta']) : null);
    $statement->bindValue(':tipo', (intval($cheque['TipoCheque']) === 0) ? 1 : ((intval($cheque['TipoCheque']) === 1) ? 0 : null), PDO::PARAM_INT);
    $statement->bindValue(':numcheque', $cheque['NumCheque'] ? StringHelper::toUpperCase($cheque['NumCheque']) : null);
    $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
    $statement->bindValue(':data_cheque', substr($cheque['DataCheque'], 0, 4).'-'.substr($cheque['DataCheque'], 4, 2).'-'.substr($cheque['DataCheque'], 6, 2));
    $statement->bindValue(':data_compensado', $cheque['DataCompensacao'] ? substr($cheque['DataCompensacao'], 0, 4).'-'.substr($cheque['DataCompensacao'], 4, 2).'-'.substr($cheque['DataCompensacao'], 6, 2) : null);
    $statement->bindValue(':servico', $servico['ServicoId']);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 5, $statement->errorInfo());
    }

    $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (4, :valor, :servico, null)");
    $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
    $statement->bindValue(':servico', $servico['ServicoId']);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 6, $statement->errorInfo());
    }
  }
  //Se for Dinheiro + Cheque
  elseif ($formaPagamento === 2) {
    //o valor em dinheiro fica no cadastro do servico, o valor em cheque fica na tabela do cheque.

    //Adiciona o pagamento em dinheiro
    $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (0, :valor, :servico, :data_pagamento)");
    $statement->bindValue(':valor', $servico['valor']);
    $statement->bindValue(':servico', $servico['ServicoId']);
    $statement->bindValue(':data_pagamento', $dataPagamento);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 4, $statement->errorInfo());
    }

    //Adiciona o pagamento em cheque
    $statement = $pdo->query("SELECT * FROM Cheques WHERE ServicoId = ".$servico['ServicoId']);
    $cheque = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$cheque) HttpHelper::erroJson(500, "Cheque não encontrado", 0, $servico['ServicoId']);
    $query = "INSERT INTO cheques (id, cliente, banco, agencia, conta, tipo, numcheque, valor, data_cheque, data_compensado, servico) VALUES (:id, :cliente, :banco, :agencia, :conta, :tipo, :numcheque, :valor, :data_cheque, :data_compensado, :servico)";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $cheque['ChequeId']);
    $statement->bindValue(':cliente', null); //O nome deve ser buscado do serviço, não daqui.
    $statement->bindValue(':banco', $cheque['NomeBanco'] ? StringHelper::toUpperCase($cheque['NomeBanco']) : null);
    $statement->bindValue(':agencia', $cheque['Agencia'] ? StringHelper::toUpperCase($cheque['Agencia']) : null);
    $statement->bindValue(':conta', $cheque['Conta'] ? StringHelper::toUpperCase($cheque['Conta']) : null);
    $statement->bindValue(':tipo', (intval($cheque['TipoCheque']) === 0) ? 1 : ((intval($cheque['TipoCheque']) === 1) ? 0 : null), PDO::PARAM_INT);
    $statement->bindValue(':numcheque', $cheque['NumCheque'] ? StringHelper::toUpperCase($cheque['NumCheque']) : null);
    $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
    $statement->bindValue(':data_cheque', substr($cheque['DataCheque'], 0, 4).'-'.substr($cheque['DataCheque'], 4, 2).'-'.substr($cheque['DataCheque'], 6, 2));
    $statement->bindValue(':data_compensado', $cheque['DataCompensacao'] ? substr($cheque['DataCompensacao'], 0, 4).'-'.substr($cheque['DataCompensacao'], 4, 2).'-'.substr($cheque['DataCompensacao'], 6, 2) : null);
    $statement->bindValue(':servico', $servico['ServicoId']);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 5, $statement->errorInfo());
    }
    $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (4, :valor, :servico, null)");
    $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
    $statement->bindValue(':servico', $servico['ServicoId']);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 6, $statement->errorInfo());
    }
  }
  //Se for Crédito
  elseif ($formaPagamento === 3) {
    $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (2, :valor, :servico, :data_pagamento)");
    $statement->bindValue(':valor', $servico['valor']);
    $statement->bindValue(':servico', $servico['ServicoId']);
    $statement->bindValue(':data_pagamento', $dataPagamento);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 4, $statement->errorInfo());
    }
  }
  //Se for multiplos cheques
  elseif ($formaPagamento === 6) {
    $statement = $pdo->query("SELECT * FROM Cheques WHERE ServicoId = ".$servico['ServicoId']);
    $cheques = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cheques as $cheque) {
      $query = "INSERT INTO cheques (id, cliente, banco, agencia, conta, tipo, numcheque, valor, data_cheque, data_compensado, servico) VALUES (:id, :cliente, :banco, :agencia, :conta, :tipo, :numcheque, :valor, :data_cheque, :data_compensado, :servico)";
      $statement = $db->prepare($query);
      $statement->bindValue(':id', $cheque['ChequeId']);
      $statement->bindValue(':cliente', null); //O nome deve ser buscado no serviço, não daqui.
      $statement->bindValue(':banco', $cheque['NomeBanco'] ? StringHelper::toUpperCase($cheque['NomeBanco']) : null);
      $statement->bindValue(':agencia', $cheque['Agencia'] ? StringHelper::toUpperCase($cheque['Agencia']) : null);
      $statement->bindValue(':conta', $cheque['Conta'] ? StringHelper::toUpperCase($cheque['Conta']) : null);
      $statement->bindValue(':tipo', (intval($cheque['TipoCheque']) === 0) ? 1 : ((intval($cheque['TipoCheque']) === 1) ? 0 : null), PDO::PARAM_INT);
      $statement->bindValue(':numcheque', $cheque['NumCheque'] ? StringHelper::toUpperCase($cheque['NumCheque']) : null);
      $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
      $statement->bindValue(':data_cheque', substr($cheque['DataCheque'], 0, 4).'-'.substr($cheque['DataCheque'], 4, 2).'-'.substr($cheque['DataCheque'], 6, 2));
      $statement->bindValue(':data_compensado', $cheque['DataCompensacao'] ? substr($cheque['DataCompensacao'], 0, 4).'-'.substr($cheque['DataCompensacao'], 4, 2).'-'.substr($cheque['DataCompensacao'], 6, 2) : null);
      $statement->bindValue(':servico', $servico['ServicoId']);
      if (!$statement->execute()) {
        truncateAll($db);
        HttpHelper::erroJson(500, 'Falha na base de dados', 9, $statement->errorInfo());
      }

      $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (4, :valor, :servico, null)");
      $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
      $statement->bindValue(':servico', $servico['ServicoId']);
      if (!$statement->execute()) {
        truncateAll($db);
        HttpHelper::erroJson(500, 'Falha na base de dados', 10, $statement->errorInfo());
      }
    }
  }
  //Se for outros
  elseif ($formaPagamento === 7) {
    $statement = $db->prepare("INSERT INTO pagamentos (tipo, valor, servico, data_pagamento) VALUES (5, :valor, :servico, :data_pagamento)");
    $statement->bindValue(':valor', $servico['valor']);
    $statement->bindValue(':servico', $servico['ServicoId']);
    $statement->bindValue(':data_pagamento', $dataPagamento);
    if (!$statement->execute()) {
      truncateAll($db);
      HttpHelper::erroJson(500, 'Falha na base de dados', 4, $statement->errorInfo());
    }
  }
}

//Registra os cheques standalone
echo "Inserindo cheques avulsos...\n";
$statement = $pdo->query("SELECT * FROM Cheques WHERE ServicoId IS NULL");
$cheques = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($cheques as $cheque) {
  $query = "INSERT INTO cheques (id, cliente, banco, agencia, conta, tipo, numcheque, valor, data_cheque, data_compensado, servico) VALUES (:id, :cliente, :banco, :agencia, :conta, :tipo, :numcheque, :valor, :data_cheque, :data_compensado, :servico)";
  $statement = $db->prepare($query);
  $statement->bindValue(':id', $cheque['ChequeId']);
  $statement->bindValue(':cliente', StringHelper::toUpperCase($cheque['NomeCliente'])); //O nome deve ser buscado no serviço, não daqui.
  $statement->bindValue(':banco', $cheque['NomeBanco'] ? StringHelper::toUpperCase($cheque['NomeBanco']) : null);
  $statement->bindValue(':agencia', $cheque['Agencia'] ? StringHelper::toUpperCase($cheque['Agencia']) : null);
  $statement->bindValue(':conta', $cheque['Conta'] ? StringHelper::toUpperCase($cheque['Conta']) : null);
  $statement->bindValue(':tipo', (intval($cheque['TipoCheque']) === 0) ? 1 : ((intval($cheque['TipoCheque']) === 1) ? 0 : null), PDO::PARAM_INT);
  $statement->bindValue(':numcheque', $cheque['NumCheque'] ? StringHelper::toUpperCase($cheque['NumCheque']) : null);
  $statement->bindValue(':valor', $cheque['ValorCheque'] ? $cheque['ValorCheque'] : '0');
  $statement->bindValue(':data_cheque', substr($cheque['DataCheque'], 0, 4).'-'.substr($cheque['DataCheque'], 4, 2).'-'.substr($cheque['DataCheque'], 6, 2));
  $statement->bindValue(':data_compensado', $cheque['DataCompensacao'] ? substr($cheque['DataCompensacao'], 0, 4).'-'.substr($cheque['DataCompensacao'], 4, 2).'-'.substr($cheque['DataCompensacao'], 6, 2) : null);
  $statement->bindValue(':servico', null);
  if (!$statement->execute()) {
    truncateAll($db);
    HttpHelper::erroJson(500, 'Falha na base de dados', 12, $statement->errorInfo());
  }
}
