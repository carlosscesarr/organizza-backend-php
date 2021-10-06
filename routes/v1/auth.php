<?php

$obRouter->post('/v1/auth/login', [
    "controller" => "App\Modules\V1\Controllers\AuthController",
    "action" => "login"
]);