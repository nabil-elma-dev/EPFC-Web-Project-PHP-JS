<?php

require_once "model/Member.php";
require_once "model/Message.php";
require_once "framework/View.php";
require_once "framework/Controller.php";

class ControllerMember extends Controller {

    /** Enregistre le profil et la photo seulement si tout est valide, puis redirige. */
    public function edit_profile(): void {
        $member = Member::get_by_id($this->get_user_id_or_redirect());
        $errors = [];

        $profile = $_POST["profile"] ?? null;
        if ($profile !== null) {
            $photo_errors = [];
            $new_photo = null;
            $image = $_FILES["image"] ?? null;
            if ($image !== null && ($image["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $photo_errors = Member::validate_photo($image);
                if ($photo_errors === []) {
                    $new_photo = $image;
                }
            }
            $member->set_profile($profile);
            // ... déplie un tableau (ou Traversable) dans un littéral : [...$a, ...$b] concatène les listes.
            $errors = [...$photo_errors, ...$member->validate()];
            if ($errors === []) {
                if ($new_photo !== null) {
                    $save_to = $member->generate_photo_name($new_photo);
                    $old_file_name = $member->get_picture_path();
                    if ($old_file_name && file_exists("upload/" . $old_file_name)) {
                        unlink("upload/" . $old_file_name);
                    }
                    move_uploaded_file($new_photo["tmp_name"], "upload/$save_to");
                    $member->set_picture_path($save_to);
                }
                $errors = $member->persist();
            }
            if ($errors === []) {
                $this->redirect("member", "edit_profile");
            }
        }

        (new View("edit_profile"))->show(["member" => $member, "errors" => $errors]);
    }

    /** Affiche le profil. */
    public function index(): void {
        $this->profile();
    }

    /** Affiche le profil du membre connecté, ou celui demandé dans l'URL. */
    public function profile(): void {
        $member = Member::get_by_id($this->get_user_id_or_redirect());
        $pseudo = $_GET["param1"] ?? "";
        if ($pseudo !== "") {
            $found = Member::get_member_by_pseudo($pseudo);
            if ($found === false) {
                throw new Exception("Unknown member");
            }
            $member = $found;
        }
        (new View("profile"))->show(["member" => $member]);
    }

}
