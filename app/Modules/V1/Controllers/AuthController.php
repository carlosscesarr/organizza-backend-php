<?php

namespace App\Modules\V1\Controllers;

use Firebase\JWT\JWT;
use Core\Validate;
use App\Modules\V1\Models\Usuario;

class AuthController
{
    public function login($request, $response)
    {
        $postVars = $request->getPostVars();
        $email = $postVars["email"] ?? "";
        $senha = $postVars["senha"] ?? "";

        $errosCampos = [];
        if ($email == "") {
            $errosCampos[] = ["nome" => "email", "mensagem" => "Email é obrigatório"];
        } elseif (!Validate::email($email)) {
            $errosCampos[] = ["nome" => "email", "mensagem" => "Email inválido"];
        }
        
        if ($senha == "") {
            $errosCampos[] = ["nome" => "senha", "mensagem" => "Senha é obrigatório"];
        }
        
        if (!empty($errosCampos)) {
            return $response->status(400)->send([
                "codigo" => "validacao",
                "campos" => $errosCampos
            ]);
        }

        $senhaHash = hash("sha256", $senha);

        $usuario = new Usuario();
        $rsUsuario = $usuario->select("id")->whereRaw("email='$email' AND senha='$senhaHash'")->fetch();
        if (empty($rsUsuario)) {
            return $response->status(404)->send([
                "codigo" => "recurso_nao_encontrado",
                "mensagem" => "Email e/ou senha inválidos"
            ]);
        }
        //Application Key
        $key = 'lili9090';

        //Payload - Content
        $payload = [
            'id' => $rsUsuario['id'],
            'email' => $email,
        ];

        $jwt = JWT::encode($payload, $key);

        return $response->send([
            "acesso_token" => $jwt
        ]);
    }

    public static function checkAuth()
    {
        $http_header = apache_request_headers();

        if (isset($http_header['Authorization']) && $http_header['Authorization'] != null) {
            $bearer = explode (' ', $http_header['Authorization']);
            //$bearer[0] = 'bearer';
            //$bearer[1] = 'token jwt';

            $token = explode('.', $bearer[1]);
            $header = $token[0];
            $payload = $token[1];
            $sign = $token[2];

            //Conferir Assinatura
            $valid = hash_hmac('sha256', $header . "." . $payload, 'lili9090', true);
            $valid = base64_encode($valid);

            if ($sign === $valid) {
                return true;
            }
        }

        return false;
    }
}