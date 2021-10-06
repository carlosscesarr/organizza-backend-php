<?php

namespace App\Http\Middleware;

class UserBasicAuth {
    public function handle($request, $response, $next) {
        $this->basicAuth($request, $response);

        return $next($request, $response);
    }

    public function basicAuth($request) {
        if ($obUser = $this->getBasicAuthUser()) {
            $request->obUser = $obUser;
            return true;
        }

        throw new \Exception("Usuário ou senha inválidos", 403);
        
    }
    public function getBasicAuthUser() {

        if (!isset($_SERVER["PHP_AUTH_USER"]) || !isset($_SERVER["PHP_AUTH_PW"])) {
            return false;
        }
        return true;
    }
}