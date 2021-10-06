<?php
namespace App;

class Config
{
    public static $dbHost     = "";
    public static $dbUser     = "";
    public static $dbPassword = "";
    public static $dbBank     = "";
    public static $dbPrefix   = '';
    public static $dbCharset  = 'utf8';

    public static $appFolder       = "";
    public static $siteFrontUrl    = "";
    public static $baseUrl         = "";
    public static $siteAssets      = "";
    public static $timezone        = "America/Fortaleza";
    
    public static $displayErrors   = true;

    public static function init()
    {
        # Settings for database localhost
        self::$dbHost     = "localhost";
        self::$dbUser     = "root";
        self::$dbPassword = "";
        self::$dbBank     = "organizza";
        self::$dbPrefix   = '';
        self::$dbCharset  = 'utf8';
        
        self::$appFolder       = "organizza";
        self::$baseUrl         = "http://localhost/" . self::$appFolder;
        self::$siteAssets      = self::$baseUrl . "/public";
        
        self::$displayErrors = true;
    }
}