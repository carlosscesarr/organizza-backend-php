<?php

namespace App\Modules\V1\Controllers;

use Firebase\JWT\JWT;
use App\Modules\V1\Models\Categoria;
use Core\Database;

class CategoriasController
{
    public $categoria = null;

    public function __construct()
    {
        $this->categoria = new Categoria();
    }

    public function index($request, $response)
    {
        $maxLimitSearch = 50;
        $startOffset = 0;

        $search = $_GET["search"] ?? "";
        $_fields = $_GET["_fields"] ?? "";
        $limit = $_GET["limit"] ?? $maxLimitSearch;
        $offset = $_GET["offset"] ?? 0;
        $fieldSort = $_GET["sort"] ?? "id";
        $sort = $fieldSort[0] == "-" ? "DESC" : "ASC";

        if ($fieldSort[0] == "-") {
            $fieldSort = substr($fieldSort, 1);
        }

        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];
        //const { user } = req.headers;

        $limitSearch = $limit != "" ? $limit : $maxLimitSearch;
        $limitSearch = ($limitSearch > $maxLimitSearch) ? $maxLimitSearch : $limitSearch;
        $searchComplement = ($search && $search != '') ? "AND nome LIKE '%$search%'" : "";
        $startOffset = $offset != "" ? $offset : (int)$startOffset;

        $fieldsSearch = $_fields != "" ? $_fields : '*';
        $limiteSql = "LIMIT $startOffset, $limitSearch";
        $sortSql = "ORDER BY $fieldSort $sort";
        $sql = "
        SELECT $fieldsSearch
        FROM categoria WHERE usuario_id = $usuarioId $searchComplement
        ";

        $sqlComLimite = $sql . " " . $sortSql . " ". $limiteSql;
        $sqlSemLimite = $sql;

        $con = \Core\Database::getInstance();
        $queryCategoriasComLimite = $con->query($sqlComLimite);
        $queryCategoriasSemLimite = $con->query($sqlSemLimite);

        $rowsBuscasSemLimite = $queryCategoriasSemLimite->num_rows;
        $rowsBuscasComLimite = $queryCategoriasComLimite->num_rows;

        //$inicio = ($limitSearch * ($startOffset + 1)) - $limitSearch;
        $offsetAtual = (int)$startOffset;

        $totalOffset = ceil($rowsBuscasSemLimite / $limitSearch);
        $lastOffset = ($totalOffset * $limitSearch) - $limitSearch;
        $nextOffset = ($offsetAtual) >= $rowsBuscasSemLimite ? $offsetAtual : ($offsetAtual + $limitSearch);
        $previousOffset = ($offsetAtual) <= 1 ? 1 : ($offsetAtual - $limitSearch);
        $isFirst = $offsetAtual == 0 ? true : false;
        $isLast = $offsetAtual >= $totalOffset ? true : false;
        //$last = $limitSearch * 
        $paginacao = [
            "first_offset" => 0,
            "last_offset" => $lastOffset,
            "previous_offset" => $previousOffset,
            "next_offset" => $nextOffset,
            "current_offset" => $offsetAtual,
            "total_offset" => $totalOffset,
            "is_first" => $isFirst,
            "is_last" => $isLast,
            "query_count" => $rowsBuscasComLimite,
            "total_count" => $rowsBuscasSemLimite
        ];
        
        if (!($queryCategoriasComLimite->num_rows > 0)) {
            return $response->status(404)->send(["message" => "Nenhum registro foi encontrado"]);
        }

        $data["paginacao"] = $paginacao;
        while ($rsCategoria = $queryCategoriasComLimite->fetch_assoc()) {
            $data["data"][] = $rsCategoria;
        }

