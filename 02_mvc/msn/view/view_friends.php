<?php
/**
 * @var $other_members_and_relationships
 */
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Your Friends</title>
        <base href="<?= $web_root ?>"/>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="title">Your Friends</div>
        <?php include("view/menu.html"); ?>
        <div class="main">

            <!-- Case 1: all 3 arrays are empty -->
            <?php 
                if ($arrays_are_empty) : ?>
                <h2> No mutual friends, followers or followee yet</h2>
            <?php endif; ?>
            
            <ul>
            <!-- Case 2: for-each mutual friends (if any) -->
                <h2 <?php if (count($mutual_friends) === 0) : ?> hidden <?php endif; ?> >Your mutual friends</h2>
                
                <?php foreach ($mutual_friends as $mutual_friend) : ?>
                    <li>
                        <a href="member/profile/<?= e($mutual_friend) ?>"> <?=e($mutual_friend) ?> </a>
                    </li>
                    
                <?php endforeach; ?>

            <!-- Case 3: for-each followers (if any) -->
                <h2 <?php if (count($followers) === 0) : ?> hidden <?php endif; ?> >Your followers</h2>
                
                <?php foreach ($followers as $follower) : ?>
                    <li>
                        <a href="member/profile/<?= e($follower) ?>"> <?=e($follower) ?> </a>
                    </li>
                    
                <?php endforeach; ?>
                
            <!-- Case 4: for-each followed (if any) -->
                <h2 <?php if (count($followed) === 0) : ?> hidden <?php endif; ?> >People you follow</h2>
                    
                <?php foreach ($followed as $follows_you) : ?>
                    <li>
                        <a href="member/profile/<?= e($follows_you) ?>"> <?=e($follows_you) ?> </a>
                    </li>
                    
                <?php endforeach; ?>
            </ul>
            
        </div>
    </body>
</html>