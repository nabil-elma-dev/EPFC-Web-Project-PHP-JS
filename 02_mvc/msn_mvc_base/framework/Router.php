<?php

declare(strict_types=1);

require_once 'Controller.php';
require_once 'Configuration.php';
require_once 'Tools.php';

class Router {

    /** Applique trim à chaque chaîne, y compris dans les tableaux imbriqués. */
    private function trim_all_array(array $array): array {
        $copy = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $copy[$key] = $this->trim_all_array($value);
            } elseif (is_string($value)) {
                $copy[$key] = trim($value);
            } else {
                $copy[$key] = $value;
            }
        }
        return $copy;
    }

    /** Retire les espaces des paramètres GET et POST. N'échappe pas le HTML. */
    private function trim_all_input(): void {
        $_GET = $this->trim_all_array($_GET);
        $_POST = $this->trim_all_array($_POST);
    }

    /** Instancie le contrôleur demandé, ou le contrôleur par défaut. */
    private function get_controller(): Controller {
        $controller_name = $_GET["controller"] ?? "";
        if ($controller_name === "") {
            $controller_name = Configuration::get("default_controller");
        }

        $parts = preg_split('/_+/', $controller_name);
        $parts = array_map(function($p) { return ucfirst(strtolower($p)); }, $parts);
        $controller_class_name = 'Controller' . implode('', $parts);

        $filename = "controller/$controller_class_name.php";
        
        if (file_exists($filename)) {
            require_once "controller/$controller_class_name.php";
            if (class_exists($controller_class_name)) {
                return new $controller_class_name();
            }
        }
        throw new Exception("Controller '$controller_name' doesn't exist");

    }

    /** Appelle l'action demandée, ou index. */
    private function call_action(Controller $controller): void {
        $action_name = strtolower($_GET["action"] ?? "");
        if ($action_name === "") {
            $action_name = "index";
        }

        if (is_callable([$controller, $action_name])) {
            $controller->$action_name();
        } else {
            throw new Exception("Action '$action_name' doesn't exist in this controller.");
        }
    }

    /** Bloque le JavaScript, sauf les chemins listés dans enabled_paths. */
    private function block_javascript(): void {
        if (Configuration::get("disable_js", false)) {
            $protocol = ($_SERVER["HTTPS"] ?? "") === "on" ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $web_root = Configuration::get("web_root");
            $base_url = $protocol . '://' . $host . $web_root;
            $enabled_paths_config = Configuration::get("enabled_paths", false);
            
            $paths_array = [];
            if ($enabled_paths_config) {
                if (is_array($enabled_paths_config)) {
                    $paths_array = $enabled_paths_config;
                } else {
                    $enabled_paths = trim($enabled_paths_config);
                    if ($enabled_paths) {
                        $paths_array = preg_split("/[\s;:]+/", $enabled_paths);
                    }
                }
            }
            
            if (!empty($paths_array)) {
                $enabled_paths = implode(" ", array_map(function ($p) use ($base_url) {
                    $path = trim($p);
                    if (str_starts_with($path, "https://") || str_starts_with($path, "http://")) {
                        return $path;
                    }
                    return "{$base_url}{$path}";
                }, $paths_array));
            } else {
                $enabled_paths = "'none'";
            }
            header("Content-Security-Policy: script-src $enabled_paths;");
        }
    }

    /** Prépare la requête et appelle l'action du contrôleur. En dev, la page d'erreur inclut la trace. */
    public function route(): void {
        try {
            $this->trim_all_input();
            //si un parametre 1, 2 ou 3 est vide (et donc non passé), le supprimer.
            for ($i = 1; $i <= 3; ++$i) {
                if (($_GET["param$i"] ?? null) === "") {
                    unset($_GET["param$i"]);
                }
            }
            $controller = $this->get_controller();
            $this->block_javascript();
            $this->call_action($controller);
        } catch (Throwable $ex) {
            if (Configuration::is_dev()) {
                Tools::abort($ex->getMessage() . "\n" . $ex->getTraceAsString());
            } else {
                Tools::abort($ex->getMessage());
            }
        }
    }

}
