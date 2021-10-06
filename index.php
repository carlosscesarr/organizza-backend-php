<?php
include __DIR__.'/includes/cors.php';
include __DIR__.'/includes/app.php';

use App\Config;
use App\Http\Router;

Config::init();
// Objeto da rota
$obRouter = new Router(Config::$baseUrl);

// Imclui arquivo de rotas
include __DIR__.'/routes/v1/routes.php';
// Imprime o response da rota
$obRouter->run();