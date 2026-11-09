<?php

declare(strict_types=1);

class Configuration {

    private static ?array $parameters = null;

    /** Lit un paramètre de dev.ini, ou de prod.ini s'il n'y a pas de fichier dev. */
    public static function get(string $name, mixed $default_value = null): mixed {
        $parameters = self::get_parameters();
        return $parameters[$name] ?? $default_value;
    }

    /** Charge le fichier ini une seule fois. */
    private static function get_parameters(): array {
        if (self::$parameters === null) {
            $file_path = "config/dev.ini";
            if (!file_exists($file_path)) {
                $file_path = "config/prod.ini";
            }
            if (!file_exists($file_path)) {
                throw new Exception("Config file is missing.");
            }
            $parsed = parse_ini_file($file_path);
            if ($parsed === false) {
                throw new Exception("Config file could not be read.");
            }
            self::$parameters = $parsed;
        }
        return self::$parameters;
    }

    /** Vrai si le fichier config/dev.ini est présent. */
    public static function is_dev(): bool {
        return file_exists("config/dev.ini");
    }

}
