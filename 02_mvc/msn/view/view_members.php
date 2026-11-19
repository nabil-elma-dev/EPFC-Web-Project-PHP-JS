<?php
/**
 * @var string $web_root
 * @var Member[] $members
 * @var Member $member_in_session
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
            <?php  
                foreach ($other_members_and_relationships as $other_member):
            ?>
                <li>
                    <table>
                        <tr>
                            <td>
                                <a href="member/profile/<?= e($other_member["pseudo"]) ?>"> <?=e($other_member["pseudo"]) ?> </a>
                            </td>
                            
                            <td>
                                <?php if ($other_member["i_follow"] === 1) : ?>
                                    <?php if ($other_member["follows_me"] === 1) : ?>
                                        <p>↔ is mutual friend</p>
                                    <?php else : ?>
                                        <p>← you are following</p>
                                    <?php endif; ?>
                                <?php elseif ($other_member["follows_me"] === 1) : ?>
                                    <p>→ is following you</p>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($other_member["i_follow"] === 1) : ?>
                                    <form class="link" action="member/unfollow" method="post">
                                        <input type="text" name="user_to_unfollow" value="<?= $other_member["pseudo"] ?>" hidden>
                                        <input type="submit" value="[drop]">
                                    </form>
                                <?php else: ?>
                                    <form class="link" action="member/follow" method="post">
                                        <input type="text" name="user_to_follow" value="<?= $other_member["pseudo"] ?>" hidden>
                                        <input type="submit" value=<?=$other_member["follows_me"] === 1 ? "[recip]" : "[follow]"?>>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </li>
            <?php 
                endforeach; 
            ?>
 <!--               
4 Case scenario: 
    a) Member in session follows member in list
    b) Member in session is followed by member in list
    c) Member in session && Member in list follow each other
    d) bi-lateral non-following              
-->
                
            </ul>
        </div>
    </body>
</html>