        return $response->status(200)->send($data);
    }

    public function cadastrar($request, $response)
    {
        $dataInsert = $request->getPostVars();
        $dataInsert["data_cadastro"] = date("Y-m-d H:i:s");
        $descricao = $dataInsert["descricao"] ?? "";
        $tipoCategoria = $dataInsert["tipo_categoria"] ?? "";
        $ativo = $dataInsert["ativo"] ?? "";

        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $errosCampos = [];
        if ($descricao == "") {
            $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição é obrigatório"]; 
        } else {
            $descricao = filter_var($descricao, FILTER_SANITIZE_STRING);
            $buscaProdutoPelaDescricao = $this->categoria->select("descricao")
                ->whereRaw("descricao = '$descricao' AND usuario_id = $usuarioId AND tipo_categoria = '$tipoCategoria'")->fetch();
            if (!empty($buscaProdutoPelaDescricao)) {
                $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição da categoria já existe"];
            }
        }

        if ($tipoCategoria == "") {
            $errosCampos[] = ["nome" => "tipo", "mensagem" => "Tipo é obrigatório"]; 
        } else {
            $tipoCategoriaCapslock = strtoupper($tipoCategoria);
            $dataInsert["tipo_categoria"] = $tipoCategoriaCapslock;
            if (!in_array($tipoCategoriaCapslock, ["DESPESA", "RECEITA"])) {
                $errosCampos[] = ["nome" => "tipo", "mensagem" => "Tipo inválido"];
            }
        }

        if ($ativo == "") {
            $errosCampos[] = ["nome" => "ativo", "mensagem" => "Ativo é obrigatório"];
        } else {
            $ativoCapslock = strtoupper($ativo);
            $dataInsert["ativo"] = $ativoCapslock;
            if (!in_array($ativoCapslock, ["S","N"])) {
                $errosCampos[] = ["nome" => "ativo", "mensagem" => "Parâmetro inválido"];
            }
        }
        
        if (!empty($errosCampos)) {
            $response->status(404)->send([
                "codigo" => "validacao",
                "campos" => $errosCampos
            ]);
        }

        $dataInsertComUsuario = $dataInsert;
        $dataInsertComUsuario["usuario_id"] = $usuarioId;
        $insertCategoria = $this->categoria->insert($dataInsertComUsuario);
        if ($insertCategoria) {
            $dataInsert["id"] = $this->categoria->lastInsertId();
            return $response->status(201)->send($dataInsert);
        }
        return $response->status(500)->send(["erro" => true, "mensagem" => "Falha interna"]);
    }

    public function visualizar($request, $response)
    {
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];
        
        $data = $request->getQueryParams();
        $categoria_id = $data["id"] ?? 0;
        $categoria_id = filter_var($categoria_id, FILTER_SANITIZE_NUMBER_INT);
        if (!($categoria_id > 0)) {
            return $response->status(400)->send(["mensagem" => "Parâmetro de busca inválido"]);
        }

        $data = $this->categoria->select()->whereRaw("id = $categoria_id AND usuario_id = $usuarioId AND ativo IN ('S', 'N')")->fetch();

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
        $usuarioId = $decoded["id"];

        $errosCampos = [];
        $queryParams = $request->getQueryParams();
        $data = $request->getPostVars();
        $id = $queryParams["id"] ?? 0;
        
        $dataUpdate = $data;
        $dataUpdate["data_atualizacao"] = date("Y-m-d H:i:s");
        $descricao = $dataUpdate["descricao"] ?? "";
        $tipoCategoria = $dataUpdate["tipo_categoria"] ?? "";
        $ativo = $dataUpdate["ativo"] ?? "";
        
        // Verificar se o produto existe
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $rsCategoria = $this->categoria->select()
            ->whereRaw("id = $id AND usuario_id = $usuarioId")
            ->fetch();

        if (empty($rsCategoria)) {
            return $response->status(400)->send([
                "codigo" => "recurso_nao_encontrado",
                "mensagem" => "Desculpe, a categoria (id:) que você está tentando acessar não existe ou foi excluído"
            ]);
        }

        if ($descricao == "") {
            $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição é obrigatório"];
        } else {
            $descricao = filter_var($descricao, FILTER_SANITIZE_STRING);
            $buscaCategoriaPelaDescricao = $this->categoria->select("descricao")
                ->whereRaw("descricao = '$descricao' AND id <> $id AND usuario_id <> $usuarioId AND tipo_categoria <> '$tipoCategoria'")
                ->fetch();
            if (!empty($buscaCategoriaPelaDescricao)) {
                $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição da categoria já existe"];
            }
        }

        if ($tipoCategoria == "") {
            $errosCampos[] = ["nome" => "tipo", "mensagem" => "Tipo é obrigatório"]; 
        } else {
            $tipoCategoriaCapslock = strtoupper($tipoCategoria);
            $dataUpdate["tipo_categoria"] = $tipoCategoriaCapslock;
            if (!in_array($tipoCategoriaCapslock, ["DESPESA", "RECEITA"])) {
                $errosCampos[] = ["nome" => "tipo", "mensagem" => "Tipo inválido"];
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

    public function delete($data) {

    }
}
