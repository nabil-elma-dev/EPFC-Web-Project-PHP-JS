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
<!DOCTYPE html>
<html>
    <head>
        <title><?= e($recipient->get_pseudo()) ?>'s Messages</title>
        <base href="<?= $web_root ?>"/>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title"><?= e($recipient->get_pseudo()) ?>'s Messages</div>
        <?php include("view/menu.html"); ?>
        <div class="main">
            <form id="message_form" action="messages.php?param1=<?= e($recipient->get_pseudo()) ?>" method="post">
                <h4 id="message_form_title">Type here to leave a message:</h4>
                <textarea id="body" name="body" rows="3"><?= e($body) ?></textarea><br>
                <input id="private" name="private" type="checkbox"<?= $private ? " checked" : "" ?>>Private message<br>
                <input id="post" type="submit" value="Post">
            </form>

            <?php if ($errors !== []): ?>
                <div class="errors">
                    <p>Please correct the following errors:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <p>These are <?= e($recipient->get_pseudo()) ?>'s messages:</p>
            <table id="message_list" class="message_list">
                <tr>
                    <th>Date/Time</th>
                    <th>Author</th>
                    <th>Message</th>
                    <th>Private?</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($recipient->get_messages() as $message): ?>
                    <?php if ($message->is_visible_to($user)): ?>
                        <tr>
                            <td><?= e($message->get_date_time()) ?></td>
                            <td><a href="member/profile/<?= e($message->get_author()->get_pseudo()) ?>"><?= e($message->get_author()->get_pseudo()) ?></a></td>
                            <td><?= e($message->get_body()) ?></td>
                            <td><input type="checkbox" disabled <?= ($message->is_private() ? " checked" : "") ?>></td>
                            <td>
                                <?php if ($message->can_be_deleted_by($user)): ?>
                                    <form class="link" action="messages.php?action=delete" method="post">
                                        <input type="text" name="id_message" value="<?= e($message->get_post_id()) ?>" hidden>
                                        <input type="submit" value="erase">
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    </body>
</html>
