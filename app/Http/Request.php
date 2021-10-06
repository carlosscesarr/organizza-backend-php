<?php 

namespace App\Http;

class Request{
    private $router;

    /**
     * Metodo HTTP da requisição
     * @var string
     */
    private $httpMethod;

    /**
     * URI da pagina
     * @var string
     */
    private $uri;

    /**
     * Parametros da URL ($_GET)
     * @var array
     */
    private $queryParamans = [];

    /**
     * Variaveis recebidas no POST da pagina ($_POST)
     * @var array
     */
    private $postVars = [];

    /**
     * Cabeçalho da requisição
     * @var array 
     */ 
    private $headers;


    public function __construct($router)
    {
        $this->router = $router;
        $this->queryParamans    = $_GET ?? [];
        $this->headers          = getallheaders();
        $this->httpMethod       = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->setUri();
        $this->setPostVars();
    }

    public function setPostVars()
    {
        if ($this->httpMethod == "GET") return false;
        $this->postVars = $_POST ?? [];

        $inputRaw = file_get_contents("php://input");
        $this->postVars = (strlen($inputRaw) && empty($_POST)) ? json_decode($inputRaw, true) : $this->postVars;
    }

    private function setUri() {
        $this->uri = $_SERVER['REQUEST_URI'] ?? '';
        $xURI = explode("?", $this->uri);
        $this->uri = $xURI[0];
    }

    public function getRouter() {
        return $this->router;
    }

    /**
     * Metodo responsavel por retornar metodo http da requisição
     * @return string
     */
    public function getHttpMethod() {
        return $this->httpMethod;
    }



    /**
     * Metodo responsavel por retornar uri da requisição
     * @return string
     */
    public function getUri() {
        return $this->uri;
    }



    /**
     * Metodo responsavel por retornar parametros da requisição
     * @return string
     */
    public function getQueryParams() {
        return $this->queryParamans;
    }

    /**
     * Metodo responsavel por retornar parametros da requisição
     * @return string
     */
    public function setQueryParams($params) {
        $this->queryParamans = $params;
    }

    /**
     * Metodo responsavel por retornar variaveis recebidas no post da requisição
     * @return string
     */
    public function getPostVars() {
        return $this->postVars;
    }

/**
     * Metodo responsavel por retornar headers da requisição
     * @return string
     */
    public function getHeaders() {
        return $this->headers;
    }
}