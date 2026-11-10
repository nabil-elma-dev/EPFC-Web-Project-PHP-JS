<?php

require_once "framework/Controller.php";
require_once "framework/Configuration.php";
require_once "framework/Tools.php";

class ControllerSetup extends Controller {

    /** Importe la base. */
    public function index(): void {
        $this->install();
    }

    /** Crée le schéma et importe les données. Vide aussi la session. */
    public function install(): void {
        $_SESSION = [];
        session_destroy();
        echo "<p>Importing data...</p>";
        try {
            $webroot = Configuration::get("web_root");
            $dbtype = Configuration::get("dbtype");
            $dbhost = Configuration::get("dbhost");
            $dbname = Configuration::get("dbname");
            $dbuser = Configuration::get("dbuser");
            $dbpassword = Configuration::get("dbpassword");
            $pdo = new PDO("{$dbtype}:host={$dbhost};charset=utf8mb4", $dbuser, $dbpassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = file_get_contents("database/{$dbname}.sql");
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute()) {
                echo "<p>The database schema was created.</p>";
            } else {
                echo "<p>Could not create the database schema.</p>";
            }

            if (file_exists("database/{$dbname}_dump.sql")) {
                $sql = file_get_contents("database/{$dbname}_dump.sql");
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute()) {
                    echo "<p>Data imported.</p>";
                } else {
                    echo "<p>Could not import data.</p>";
                }
            }
            echo "<a href='" . e($webroot) . "'>Back to index</a>";
        } catch (Exception $exc) {
            throw new Exception("Database access error: " . $exc->getMessage());
        }
    }

    /** Exporte les données vers un fichier SQL. */
    public function export(): void {
        echo "<p>Exporting data...</p>";
        $mysql_path = Configuration::get("mysql_path");
        $webroot = Configuration::get("web_root");
        $dbhost = Configuration::get("dbhost");
        $dbname = Configuration::get("dbname");
        $dbuser = Configuration::get("dbuser");
        $dbpassword = Configuration::get("dbpassword");

        $file = dirname(__FILE__) . "/../database/{$dbname}_dump.sql";

        $output = [];
        exec("{$mysql_path}mysqldump --no-create-info --user={$dbuser} --password={$dbpassword} --host={$dbhost} {$dbname} --result-file={$file} 2>&1", $output);
        if (count($output) == 0) {
            echo "<p>Data was exported to `<code>" . e($file) . "</code>`</p>";
        } else {
            throw new Exception(json_encode($output));
        }
        echo "<a href='" . e($webroot) . "'>Back to index</a>";
    }
}
