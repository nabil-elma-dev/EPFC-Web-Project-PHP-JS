<?php
/**
 * @var string $web_root
 * @var Member $member
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?= e($member->get_pseudo()) ?>'s Profile!</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title"><?= e($member->get_pseudo()) ?>'s Profile!</div>
        <?php include("menu.html"); ?>
        <div class="main">
            <?php if (mb_strlen($member->get_profile() ?? "") == 0): ?>
                No profile string entered yet!
            <?php else: ?>
                <?php /* nl2br après e() pour les retours à la ligne du profil */ ?>
                <?= nl2br(e($member->get_profile()), false) ?>
            <?php endif; ?>
            <br><br>
            <?php if (mb_strlen($member->get_picture_path() ?? "") == 0): ?>
                No picture loaded yet!
            <?php else: ?>
                <img src="upload/<?= e($member->get_picture_path()) ?>" width="100" alt="<?= e($member->get_pseudo()) ?>'s photo!">
            <?php endif; ?>
            <br>
            <br>
            <a href="messages.php?param1=<?= e($member->get_pseudo()) ?>">View <?= e($member->get_pseudo()) ?>'s messages</a>
        </div>
    </body>
</html>
