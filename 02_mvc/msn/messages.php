<?php

require_once "functions.php";
require_once "model/Member.php";
require_once "model/Message.php";
require_once "framework/Configuration.php";

function get_recipient(Member $user): Member {
    $pseudo = trim($_GET["param1"] ?? "");
    if ($pseudo === "") {
        return $user;
    }
    $recipient = Member::get_member_by_pseudo($pseudo);
    if ($recipient === false) {
        throw new Exception("Unknown member");
    }
    return $recipient;
}

function delete_message(Member $user): void {
    $post_id = filter_var($_POST["id_message"] ?? "", FILTER_VALIDATE_INT);
    if ($post_id === false) {
        Tools::abort("Wrong/missing param or action no permitted");
    }
    $message = Message::get_message($post_id);
    if ($message === false || !$message->can_be_deleted_by($user)) {
        Tools::abort("Wrong/missing param or action no permitted");
    }
    $message->delete($user);
    redirect("messages.php?param1=" . $message->get_recipient()->get_pseudo());
}

$user = Member::get_by_id(get_user_id_or_redirect());

if (trim($_GET["action"] ?? "") === "delete") {
    delete_message($user);
}

$recipient = get_recipient($user);
$errors = [];
$body = $_POST["body"] ?? null;
$private = false;
if ($body !== null) {
    $private = isset($_POST["private"]);
    $message = new Message($user, $recipient, trim($body), $private);
    $errors = $message->validate();
    if ($errors === []) {
        $errors = $user->write_message($message);
    }
    if ($errors === []) {
        redirect("messages.php?param1=" . $recipient->get_pseudo());
    }
}
$body = $body ?? "";

$web_root = Configuration::get("web_root");
?>