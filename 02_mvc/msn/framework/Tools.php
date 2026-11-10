<?php

declare(strict_types=1);

require_once "View.php";

class Tools {

    /** Échappe une valeur pour l'affichage HTML. */
    public static function escape(mixed $var): string {
        return htmlspecialchars((string) $var, ENT_QUOTES, "UTF-8");
    }

    /** Affiche la page d'erreur en HTTP 500, puis arrête le script. */
    public static function abort(string $err): void {
        http_response_code(500);
        (new View("error"))->show(["error" => $err]);
        die;
    }
}

/** Raccourci de Tools::escape, pour les vues. */
function e(mixed $value): string {
    return Tools::escape($value);
}
