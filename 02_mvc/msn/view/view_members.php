<?php
/**
 * @var string $web_root
 * @var Member[] $members
 */
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Members</title>
        <meta charset="UTF-8">
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Members</div>
        <?php include("menu.html"); ?>
        <div class="main">
            <ul>
                <?php foreach ($members as $member): ?>
                    <li><a href="member/profile/<?= e($member->get_pseudo()) ?>"><?= e($member->get_pseudo()) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </body>
</html>