<?php

require_once "framework/Model.php";
require_once "framework/Authenticatable.php";
require_once "Message.php";

class Member extends Model implements Authenticatable {

    /** Crée un membre. Le mot de passe reçu est déjà haché. */
    public function __construct(
        private string $pseudo,
        private string $hashed_password,
        private ?string $profile = null,
        private ?string $picture_path = null
    ) {
    }

    /** Renvoie le pseudo. */
    public function get_pseudo(): string {
        return $this->pseudo;
    }

    /** Clé à stocker en session : le pseudo. */
    public function get_id(): mixed {
        return $this->pseudo;
    }

    /** Renvoie le texte du profil. */
    public function get_profile(): ?string {
        return $this->profile;
    }

    /** Remplace le texte du profil, sans l'enregistrer. */
    public function set_profile(?string $profile): void {
        $this->profile = $profile;
    }

    /** Renvoie le nom du fichier photo. */
    public function get_picture_path(): ?string {
        return $this->picture_path;
    }

    /** Remplace le nom du fichier photo, sans l'enregistrer. */
    public function set_picture_path(?string $picture_path): void {
        $this->picture_path = $picture_path;
    }

    /** Mémorise le mot de passe déjà haché, sans l'enregistrer. */
    public function set_hashed_password(string $hashed_password): void {
        $this->hashed_password = $hashed_password;
    }

    /** Enregistre le message et renvoie les erreurs, vide si tout va bien. */
    public function write_message(Message $message): array {
        return $message->persist();
    }

    /** Supprime le message si ce membre en a le droit. */
    public function delete_message(Message $message): Message|false {
        return $message->delete($this);
    }

    /** Renvoie les messages reçus par ce membre. */
    public function get_messages(): array {
        return Message::get_messages($this);
    }

    /** Liste les autres membres et les liens, en une requête. i_follow = je suis cette personne, follows_me = elle me suit. */
    public function get_other_members_and_relationships(): array {
        $stmt = self::execute(
            "SELECT pseudo,
                    (SELECT COUNT(*)
                     FROM Follows
                     WHERE follower = :user AND followee = Members.pseudo) AS i_follow,
                    (SELECT COUNT(*)
                     FROM Follows
                     WHERE followee = :user AND follower = Members.pseudo) AS follows_me
             FROM Members
             WHERE pseudo <> :user
             ORDER BY pseudo ASC",
            ["user" => $this->pseudo]
        );
        return $stmt->fetchAll();
    }

    /** Suit ce membre, sans rien faire si le lien existe déjà. */
    public function follow(Member $followee): void {
        if (!$this->follows($followee)) {
            self::add_follower($this->pseudo, $followee->get_pseudo());
        }
    }

    /** Ne suit plus ce membre. */
    public function unfollow(Member $followee): void {
        self::delete_follower($this->pseudo, $followee->get_pseudo());
    }

    /** Indique si ce membre suit déjà l'autre. */
    public function follows(Member $followee): bool {
        $stmt = self::execute(
            "SELECT COUNT(*) FROM Follows WHERE follower = :follower AND followee = :followee",
            ["follower" => $this->pseudo, "followee" => $followee->get_pseudo()]
        );
        $row = $stmt->fetch();
        return ((int) $row[0]) > 0;
    }

    /** Insère le lien « follower suit followee ». */
    private static function add_follower(string $user_pseudo, string $followee_pseudo): void {
        self::execute(
            "INSERT INTO Follows VALUES (:follower, :followee)",
            ["follower" => $user_pseudo, "followee" => $followee_pseudo]
        );
    }

    /** Retire le lien « follower suit followee ». */
    private static function delete_follower(string $user_pseudo, string $followee_pseudo): void {
        self::execute(
            "DELETE FROM Follows WHERE follower = :follower AND followee = :followee",
            ["follower" => $user_pseudo, "followee" => $followee_pseudo]
        );
    }

    /** Vérifie le pseudo. Renvoie les messages d'erreur, vide si tout va bien. */
    public function validate(): array {
        $errors = [];
        $min = (int) Configuration::get("pseudo_min_length");
        $max = (int) Configuration::get("pseudo_max_length");
        $pseudo_pattern = (string) Configuration::get("pseudo_pattern");
        if (mb_strlen($this->pseudo) === 0) {
            $errors[] = "Pseudo is required.";
        } elseif (mb_strlen($this->pseudo) < $min || mb_strlen($this->pseudo) > $max) {
            $errors[] = "Pseudo length must be between $min and $max.";
        } elseif (!preg_match("#{$pseudo_pattern}#", $this->pseudo)) {
            $errors[] = "Pseudo must start by a letter and must contain only letters and numbers.";
        }
        return $errors;
    }

