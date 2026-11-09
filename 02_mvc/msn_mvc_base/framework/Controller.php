<?php

declare(strict_types=1);

require_once 'Configuration.php';
require_once 'Authenticatable.php';

abstract class Controller {

    /** Démarre la session. */
    public function __construct() {
        session_start();
    }

    /** Régénère l'identifiant de session, mémorise get_id() de l'utilisateur, puis redirige. */
    protected function log_user(Authenticatable $user, string $controller = "", string $action = "index"): void {
        session_regenerate_id(true);
        $_SESSION["user"] = $user->get_id();
        // Libère la session avant la redirection.
        session_write_close();
        $this->redirect($controller, $action);
    }

    /** Efface la session et renvoie à l'accueil. */
    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        $this->redirect();
    }

    /** Envoie une redirection 303. Un paramètre vide coupe ceux qui suivent. */
    protected function redirect(string $controller = "", string $action = "index", string $param1 = "", string $param2 = "", string $param3 = "", int $status_code = 303): void {
        $web_root = Configuration::get("web_root");
        $default_controller = Configuration::get("default_controller");
        if ($controller === "") {
            $controller = $default_controller;
        }

        $header = "Location: $web_root$controller/$action";
        if ($param1 !== "") {
            $header .= "/$param1";
            if ($param2 !== "") {
                $header .= "/$param2";
                if ($param3 !== "") {
                    $header .= "/$param3";
                }
            }
        }
        header($header, true, $status_code);
        die();
    }

    /** Indique si un utilisateur est connecté. */
    protected function user_logged(): bool {
        return isset($_SESSION['user']);
    }

    /** Renvoie la clé primaire stockée en session, ou false. */
    protected function get_user_id(): mixed {
        return $_SESSION["user"] ?? false;
    }

    /** Renvoie la clé primaire stockée en session, ou renvoie à l'accueil. */
    protected function get_user_id_or_redirect(): mixed {
        $user_id = $this->get_user_id();
        if ($user_id === false) {
            $this->redirect();
        }
        return $user_id;
    }

    /** Action par défaut du contrôleur. */
    public abstract function index(): void;
}
