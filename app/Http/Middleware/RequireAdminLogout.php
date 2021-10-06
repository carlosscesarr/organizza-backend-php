<?php

namespace App\Http\Middleware;

class RequireAdminLogout {
    public function handle($request, $next) {

        echo "passou middleware";
        return $next($request);
    }
}