<?php

require_once "model/Member.php";
require_once "framework/Tools.php";

session_start();

function redirect(string $url = "index.php", int $statusCode = 303): void {
    header("Location: " . $url, true, $statusCode);
    die();
}

function user_logged(): bool {
    return isset($_SESSION["user"]);
}

function get_user_id(): mixed {
    return $_SESSION["user"] ?? false;
}

function get_user_id_or_redirect(): mixed {
    if (!user_logged()) {
        redirect();
    }
    return $_SESSION["user"];
}

function log_user(Member $member): void {
    session_regenerate_id(true);
    $_SESSION["user"] = $member->get_id();
    session_write_close();
    redirect("member/profile");
}
