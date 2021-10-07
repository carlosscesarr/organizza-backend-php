<?php
namespace App\Modules\V1\Models;

use Core\BaseModel;
use Core\Database;

class Categoria extends BaseModel
{
    protected $table = "categoria";
    private $id;
    private $descricao;
    private $ativo;
    private $dataCadastro;
    private $dataAtualizacao;
    private $tipoCategoria;
    protected $con;

    public function __construct()
    {
        $con = Database::getInstance();
    }
}