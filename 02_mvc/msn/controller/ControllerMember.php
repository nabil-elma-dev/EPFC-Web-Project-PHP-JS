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

    /** Affiche la liste des membres. **/ 
    public function members() : void {
        $member_id = $this->get_user_id_or_redirect();
        $member_in_session = Member::get_by_id($member_id);
        $other_members_and_relationships = $member_in_session->get_other_members_and_relationships();
        $members = Member::get_members();
        (new View("members"))->show(["members" => $members, 
                                      "member_in_session" => $member_in_session,
                                      "other_members_and_relationships" => $other_members_and_relationships
        ]);
    }

    public function friends() : void {
        $member_id = $this->get_user_id_or_redirect();
        $member_in_session = Member::get_by_id($member_id);
        $other_members_and_relationships = $member_in_session->get_other_members_and_relationships();
        $mutual_friends = [];
        $followers = [];
        $followed = [];
        
        foreach ($other_members_and_relationships as $other_member) {
            if ($other_member["i_follow"] === 1 && $other_member["follows_me"] === 1) {
                $mutual_friends[] = $other_member["pseudo"];
            } elseif ($other_member["i_follow"] === 1) {
                $followed[] = $other_member["pseudo"];
            } elseif ($other_member["follows_me"] === 1) {
                $followers[] = $other_member["pseudo"];
            }
        } 

        $arrays_are_empty = count($mutual_friends) === 0 && count($followers) === 0 && count($followed) === 0;


        (new View("friends"))->show(["member_in_session" => $member_in_session,
                                     "mutual_friends" => $mutual_friends,
                                     "followed" => $followed,
                                     "followers" => $followers,
                                     "arrays_are_empty" => $arrays_are_empty
        ]);
    }

    public function follow() : void {
        $member_id = $this->get_user_id_or_redirect();
        $member_in_session = Member::get_by_id($member_id);
        $user_to_follow_string = $_POST["user_to_follow"] ?? null;
        if ($user_to_follow_string === null || $user_to_follow_string === "") {
            Tools::abort("Wrong/missing param or action no permitted");
        }
        $user_to_follow = Member::get_member_by_pseudo($user_to_follow_string);
        $member_in_session->follow($user_to_follow);
        $this->redirect("member", "members");
    }

    public function unfollow() : void {
        $member_id = $this->get_user_id_or_redirect();
        $member_in_session = Member::get_by_id($member_id);
        $user_to_unfollow_string = $_POST["user_to_unfollow"] ?? null;
        if ($user_to_unfollow_string === null || $user_to_unfollow_string === "") {
            Tools::abort("Wrong/missing param or action no permitted");
        }
        $user_to_unfollow = Member::get_member_by_pseudo($user_to_unfollow_string);
        $member_in_session->unfollow($user_to_unfollow);
        $this->redirect("member", "members");
    }
}
