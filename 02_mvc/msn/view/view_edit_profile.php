<?php
/**
 * @var string $web_root
 * @var Member $member
 * @var string[] $errors
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Update Your Profile</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Update Your Profile</div>
        <?php include('menu.html'); ?>
        <div class="main">
            <form method='post' action='member/edit_profile' enctype='multipart/form-data'>
                <p>Enter or edit your details and/or upload an image.</p>
                <textarea name="profile" cols="50" rows="3"><?= e($member->get_profile()) ?></textarea>

                Image: <input type="file" name="image" accept="image/x-png, image/gif, image/jpeg">
                <?php if (mb_strlen($member->get_picture_path() ?? "") != 0): ?>
                    <img src="upload/<?= e($member->get_picture_path()) ?>" width="100" alt="Profile image">
                <?php endif; ?>
                <input type='submit' value='Save Profile'>
            </form>

            <?php if ($errors !== []): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>


        </div>
    </body>
</html>
