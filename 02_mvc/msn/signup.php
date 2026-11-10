<?php

require_once "functions.php";
require_once "model/Member.php";
require_once "framework/Configuration.php";

$pseudo = $_POST["pseudo"] ?? null;
$password = $_POST["password"] ?? null;
$password_confirm = $_POST["password_confirm"] ?? null;
$errors = [];

if ($pseudo !== null && $password !== null && $password_confirm !== null) {
    $pseudo = trim($pseudo);
    $password = trim($password);
    $password_confirm = trim($password_confirm);

    $member = new Member($pseudo, "");
    $errors = [
        ...$member->validate(),
        ...Member::validate_passwords($password, $password_confirm),
        ...Member::validate_unique_pseudo($pseudo),
    ];
    if ($errors === []) {
        $member->set_hashed_password(password_hash($password, PASSWORD_BCRYPT));
        $errors = $member->persist();
    }
    if ($errors === []) {
        log_user($member);
    }
}

$web_root = Configuration::get("web_root");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Sign Up</div>
        <div class="menu">
            <a href="main/index">Home</a>
        </div>
        <div class="main">
            Please enter your details to sign up :
            <br><br>
            <form id="signup_form" action="signup.php" method="post">
                <table>
                    <tr>
                        <td>Pseudo:</td>
                        <td><input id="pseudo" name="pseudo" type="text" size="16" value="<?= e($pseudo ?? "") ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="errors" id="err_pseudo"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input id="password" name="password" type="password" size="16" value="<?= e($password ?? "") ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="errors" id="err_password"></td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td><input id="password_confirm" name="password_confirm" type="password" size="16" value="<?= e($password_confirm ?? "") ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="errors" id="err_password_confirm"></td>
                    </tr>
                </table>
                <input type="submit" value="Sign Up">
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
        </div>
    </body>
</html>
