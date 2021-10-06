<?php
$obRouter->get('/v1/me', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\MeController",
    "action" => "visualizar"
]);

$obRouter->put('/v1/me', [
    "middlewares" => ["jwt-auth"],
    "controller" => "App\Modules\V1\Controllers\MeController",
    "action" => "editar"
]);