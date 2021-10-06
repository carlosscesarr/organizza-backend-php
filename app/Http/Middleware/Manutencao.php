<?php

namespace App\Http\Middleware;

use App\Http\Response;
class Manutencao {
    public function handle($request, $response, $next) {
        return $next($request, $response);
    }
}