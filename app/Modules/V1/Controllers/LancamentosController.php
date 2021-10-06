<?php
namespace App\Modules\V1\Controllers;

use Firebase\JWT\JWT;
use App\Modules\V1\Models\Lancamento;
use App\Modules\V1\Models\Categoria;
use Core\Validate;
use Core\Mask;
use Core\Date;

class LancamentosController
{
    public $lancamento = null;

    public function __construct()
    {
        $this->lancamento = new Lancamento();
    }

    public function index($request, $response)
    {
        $maxLimitSearch = 50;
        $startOffset = 0;
        $queryParams = $request->getQueryParams();

        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $_fields = $queryParams["_fields"] ?? "";
        $limit = $queryParams["limit"] ?? $maxLimitSearch;
        $offset = $queryParams["offset"] ?? 0;
        $fieldSort = $queryParams["sort"] ?? "id";
        $sort = $fieldSort[0] == "-" ? "DESC" : "ASC";

        $searchAno = $queryParams["ano"] ?? "";
        $searchMes = $queryParams["mes"] ?? "";

        $searchSituacao = $queryParams["situacao"] ?? "";
        $searchSituacao = "'".implode("', '", explode(",", $searchSituacao)) . "'";

        if ($fieldSort[0] === "-") {
            $fieldSort = substr($fieldSort, 1);
        }

        $limitSearch = $limit != "" ? $limit : $maxLimitSearch;
        $limitSearch = ($limitSearch > $maxLimitSearch) ? $maxLimitSearch : $limitSearch;
        $startOffset = $offset != "" ? $offset : (int)$startOffset;

        $fieldsSearch = $_fields != "" ? $_fields : '*';
        $whereSearch = $searchSituacao != "" ? "AND situacao IN ($searchSituacao)" : "";
        $limiteSql = "LIMIT $startOffset, $limitSearch";
        $sortSql = "ORDER BY $fieldSort $sort";
        $sql = "
        SELECT id, descricao, categoria_id, valor, data_lancamento, situacao, tipo_lancamento, data_cadastro, data_atualizacao
        FROM 
            lancamento
        WHERE
            usuario_id = $usuarioId AND YEAR(data_lancamento) = $searchAno AND MONTH(data_lancamento) = $searchMes
            $whereSearch
        ";

       // echo $sql;
        
        $sqlComLimite = $sql . " " . $sortSql . " ". $limiteSql;
        $sqlSemLimite = $sql;

        $con = \Core\Database::getInstance();
        $queryLancamentosComLimite = $con->query($sqlComLimite);
        $queryLancamentosSemLimite = $con->query($sqlSemLimite);

        $rowsBuscasSemLimite = $queryLancamentosSemLimite->num_rows;
        $rowsBuscasComLimite = $queryLancamentosComLimite->num_rows;

        //$inicio = ($limitSearch * ($startOffset + 1)) - $limitSearch;
        $offsetAtual = (int)$startOffset;

        $totalOffset = ceil($rowsBuscasSemLimite / $limitSearch);
        $lastOffset = ($totalOffset * $limitSearch) - $limitSearch;
        $nextOffset = ($offsetAtual) >= $rowsBuscasSemLimite ? $offsetAtual : ($offsetAtual + $limitSearch);
        $previousOffset = ($offsetAtual) <= 1 ? 1 : ($offsetAtual - $limitSearch);
        $isFirst = $offsetAtual == 0 ? true : false;
        $isLast = $offsetAtual >= ($totalOffset - 1) ? true : false;
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
        
        if (!($queryLancamentosComLimite->num_rows > 0)) {
            return $response->status(404)->send(["message" => "Nenhum registro foi encontrado"]);
        }

        $data["paginacao"] = $paginacao;
        while ($rsLancamento = $queryLancamentosComLimite->fetch_assoc()) {
            $categoria = new Categoria();
            $rsCategoria = $categoria->find($rsLancamento["categoria_id"]);
            $rsLancamento["categoria"] = $rsCategoria;
            $data["data"][] = $rsLancamento;
        }

        $data["balanco"]["total_receita"] = "0.00";
        $data["balanco"]["total_despesa"] = "0.00";
        $data["balanco"]["total_saldo"] = "0.00";

        $sqlTotalReceita = "
        SELECT SUM(valor) as total_receita
        FROM 
            lancamento
        WHERE
            usuario_id = $usuarioId AND YEAR(data_lancamento) = $searchAno AND MONTH(data_lancamento) = $searchMes
            AND situacao = 'RESOLVIDO' AND tipo_lancamento = 'RECEITA'
        ";
        //echo $sqlTotalReceita;
        
        $con = \Core\Database::getInstance();
        $queryTotalReceita = $con->query($sqlTotalReceita);
        if ($queryTotalReceita->num_rows === 1) {
            $totalReceita = $queryTotalReceita->fetch_assoc()["total_receita"];
            if (!is_null($totalReceita)) {
                $data["balanco"]["total_receita"] = (string) $totalReceita;
            }
        }

        $sqlTotalDespesa = "
        SELECT SUM(valor) as total_despesa
        FROM 
            lancamento
        WHERE
            usuario_id = $usuarioId AND YEAR(data_lancamento) = $searchAno AND MONTH(data_lancamento) = $searchMes
            AND situacao = 'RESOLVIDO' AND tipo_lancamento = 'DESPESA'
        ";
        
        $con = \Core\Database::getInstance();
        $queryTotalDespesa = $con->query($sqlTotalDespesa);
        if ($queryTotalDespesa->num_rows === 1) {
            $totalDespesa = $queryTotalDespesa->fetch_assoc()["total_despesa"];
            if (!is_null($totalDespesa)) {
                $data["balanco"]["total_despesa"] = (string) $totalDespesa;
            }
        }

        $totalSaldo = ($data["balanco"]["total_receita"] - $data["balanco"]["total_despesa"]);
        $totalSaldo = number_format($totalSaldo, 2, ".", "");
        $data["balanco"]["total_saldo"] = (string) $totalSaldo;

        $data["balanco"]["total_receita_previsto"] = "0.00";
        $data["balanco"]["total_despesa_previsto"] = "0.00";
        $data["balanco"]["total_saldo_previsto"] = "0.00";

        $sqlTotalReceita = "
        SELECT SUM(valor) as total_receita_previsto
        FROM 
            lancamento
        WHERE
            usuario_id = $usuarioId AND YEAR(data_lancamento) = $searchAno AND MONTH(data_lancamento) = $searchMes
            $whereSearch AND tipo_lancamento = 'RECEITA'
        ";
        //echo $sqlTotalReceita;
        
        $con = \Core\Database::getInstance();
        $queryTotalReceita = $con->query($sqlTotalReceita);
        if ($queryTotalReceita->num_rows === 1) {
            $totalReceita = $queryTotalReceita->fetch_assoc()["total_receita_previsto"];
            if (!is_null($totalReceita)) {
                $data["balanco"]["total_receita_previsto"] = (string) $totalReceita;
            }
        }

        $sqlTotalDespesa = "
        SELECT SUM(valor) as total_despesa
        FROM 
            lancamento
        WHERE
            usuario_id = $usuarioId AND YEAR(data_lancamento) = $searchAno AND MONTH(data_lancamento) = $searchMes
            $whereSearch AND tipo_lancamento = 'DESPESA'
        ";
        
        $con = \Core\Database::getInstance();
        $queryTotalDespesa = $con->query($sqlTotalDespesa);
        if ($queryTotalDespesa->num_rows === 1) {
            $totalDespesa = $queryTotalDespesa->fetch_assoc()["total_despesa"];
            if (!is_null($totalDespesa)) {
                $data["balanco"]["total_despesa_previsto"] = (string) $totalDespesa;
            }
        }

        $totalSaldo = ($data["balanco"]["total_receita_previsto"] - $data["balanco"]["total_despesa_previsto"]);
        $totalSaldo = number_format($totalSaldo, 2, ".", "");
        $data["balanco"]["total_saldo_previsto"] = (string) $totalSaldo;

        return $response->status(200)->send($data);
    }

