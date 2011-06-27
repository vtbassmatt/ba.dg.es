<?php

session_start();
if(!isset($_SESSION['userid']) || is_null($_SESSION['userid']))
{
    echo '{"error": "Not logged in"}';
    exit();
}

require_once('mysqli.php');

// add the current token
$query = "select `badges`.`name`,`badges`.`img_url` "
       . "from `badges` inner join `user_badges` "
       . "on `badges`.`badge_id` = `user_badges`.`badge_id` "
       . "and `badges`.`site` = `user_badges`.`site` "
       . "where `user_badges`.`user_id` = ?";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo "Failed to prepare statement: " . $mysqli->error;
    exit();
}
$user_id = $_SESSION['userid'];
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($badge_name, $badge_url);
echo '{ "results" : [';
$printed = 0;
while($stmt->fetch()) {
    if($printed > 0) { echo ",\n"; } else { echo "\n"; }
    $printed++;
    echo '{ "name": "'.addcslashes($badge_name,'"').'", "url":"'.$badge_url.'" }';
}
echo "\n] }\n";
?>