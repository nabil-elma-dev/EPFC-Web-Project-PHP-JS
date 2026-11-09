<?php

declare(strict_types=1);

require_once 'Configuration.php';

class View {

    private string $file;

    /** Retient la vue view/view_{action}.php. */
    public function __construct(string $action) {
        $this->file = "view/view_$action.php";
    }

    /** Affiche la vue et crée une variable pour chaque entrée de $data, plus $web_root. */
    public function show(array $data = []): void {
        if (file_exists($this->file)) {
            extract($data);
            $web_root = Configuration::get("web_root");
            require $this->file;
        } else {
            throw new Exception("File '$this->file' doesn't exist");
        }
    }

}
