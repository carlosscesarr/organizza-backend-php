<?php

$obRouter->get('/v1/categorias', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\CategoriasController",
    "action" => "index"
]);

$obRouter->get('/v1/categorias/{id}', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\CategoriasController",
    "action" => "visualizar"
]);

$obRouter->post('/v1/categorias', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\CategoriasController",
    "action" => "cadastrar"
]);

$obRouter->put('/v1/categorias/{id}', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\CategoriasController",
    "action" => "editar"
]);