    /** Valide puis insère ou met à jour le membre. Renvoie les erreurs, vide si tout va bien. */
    public function persist(): array {
        $errors = $this->validate();
        if ($errors !== []) {
            return $errors;
        }
        if (self::get_member_by_pseudo($this->pseudo)) {
            self::execute(
                "UPDATE Members SET password = :password, picture_path = :picture, profile = :profile WHERE pseudo = :pseudo",
                [
                    "picture" => $this->picture_path,
                    "profile" => $this->profile,
                    "pseudo" => $this->pseudo,
                    "password" => $this->hashed_password,
                ]
            );
        } else {
            self::execute(
                "INSERT INTO Members (pseudo, password, profile, picture_path) VALUES (:pseudo, :password, :profile, :picture_path)",
                [
                    "pseudo" => $this->pseudo,
                    "password" => $this->hashed_password,
                    "picture_path" => $this->picture_path,
                    "profile" => $this->profile,
                ]
            );
        }
        return [];
    }

    /** Cherche un membre par son pseudo, ou false. */
    public static function get_member_by_pseudo(string $pseudo): Member|false {
        $stmt = self::execute(
            "SELECT * FROM Members WHERE pseudo = :pseudo",
            ["pseudo" => $pseudo]
        );
        $row = $stmt->fetch();
        if ($stmt->rowCount() == 0) {
            return false;
        }
        return self::member_from_row($row);
    }

    /** Cherche un membre par sa clé primaire (ici le pseudo), ou false. */
    public static function get_by_id(mixed $id): Member|false {
        return self::get_member_by_pseudo((string) $id);
    }

    /** Renvoie tous les membres, triés par pseudo. */
    public static function get_members(): array {
        $stmt = self::execute("SELECT * FROM Members ORDER BY pseudo ASC");
        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[] = self::member_from_row($row);
        }
        return $results;
    }

    /** Vérifie le mot de passe et sa confirmation. Le mot de passe n'est pas un attribut. */
    public static function validate_passwords(string $password, string $password_confirm): array {
        $errors = self::validate_password($password);
        if ($password !== $password_confirm) {
            $errors[] = "You have to enter twice the same password.";
        }
        return $errors;
    }

    /** Signale si ce pseudo est déjà pris. */
    public static function validate_unique_pseudo(string $pseudo): array {
        $errors = [];
        if (self::get_member_by_pseudo($pseudo)) {
            $errors[] = "This user already exists.";
        }
        return $errors;
    }

    /** Vérifie que le membre existe et que le mot de passe correspond. */
    public static function validate_login(string $pseudo, string $password): array {
        $errors = [];
        $member = self::get_member_by_pseudo($pseudo);
        if ($member) {
            if (!self::check_password($password, $member->hashed_password)) {
                $errors[] = "Wrong password. Please try again.";
            }
        } else {
            $errors[] = "Can't find a member with the pseudo '$pseudo'. Please sign up.";
        }
        return $errors;
    }

    /** Accepte une photo gif, jpeg ou png. Un envoi vide n'est pas une erreur. */
    public static function validate_photo(array $file): array {
        $errors = [];
        if (($file["name"] ?? "") === "") {
            return $errors;
        }
        if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = "Error while uploading file.";
            return $errors;
        }
        $type = (string) ($file["type"] ?? "");
        if (!preg_match("#^image/(gif|jpeg|png)$#", $type)) {
            $errors[] = "Unsupported image format : gif, jpg/jpeg or png.";
        }
        return $errors;
    }

    /** Construit un nom de fichier à partir du pseudo, de l'heure et du type. */
    public function generate_photo_name(array $file): string {
        $extension = match ($file["type"] ?? "") {
            "image/gif" => "gif",
            "image/jpeg" => "jpg",
            "image/png" => "png",
            default => "img",
        };
        return $this->pseudo . time() . "." . $extension;
    }

    /** Vérifie la longueur et les caractères exigés du mot de passe. */
    private static function validate_password(string $password): array {
        $errors = [];
        $min = (int) Configuration::get("password_min_length");
        $max = (int) Configuration::get("password_max_length");
        $password_pattern = (string) Configuration::get("password_pattern");
        if (mb_strlen($password) < $min || mb_strlen($password) > $max) {
            $errors[] = "Password length must be between $min and $max.";
        } elseif (!preg_match("#{$password_pattern}#", $password)) {
            $errors[] = "Password must contain one uppercase letter, one number and one special character.";
        }
        return $errors;
    }

    /** Compare le mot de passe saisi au hachage enregistré. */
    private static function check_password(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /** Construit un membre à partir d'une ligne SQL. */
    private static function member_from_row(array $row): Member {
        return new Member($row["pseudo"], $row["password"], $row["profile"], $row["picture_path"]);
    }
}
