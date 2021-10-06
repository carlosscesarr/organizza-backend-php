<?php
namespace App\Http\Middleware;

class DelayResponse {
    public function handle($request, $response, $next) {
        sleep(1);
        return $next($request, $response);
    }
}