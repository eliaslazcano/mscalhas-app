<?php
// === ENTRY POINT ===
//Este arquivo é executado globalmente antes de qualquer serviço ser carregado

// === Tratamento de Rota ===
$pathInfo = isset($_SERVER['PATH_INFO']);
$origPathInfo = isset($_SERVER['ORIG_PATH_INFO']);

if ($pathInfo) {
  $caminho = $_SERVER['PATH_INFO'];
} elseif ($origPathInfo) {
  $caminho = $_SERVER['ORIG_PATH_INFO'];
} else {
  echo "Falha no servidor. Rota não identificada.";
  die();
}

if (substr($caminho, 0, 1) === '/') $caminho = substr($caminho, 1); //Remove a barra inicial

// Se houver URL definida
if ($caminho) {
  // Se existir o PHP da URL informada
  if (file_exists(__DIR__.'/services/' . $caminho . '.php')) {
    require __DIR__.'/services/' . $caminho . '.php';
  } elseif (file_exists(__DIR__.'/services/' . $caminho . '/index.php')) {
    require __DIR__.'/services/' . $caminho . '/index.php' ;
  } else {
    require __DIR__.'/public/404.php';
  }
} else {
  // Quando não há URl definida, tentar evocar index.php
  if (file_exists(__DIR__.'/public/index.php')) require __DIR__.'/public/index.php';
  else require __DIR__.'/public/404.php';
}
