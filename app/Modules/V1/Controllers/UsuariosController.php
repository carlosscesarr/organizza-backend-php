<?php
namespace App\Modules\V1\Controllers;

use Firebase\JWT\JWT;
use App\Modules\V1\Models\Usuario;
use Core\Validate;
use Core\Mask;

class UsuariosController
{
    public $usuario = null;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    public function cadastrar($request, $response)
    {
        $dataInsert = $request->getPostVars();
        $nome = $dataInsert["nome"] ?? "";
        $email = $dataInsert["email"] ?? "";
        $senha = $dataInsert["senha"] ?? "";
        $ativo = $dataInsert["ativo"] ?? "";

        $errosCampos = [];
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
            $buscaUsuarioEmail = $this->usuario->select("id")->whereRaw("email = '$email'")->fetch();
            if (!empty($buscaUsuarioEmail)) {
                $errosCampos[] = ["nome" => "email", "mensagem" => "Email já existe"];
            }
        }

        if ($senha == "") {
            $errosCampos[] = ["senha" => "senha", "mensagem" => "Senha é obrigatório"];
        }
       
        if (!empty($errosCampos)) {
            $response->status(400)->send([
                "codigo" => "validacao",
                "campos" => $errosCampos
            ]);
        }

        $dataInsert["senha"] = hash("sha256", $senha);
        $insertUsuario = $this->usuario->insert($dataInsert);
        if ($insertUsuario) {
            $dataInsert["id"] = $this->usuario->lastInsertId();
            return $response->status(201)->send($dataInsert);
        }

        return $response->status(500)->send(["erro" => true, "mensagem" => "Falha interna"]);
    }

    public function visualizar($request, $response)
    {  
        $data = $request->getQueryParams();
        $categoria_id = $data["id"] ?? 0;
        $categoria_id = filter_var($categoria_id, FILTER_SANITIZE_NUMBER_INT);
        if (!($categoria_id > 0)) {
            return $response->status(400)->send(["mensagem" => "Parâmetro de busca inválido"]);
        }

        $data = $this->categoria->select()->whereRaw("id = $categoria_id AND ativo IN ('S', 'N')")->fetch();

        if (empty($data)) {
            return $response->status(404)->send(["mensagem" => "Nenhum registro foi encontrado"]);
        }

        return $response->send($data);
    }

    public function editar($request, $response)
    {
        $errosCampos = [];
        $queryParams = $request->getQueryParams();
        $data = $request->getPostVars();
        $id = $queryParams["id"] ?? 0;
        
        $dataUpdate = $data;
        $dataUpdate["data_atualizacao"] = date("Y-m-d H:i:s");
        $descricao = $dataUpdate["descricao"] ?? "";
        $ativo = $dataUpdate["ativo"] ?? "";
        
        // Verificar se o produto existe
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $rsCategoria = $this->categoria->select()
            ->where("id", "=", $id)
            ->fetch();

        if (empty($rsCategoria)) {
            return $response->status(404)->send([
                "codigo" => "recurso_nao_encontrado",
                "mensagem" => "Desculpe, a categoria (id:) que você está tentando acessar não existe ou foi excluído"
            ]);
        }

        if ($descricao == "") {
            $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição é obrigatório"];
        } else {
            $descricao = filter_var($descricao, FILTER_SANITIZE_STRING);
            $buscaCategoriaPelaDescricao = $this->categoria->select("descricao")
                ->whereRaw("descricao = '$descricao' AND id <> $id")
                ->fetch();
            if (!empty($buscaCategoriaPelaDescricao)) {
                $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição da categoria já existe"];
            }
        }

        if ($ativo == "") {
            $errosCampos[] = ["nome" => "ativo", "mensagem" => "Ativo é obrigatório"];
        } else {
            $ativo = filter_var($ativo, FILTER_SANITIZE_STRING);
            if (!in_array($ativo, ["S","N"])) {
                $errosCampos[] = ["nome" => "ativo", "mensagem" => "Parâmetro inválido"];
            }
        }

        if (!empty($errosCampos)) {
            return $response->status(400)->send([
                "codigo" => "validacao",
                "campos" => $errosCampos
            ]);
        }

        $updateCategoria = $this->categoria->update($dataUpdate, $id);
        if ($updateCategoria) {
            return $response->send($dataUpdate);
        }

        return $response->status(500)->send(["erro" => true, "mensagem" => "Falha interna"]);
    }
}