<?php
namespace Core;

Class Mask
{
    public static function remove($v)
    {
        return self::delete($v);
    }

    public static function delete($v)
    {
        $v = str_replace(array(".", ",", "/", "\\", "-", ")", "(", "_", "", " "), "", $v);
        return $v;
    }

    public static function moeda($v, $not_null = true)
    {
        if (empty($v) && $not_null) {
            $v = "00";
            $v = number_format($v, 2, ',', '.');
            return $v;
        }

        $v = number_format($v, 2, ',', '.');
        return $v;
    }

    public static function adjustMoeda($v, $decimalNumber = 2)
    {
        if (empty($v)) {
            return intval(0);
        }

        $v = str_replace(".", "", $v);
        $v = str_replace(",", ".", $v);
        $v = number_format($v, 2);
        return $v;
    }

    public static function telefone($v)
    {
        if (empty($v)) { return "";
        }

        $v = "(". @substr($v, 0, 2) .") ". @substr($v, 2);
        $v = @substr($v, 0, 9) ."-". @substr($v, 9);
        return $v;
    }

    public static function valorInteiroParaMoeda($valor)
    {
        if ($valor != "") {
            $valor_decimal = substr($valor, -2);
            $valor = substr($valor, 0, -2);
            $valor = $valor . "." . $valor_decimal;
            $valor = number_format($valor, 2 , ".", "");
            return $valor;
        }

        return false;
    }

    public static function celular($v)
    {
        if (empty($v)) { return "";
        }
        
        if (strlen($v)==11) {

            $v1 = "(". @substr($v, 0, 2) .") ";// (00)
            $v2 = @substr($v, 2, 5);// 00000
            $v3 = "-".@substr($v, 7, 4);// -0000
            $v  = $v1.$v2.$v3;
        
        } elseif(strlen($v)==10) {

            $v1 = "(". @substr($v, 0, 2) .") ";
            $v2 = @substr($v, 2, 4);
            $v3 = "-".@substr($v, 6, 4);
            $v  = $v1.$v2.$v3;

        }
        //echo "entrou no if ";
        return $v;
    }

    public static function cpf($v)
    {
        if (empty($v)) { return "";
        }

        $v = @substr($v, 0, 3) .".". @substr($v, 3);
        $v = @substr($v, 0, 7) .".". @substr($v, 7);
        $v = @substr($v, 0, 11) ."-". @substr($v, 11);
        return $v;
    }

    public static function cnpj($v)
    {
        if (empty($v)) { return "";
        }

        $v = @substr($v, 0, 3) .".". @substr($v, 3);
        $v = @substr($v, 0, 7) .".". @substr($v, 7);
        $v = @substr($v, 0, 11) ."/". @substr($v, 11);
        $v = @substr($v, 0, 16) ."-". @substr($v, 16);
        return $v;
    }

    public static function cpfCnpj($value)
    {
        if (empty($value)) { return "";
        }
        
        if (strlen($value) == 11) {
            return self::cpf($value);

        } elseif (strlen($value) == 14) {
            return self::cnpj($value);
        
        } else {
            return $value;
        }

    }

    public static function cep($v)
    {
        if (empty($v)) { return "";
        }

        $v = @substr($v, 0, 5) ."-". @substr($v, 5);
        return $v;
    }

    /*
    public static function date($v) {
        if (empty($v)) return "";

        $v = @substr($v, 0, 2) ."/". @substr($v, 2);
        $v = @substr($v, 0, 5) ."/". @substr($v, 5);
        return $v;
    }
    */

    /**
     * @description: m�todo repons�vel por receber uma string e criar outra string com a quantidade de caracteres a serem mascarados com ***
     */
    public static function stringSecutityLoop($count = 0, $string_repetir = ""){
        $string = "";
        if ($count > 0 && $string_repetir != "") {
            for ($i = 0; $i<=$count; $i++) {
                $string .= "*";
            }
            return $string;
        }
        return "";
    }
    /**
     * @description: m�todo repons�vel por receber uma string e mascarar dinamicamente com **
     */
    public static function stringSecutiry(?string $string = "")
    {
        $strlen_string = strlen($string);
        $array_string = str_split($string);
        $count = count($array_string);
        $mask = "";
        if ($count > 5) {
            $novo_nome_email = $array_string[0].$array_string[1];
            $novo_nome_email_fim = $array_string[$count - 2].$array_string[$count - 1];
            $mask = self::stringSecutityLoop($count - 4, "*");
            $string = $novo_nome_email.$mask.$novo_nome_email_fim;

        } elseif($count == 5) {
            $novo_nome_email = $array_string[0];
            $novo_nome_email_fim = $array_string[$count - 1];
            $mask = self::stringSecutityLoop($count - 2, "*");
            $string = $novo_nome_email.$mask.$novo_nome_email_fim;
        
        } elseif($count > 2) {
            $novo_nome_email = $array_string[0];
            $novo_nome_email_fim = $array_string[$count - 1];
            $mask = self::stringSecutityLoop($count - 2, "*");
            $string = $novo_nome_email.$mask;

        } elseif($count == 2) {
            $novo_nome_email = $array_string[0];
            $novo_nome_email_fim = $array_string[$count - 1];
            $string = $novo_nome_email."*";
        } else {
            $string = "*";
        }
        return $string;
    }

    public static function securityEmail(?string $email = "")
    {
        if ($email != "" || !is_null($email)) {
            list($nome_email, $email_host_) = explode("@",$email);
            list($email_host) = explode(".",$email_host_);
            $email_fim = str_replace($email_host, "", $email_host_);
            $nome_email_mask = self::stringSecutiry($nome_email);
            $email_host_mask = self::stringSecutiry($email_host);
    
            return $nome_email_mask."@".$email_host_mask.$email_fim;
        }
    
        return "";
    }

}