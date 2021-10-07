<?php
namespace App\Modules\V1\Models;

use Core\BaseModel;

class Usuario extends BaseModel
{
    protected $table = "usuario";
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $ativo;
    private $dataCadastro;
    private $dataAtualizacao;
    
    public function __construct()
    {
        
    }

    public function gerarCategoriasPadrao()
    {
        $categoria = new Categoria();
    }
}