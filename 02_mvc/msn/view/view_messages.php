<?php
/**
 * @var Member $user
 * @var string $web_root
 * @var string $recipient
 * @var string $body
 * @var bool $private
 * @var string[] $errors
 */
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
            <form id="message_form" action="message/index/<?= e($recipient->get_pseudo()) ?>" method="post">
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
