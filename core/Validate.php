<?php
namespace Core;

use DateTime;

class Validate
{

    public static function url(string $url = ""): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        } 

        return false;
    }

    public static function cpf(string $cpf = ""): bool
    {         
        $nulos = array("12345678909","11111111111","22222222222","33333333333","44444444444","55555555555","66666666666","77777777777","88888888888","99999999999","00000000000");

        $cpf = str_replace(array(".", ",", "/", "\\", "-", ")", "(", "_", "", " "), "", $cpf);
    
        if (empty($cpf) || !is_numeric($cpf) || strlen($cpf) < 11 || strlen($cpf) > 11 || in_array($cpf, $nulos)) { return false;
        }
    
        $acum = 0;
        for ($i = 0; $i < 9; $i++) {
            $acum += $cpf[$i] * (10-$i);
        }
    
        $x = $acum % 11;
        $acum = ($x>1) ? (11 - $x) : 0;
    
        // RETORNA FALSO SE O DIGITO CALCULADO EH DIFERENTE DO PASSADO NA STRING
        if ($acum != $cpf[9]) { return false;
        }
    
        // CALCULA O �LTIMO D�GITO VERIFICADOR
        $acum = 0;
        for ($i = 0; $i < 10; $i++){
            $acum += $cpf[$i] * (11-$i);
        }
    
        $x = $acum % 11;
        $acum = ($x > 1) ? (11-$x) : 0;
    
        // RETORNA FALSO SE O DIGITO CALCULADO EH DIFERENTE DO PASSADO NA STRING
        if ($acum != $cpf[10]) { return false;
        }
    
        return true;
    }

    /**
     * Link: https://gist.github.com/guisehn/3276302
     * @param cnpj string
     */
    public static function cnpj(string $cnpj = ""): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
    
        // Verifica se todos os digitos s�o iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;	
    
        // Valida primeiro d�gito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
    
        $resto = $soma % 11;
    
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;
    
        // Valida segundo d�gito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
    
        $resto = $soma % 11;
    
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    public static function email(string $email = ""): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    public static function date(string $date = "")
    {
        if (empty($date)) { return false;
        }
        //echo $date;

        $explode = "";
        if (!is_string($date)) { return false;
        }

        if (strpos($date, "/")) {
            $explode = "/";
        } elseif (strpos($date, "-")) {
            $explode = "-";
        }

        if ($explode != "/" && $explode != "-") { return false;
        }

        $date = explode("{$explode}", $date);
        if (empty($date)) { return false;
        }
        
        if ($explode == "/") {
            if (empty($date[1]) || empty($date[0]) || empty($date[2])) { return false;
            }

            if (!checkdate($date[1], $date[0], $date[2])) { return false;
            }
        } elseif ($explode == "-") {
            if (empty($date[1]) || empty($date[2]) || empty($date[0])) { return false;
            }

            if (!checkdate($date[1], $date[2], $date[0])) { return false;
            }
        }
    
        return true;

    }

    // M�todo checa se uma data est� no formato fornecido
    public static function formatDate($date, string $format = 'd/m/Y'): bool
    {   
        if (empty($date) && empty($format)) {
            return false;
        }

        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

