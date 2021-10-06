<?php

$obRouter->get('/v1/lancamentos', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\LancamentosController",
    "action" => "index"
]);

$obRouter->post('/v1/lancamentos', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\LancamentosController",
    "action" => "cadastrar"
]);

$obRouter->put('/v1/lancamentos/{id}/marcar-como-pendente', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\LancamentosController",
    "action" => "marcarComoPendente"
]);

$obRouter->put('/v1/lancamentos/{id}/marcar-como-resolvido', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\LancamentosController",
    "action" => "marcarComoResolvido"
]);

$obRouter->delete('/v1/lancamentos/{id}', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\LancamentosController",
    "action" => "deletar"
]);