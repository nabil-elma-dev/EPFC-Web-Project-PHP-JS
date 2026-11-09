<?php

declare(strict_types=1);

/** Objet qu'on peut connecter : fournit la clé à stocker en session. */
interface Authenticatable {

    /** Clé primaire à mémoriser en session (int, string, …). */
    public function get_id(): mixed;
}
