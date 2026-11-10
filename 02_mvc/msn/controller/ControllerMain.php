<?php

require_once "model/Member.php";
require_once "framework/View.php";
require_once "framework/Controller.php";

class ControllerMain extends Controller {

    /** Accueil : profil si connecté, sinon la page d'invitation. */
    public function index(): void {
        if ($this->user_logged()) {
            $this->redirect("member", "profile");
        } else {
            (new View("index"))->show();
        }
    }

    /** Connecte le membre si le pseudo et le mot de passe sont valides, sinon réaffiche le formulaire. */
    public function login(): void {
        $pseudo = $_POST["pseudo"] ?? null;
        $password = $_POST["password"] ?? null;
        $errors = [];
        if ($pseudo !== null && $password !== null) {
            $errors = Member::validate_login($pseudo, $password);
            if ($errors === []) {
                $member = Member::get_member_by_pseudo($pseudo);
                if ($member === false) {
                    $errors[] = "Can't find a member with the pseudo '$pseudo'. Please sign up.";
                } else {
                    $this->log_user($member, "member", "profile");
                }
            }
        }
        (new View("login"))->show([
            "pseudo" => $pseudo ?? "",
            "password" => $password ?? "",
            "errors" => $errors,
        ]);
    }
}
