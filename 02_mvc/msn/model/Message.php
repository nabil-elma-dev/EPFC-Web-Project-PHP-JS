<?php

require_once "framework/Model.php";
require_once "Member.php";

class Message extends Model {

    /** Crée un message. L'auteur et le destinataire sont des membres. */
    public function __construct(
        private Member $author,
        private Member $recipient,
        private string $body,
        private bool $private,
        private ?int $post_id = null,
        private ?string $date_time = null
    ) {
    }

    /** Renvoie l'auteur. */
    public function get_author(): Member {
        return $this->author;
    }

    /** Renvoie le destinataire. */
    public function get_recipient(): Member {
        return $this->recipient;
    }

    /** Renvoie le texte du message. */
    public function get_body(): string {
        return $this->body;
    }

    /** Indique si le message est privé. */
    public function is_private(): bool {
        return $this->private;
    }

    /** Renvoie l'identifiant, ou null s'il n'est pas encore enregistré. */
    public function get_post_id(): ?int {
        return $this->post_id;
    }

    /** Renvoie la date d'enregistrement. */
    public function get_date_time(): ?string {
        return $this->date_time;
    }

    /** Un message public est visible par tous. Un message privé, seulement par l'auteur et le destinataire. */
    public function is_visible_to(Member $viewer): bool {
        if (!$this->private) {
            return true;
        }
        return $this->can_be_deleted_by($viewer);
    }

    /** Vrai si ce membre est l'auteur ou le destinataire. */
    public function can_be_deleted_by(Member $member): bool {
        $pseudo = $member->get_pseudo();
        return $this->author->get_pseudo() === $pseudo || $this->recipient->get_pseudo() === $pseudo;
    }

    /** Vérifie l'auteur, le destinataire et le texte. Renvoie les erreurs, vide si tout va bien. */
    public function validate(): array {
        $errors = [];
        if (!Member::get_member_by_pseudo($this->author->get_pseudo())) {
            $errors[] = "Incorrect author";
        }
        if (!Member::get_member_by_pseudo($this->recipient->get_pseudo())) {
            $errors[] = "Incorrect recipient";
        }
        if (mb_strlen($this->body) === 0) {
            $errors[] = "Body must be filled";
        }
        return $errors;
    }

    /** Valide puis insère le message. Renvoie les erreurs, vide si tout va bien. */
    public function persist(): array {
        if ($this->post_id !== null) {
            throw new Exception("Not Implemented.");
        }
        $errors = $this->validate();
        if ($errors !== []) {
            return $errors;
        }
        self::execute(
            "INSERT INTO Messages (author, recipient, body, private) VALUES (:author, :recipient, :body, :private)",
            [
                "author" => $this->author->get_pseudo(),
                "recipient" => $this->recipient->get_pseudo(),
                "body" => $this->body,
                "private" => $this->private ? 1 : 0,
            ]
        );
        $message = self::get_message(self::last_insert_id());
        if ($message === false) {
            throw new Exception("Message could not be saved.");
        }
        $this->post_id = $message->get_post_id();
        $this->date_time = $message->get_date_time();
        return [];
    }

    /** Supprime le message si ce membre en a le droit. */
    public function delete(Member $member): Message|false {
        if (!$this->can_be_deleted_by($member)) {
            return false;
        }
        self::execute(
            "DELETE FROM Messages WHERE post_id = :post_id",
            ["post_id" => $this->post_id]
        );
        return $this;
    }

    /** Renvoie les messages reçus par ce membre, du plus récent au plus ancien. */
    public static function get_messages(Member $member): array {
        $stmt = self::execute(
            "SELECT * FROM Messages WHERE recipient = :pseudo ORDER BY date_time DESC",
            ["pseudo" => $member->get_pseudo()]
        );
        $messages = [];
        foreach ($stmt->fetchAll() as $row) {
            $messages[] = self::message_from_row($row);
        }
        return $messages;
    }

    /** Cherche un message par son identifiant, ou false. */
    public static function get_message(int $post_id): Message|false {
        $stmt = self::execute(
            "SELECT * FROM Messages WHERE post_id = :id",
            ["id" => $post_id]
        );
        if ($stmt->rowCount() == 0) {
            return false;
        }
        return self::message_from_row($stmt->fetch());
    }

    /** Construit un message à partir d'une ligne SQL. */
    private static function message_from_row(array $row): Message {
        $author = Member::get_member_by_pseudo($row["author"]);
        $recipient = Member::get_member_by_pseudo($row["recipient"]);
        if ($author === false || $recipient === false) {
            throw new Exception("Message refers to an unknown member.");
        }
        return new Message(
            $author,
            $recipient,
            $row["body"],
            (int) $row["private"] === 1,
            (int) $row["post_id"],
            $row["date_time"]
        );
    }
}
