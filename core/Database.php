<?php
namespace Core;

use Mysqli;
use Exception;
use App\Config;

class Database
{
     /**
      * 
      *
      * @var Mysqli
      */
    private static $con = null;
    /**
     * 
     *
     * @var ErrorCconnection
     */
    private static $error;

    public static function getInstance()
    {
        if (is_null(self::$con)) {
            try {
                self::$con = new Mysqli(
                    Config::$dbHost,
                    Config::$dbUser,
                    Config::$dbPassword,
                    Config::$dbBank
                );
                if (!self::$con) {
                    exit;
                }
                self::$con->set_charset(Config::$dbCharset);
            } catch (Exception $e) {
                self::$error = $e->getMessage();
                exit;
            }
        }
        return self::$con;
    }

    public static function getError()
    {
        return self::$error;
    }
}
