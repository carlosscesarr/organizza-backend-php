<?php
namespace App\Modules\V1\Controllers;

use Firebase\JWT\JWT;
use App\Modules\V1\Models\Usuario;
use Core\Validate;
use Core\Mask;

class MeController
{
    public $usuario = null;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function visualizar($request, $response)
    {  
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $data = $this->usuario->select("id, nome, email, cpf, data_cadastro, data_atualizacao")->whereRaw("id = $usuarioId")->fetch();

        if (empty($data)) {
            return $response->status(404)->send(["mensagem" => "Nenhum registro foi encontrado"]);
        }

        return $response->send($data);
    }

    public function editar($request, $response)
    {
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));

        $errosCampos = [];
        $dataUpdate = $request->getPostVars();
        $dataUpdate["data_atualizacao"] = date("Y-m-d H:i:s");
        $nome = $dataUpdate["nome"] ?? "";
        $email = $dataUpdate["email"] ?? "";
        $cpf = $dataUpdate["cpf"] ?? "";
        $ativo = $dataUpdate["ativo"] ?? "";
        $usuarioId = $decoded["id"];
        
        if ($nome == "") {
            $errosCampos[] = ["nome" => "nome", "mensagem" => "Nome é obrigatório"];
        } else {
            $nome = filter_var($nome, FILTER_SANITIZE_STRING);
        }

        if ($email == "") {
            $errosCampos[] = ["nome" => "email", "mensagem" => "Email é obrigatório"];
        } elseif (!Validate::email($email)) {
            $errosCampos[] = ["nome" => "email", "mensagem" => "Email inválido"];
        } else {
            $buscaUsuarioEmail = $this->usuario->select("id")->whereRaw("email = '$email' AND id <> $usuarioId")->fetch();
            if (!empty($buscaUsuarioEmail)) {
                $errosCampos[] = ["nome" => "email", "mensagem" => "Email já existe"];
            }
        }

        if ($cpf == "") {
            $errosCampos[] = ["cpf" => "cpf", "mensagem" => "CPF é obrigatório"];
        } else {
            $cpf = Mask::remove($cpf);
            if (!Validate::cpf($cpf)) {
                $errosCampos[] = ["nome" => "cpf", "mensagem" => "CPF inválido"];
            } else {
                $buscaUsuarioCpf = $this->usuario->select("id")->whereRaw("cpf = '$cpf' AND id <> $usuarioId")->fetch();
                if (!empty($buscaUsuarioCpf)) {
                    $errosCampos[] = ["nome" => "cpf", "mensagem" => "Cpf já existe"];
                }
            }
        }

        /*
        if ($ativo == "") {
            $errosCampos[] = ["nome" => "ativo", "mensagem" => "Ativo é obrigatório"];
        } else {
            $ativo = filter_var($ativo, FILTER_SANITIZE_STRING);
            if (!in_array($ativo, ["S","N"])) {
                $errosCampos[] = ["nome" => "ativo", "mensagem" => "Parâmetro inválido"];
            }
        }
        */

        if (!empty($errosCampos)) {
            return $response->status(400)->send([
                "codigo" => "validacao",
                "campos" => $errosCampos
            ]);
        }

        $updateUsuario = $this->usuario->update($dataUpdate, $usuarioId);
        if ($updateUsuario) {
            return $response->send($dataUpdate);
        }

        return $response->status(500)->send(["erro" => true, "mensagem" => "Falha interna"]);
    }

    public function delete($data) {
    }
}