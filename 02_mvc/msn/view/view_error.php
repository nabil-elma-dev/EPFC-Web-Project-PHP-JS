<?php
/**
 * @var string $web_root
 * @var string $error
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Error</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Error</div>
        <div class="main">
            <?php /* nl2br après e() : Tools::abort() peut joindre le message et la trace, donc plusieurs lignes. En HTML, ces retours à la ligne disparaissent. nl2br les change en <br> une fois le texte échappé. false garde la forme <br>. Un message d'une seule ligne ne change pas d'aspect. */ ?>
            <div class="errors"><?= nl2br(e($error), false) ?></div>
        </div>
    </body>
</html>
