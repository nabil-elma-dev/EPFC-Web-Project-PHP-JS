<?php
/**
 * @var string $web_root
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Welcome to MSN (My Social Network)!</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Welcome to MSN (My Social Network)!</div>
        <div class="menu">
            <a href="main/login">Log In</a>
            <a href="signup.php">Sign Up</a>
        </div>
        <div class="main">
            Please log in or sign up!
        </div>
        <?php if (Configuration::is_dev()): ?>
            <div class="debug_box">
                <p>For debug purposes only</p>
                <p><a href="setup/install">Install the database</a></p>
            </div>
        <?php endif; ?>
    </body>
</html>
