<?php
/**
 * @var string $web_root
 * @var string $pseudo
 * @var string $password
 * @var string[] $errors
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Log In</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Log In</div>
        <div class="menu">
            <a href="main/index">Home</a>
            <a href="signup.php">Sign Up</a>
        </div>
        <div class="main">
            <form action="main/login" method="post">
                <table>
                    <tr>
                        <td>Pseudo:</td>
                        <td><input id="pseudo" name="pseudo" type="text" value="<?= e($pseudo) ?>"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input id="password" name="password" type="password" value="<?= e($password) ?>"></td>
                    </tr>
                </table>
                <input type="submit" value="Log In">
            </form>
            <?php if ($errors !== []): ?>
                <div class="errors"><?= e($errors[0]) ?></div>
            <?php endif; ?>
        </div>
    </body>
</html>
