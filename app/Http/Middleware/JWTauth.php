<?php

namespace App\Http\Middleware;

use Firebase\JWT\JWT;

class JWTauth {
    public function handle($request, $response, $next) {
        $this->auth($request, $response);

        return $next($request, $response);
    }

    public function auth($request, $response) {
        if ($obUser = $this->getJWTAuthUser($request)) {
            $request->obUser = $obUser;
            return true;
        }

        return $response->status(403)->send(["status" => "Acesso negado", "mensagem" => "Credenciais inválidas"]);
    }

    public function getJWTAuthUser($request) {

        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        try {
            $decoded = (array) JWT::decode($jwt, $key, array('HS256'));

        } catch (\Exception $e) {
            return false;
        }

        $email = $decoded["email"] ?? "";
        $cpf = $decoded["cpf"] ?? "";
        $id = $decoded["id"] ?? "";
        if (empty($email) || empty($cpf) || empty($id)) {
            return false;
        }
        
        return true;
    }
}