    public function cadastrar($request, $response)
    {
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $dataInsert = $request->getPostVars();
        $descricao = $dataInsert["descricao"] ?? "";
        $categoriaId = $dataInsert["categoria_id"] ?? "";
        //$periodicidade = $dataInsert["periodicidade"] ?? "";
        $dataLancamento = $dataInsert["data_lancamento"] ?? "";
        $dataVencimento = $dataInsert["data_lancamento"] ?? "";
        $valor = $dataInsert["valor"] ?? "";
        $dataInsert["usuario_id"] = $usuarioId;

        $situacao = $dataInsert["situacao"] ?? ""; //ABERTO, PAGO
        $tipoLancamento = $dataInsert["tipo_lancamento"] ?? ""; //PAGAR, RECEBER

        $errosCampos = [];
        if ($descricao == "") {
            $errosCampos[] = ["nome" => "descricao", "mensagem" => "Descrição é obrigatório"];
        } else {
            $descricao = filter_var($descricao, FILTER_SANITIZE_STRING);
        }
        
        if ($tipoLancamento == "") {
            $errosCampos[] = ["nome" => "tipo_lancamento", "mensagem" => "Tipo lançamento é obrigatório"];
        } else {
            $tipoLancamento = strtoupper($dataInsert["tipo_lancamento"]);
            $dataInsert["tipo_lancamento"] = $tipoLancamento;
            if (!in_array($tipoLancamento, ["DESPESA", "RECEITA"])) {
                $errosCampos[] = ["nome" => "tipo_lancamento", "mensagem" => "Tipo lançamento inválido"];
            }
        }

        if ($situacao == "") {
            $errosCampos[] = ["nome" => "situacao", "mensagem" => "Situação é obrigatório"];
        } else {
            $situacao = strtoupper($dataInsert["situacao"]);
            $dataInsert["situacao"] = $situacao;
            if (!in_array($situacao, ["RESOLVIDO", "PENDENTE"])) {
                $errosCampos[] = ["nome" => "situacao", "mensagem" => "Situação é inválida"];
            }
        }

        if ($categoriaId == "") {
            $errosCampos[] = ["nome" => "categoria_id", "mensagem" => "Categoria é obrigatório"];
        } elseif (!($categoriaId > 0)) {
            $errosCampos[] = ["nome" => "categoria_id", "mensagem" => "Categoria inválida"];
        } else {
            $categoria = new Categoria();
            $rsCategoria = $categoria->select("id")
                ->whereRaw("id = $categoriaId AND usuario_id = $usuarioId AND ativo IN ('S', 'N')")->fetch();
            if (empty($rsCategoria)) {
                $errosCampos[] = ["nome" => "categoria_id", "mensagem" => "Categoria não encontrada"];
            }
    
        }

        if ($valor == "") {
            $errosCampos[] = ["nome" => "valor", "mensagem" => "Valor é obrigatório"];
        } elseif (!($valor > 0)) {
            $errosCampos[] = ["nome" => "valor", "mensagem" => "Valor deve ser maior que 0"];
        } else {
            $dataInsert["valor"] = Mask::valorInteiroParaMoeda($valor);
        }

        if ($dataLancamento == "") {
            $errosCampos[] = ["nome" => "data_lancamento", "mensagem" => "Data lançamento é obrigatório"];
        } elseif (!Validate::date($dataLancamento)) {
            $errosCampos[] = ["nome" => "data_lancamento", "mensagem" => "Data lançamento inválido"];
        }
        
        if (!empty($errosCampos)) {
            $response->status(400)->send([
                "codigo" => "validacao",
                "campos" => $errosCampos
            ]);
        }

        $insertLancamento = $this->lancamento->insert($dataInsert);
        if ($insertLancamento) {
            $dataInsert["id"] = $this->lancamento->lastInsertId();
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

    public function marcarComoPendente($request, $response)
    {
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $queryParams = $request->getQueryParams();
        $lancamentoId = $queryParams["id"] ?? "";
        $lancamentoId = filter_var($lancamentoId, FILTER_SANITIZE_NUMBER_INT);
        if ($lancamentoId == "") {
            return $response->status(404)->send(["mensagem" => "Lançamento id inválido"]);
        }

        $rsLancamento = $this->lancamento->select("id")->whereRaw("id = $lancamentoId AND usuario_id = $usuarioId")->fetch();
        if (empty($rsLancamento)) {
            return $response->status(404)->send(["mensagem" => "Lançamento não encontrado"]);
        }
        $dataHoraAtual = date("Y-m-d H:i:s");
        $updateSituacaoLancamento = $this->lancamento->update(["situacao" => "PENDENTE", "data_atualizacao" => $dataHoraAtual], $lancamentoId);
        if (!$updateSituacaoLancamento) {
            return $response->status(500)->send(["mensagem" => "Erro interno"]);
        }

        return $response->status(200)->send([]);
    }

    public function marcarComoResolvido($request, $response)
    {
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $queryParams = $request->getQueryParams();
        $lancamentoId = $queryParams["id"] ?? "";
        $lancamentoId = filter_var($lancamentoId, FILTER_SANITIZE_NUMBER_INT);
        if ($lancamentoId == "") {
            return $response->status(404)->send(["mensagem" => "Lançamento id inválido"]);
        }

        $rsLancamento = $this->lancamento->select("id")->whereRaw("id = $lancamentoId AND usuario_id = $usuarioId")->fetch();
        if (empty($rsLancamento)) {
            return $response->status(404)->send(["mensagem" => "Lançamento não encontrado"]);
        }
        
        $dataHoraAtual = date("Y-m-d H:i:s");
        $updateSituacaoLancamento = $this->lancamento->update(["situacao" => "RESOLVIDO", "data_atualizacao" => $dataHoraAtual], $lancamentoId);
        if (!$updateSituacaoLancamento) {
            return $response->status(500)->send(["mensagem" => "Erro interno"]);
        }

        return $response->status(200)->send([]);
    }

    public function deletar($request, $response)
    {
        $headers = $request->getHeaders();
        $jwt = isset($headers["Authorization"]) ? str_replace("Bearer ", "", $headers["Authorization"]) : "";
        $key = "lili9090";
        $decoded = (array) JWT::decode($jwt, $key, array('HS256'));
        $usuarioId = $decoded["id"];

        $queryParams = $request->getQueryParams();
        $lancamentoId = $queryParams["id"] ?? "";
        $lancamentoId = filter_var($lancamentoId, FILTER_SANITIZE_NUMBER_INT);
        if ($lancamentoId == "") {
            return $response->status(404)->send(["mensagem" => "Lançamento id inválido"]);
        }

        $rsLancamento = $this->lancamento->select("id")->whereRaw("id = $lancamentoId AND usuario_id = $usuarioId")->fetch();
        if (empty($rsLancamento)) {
            return $response->status(404)->send(["mensagem" => "Lançamento não encontrado"]);
        }
        $dataHoraAtual = date("Y-m-d H:i:s");
        $updateSituacaoLancamento = $this->lancamento->update(["situacao" => "CANCELADO", "data_atualizacao" => $dataHoraAtual], $lancamentoId);
        if (!$updateSituacaoLancamento) {
            return $response->status(500)->send(["mensagem" => "Erro interno"]);
        }

        return $response->status(200)->send([]);
    }
}