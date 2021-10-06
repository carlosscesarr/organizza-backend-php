<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Http\Middleware\Queue as MiddlewareQueue;

//CONFIG DATABASE CLASS

MiddlewareQueue::setMap(
    [
        "manutencao" => \App\Http\Middleware\Manutencao::class,
        //"required-admin-logout" => \App\Http\Middleware\RequireAdminLogout::class,
        "api" => \App\Http\Middleware\Api::class,
        //"user-basic-auth" => \App\Http\Middleware\UserBasicAuth::class,
        "jwt-auth" => \App\Http\Middleware\JWTauth::class,
        //"cache" => \App\Http\Middleware\Cache::class
    ]
);

MiddlewareQueue::setDefault(
    ["api"]
);
