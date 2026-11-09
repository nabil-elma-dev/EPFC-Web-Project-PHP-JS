<?php

declare(strict_types=1);

require_once 'Configuration.php';

abstract class Model {

    private static ?PDO $pdo = null;

    /** Ouvre la connexion PDO en utf8mb4, ou renvoie celle déjà ouverte. */
    private static function connect(): PDO {
        try {
            if (self::$pdo === null) {
                $dbtype = Configuration::get("dbtype");
                $dbhost = Configuration::get("dbhost");
                $dbname = Configuration::get("dbname");
                $dsn = "{$dbtype}:host={$dbhost};dbname={$dbname};charset=utf8mb4";
                $dbuser = Configuration::get("dbuser");
                $dbpassword = Configuration::get("dbpassword");

                self::$pdo = new PDO($dsn, $dbuser, $dbpassword);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->query("SET sql_mode=(SELECT CONCAT(@@sql_mode,',ONLY_FULL_GROUP_BY'));");
            }
            return self::$pdo;
        } catch (PDOException $e) {
            throw new Exception("PDO connection error : " . $e->getMessage(), previous: $e);
        }
    }

    /** Prépare et exécute une requête, avec les paramètres nommés. */
    protected static function execute(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("PDO query error : " . $e->getMessage(), previous: $e);
        }
    }

    /** Renvoie l'identifiant de la dernière insertion. */
    protected static function last_insert_id(): int {
        return (int) self::connect()->lastInsertId();
    }

}
