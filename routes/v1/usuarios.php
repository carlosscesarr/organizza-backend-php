<?php

$obRouter->post('/v1/usuarios', [
    "controller" => "App\Modules\V1\Controllers\UsuariosController",
    "action" => "cadastrar"
]);