<?php
session_start();

class Database {
    private static ?Database $instance = null;
    private const PARAM_HOST = "mysql-recabet.alwaysdata.net";
    private const PARAM_DB = "recabet_hwp";
    private const PARAM_USER = "recabet";
    private const PARAM_PASSWD = "Receb.2005";
    private const PARAM_PORT = 3306;
    private PDO $pdo;

    private function __construct() {
        $dsn = 'mysql:host=' . self::PARAM_HOST . ';port=' . self::PARAM_PORT . ';dbname=' . self::PARAM_DB;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10,
        ];

        try {

            $this->pdo = new PDO($dsn, self::PARAM_USER, self::PARAM_PASSWD, $options);
            $this->pdo->exec("SET NAMES 'utf8'");
        } catch (PDOException $ex) {
            error_log("Error: " . $ex->getMessage() . " Code: " . $ex->getCode(), 3, "/var/log/php-errors.log");
            exit("Database connection error. Please try again later.");
        }
    }

    private function __clone() {}

    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

}
