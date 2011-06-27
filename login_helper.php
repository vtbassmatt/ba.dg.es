<?php

session_start();

// ask Facebook to make sure this is really the user they say they are
$fb_uid = $_GET['uid'];
$fb_access_token = $_GET['token'];

if(strlen($fb_uid) < 1) {
    echo '{ "error" : "Missing uid" }';
    exit();
}
if(strlen($fb_access_token) < 1) {
    echo '{ "error" : "Missing token" }';
    exit();
}


$fb_endpoint = "https://graph.facebook.com/".$fb_uid."?access_token=".$fb_access_token;
@$fb_response = file_get_contents($fb_endpoint);

// expect a 200 if everything is hunky-dory, anything else is an error
$found_200 = false;
foreach($http_response_header as $header) {
    if(substr($header, 0, 5) == "HTTP/" && substr($header, 9, 3) == "200") {
        $found_200 = true;
        break;
    }
}
if(!$found_200) {
    echo '{ "error" : "Error authenticating the user with Facebook" }';
    unset($_SESSION['userid']);
    exit();
}


$fb_user = json_decode($fb_response, true);
$fb_name = $fb_user['name'];


// find the user in our database
require_once('mysqli.php');

$query = "select `user_id`,`name` from `users` where `fb_uid` = ?";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
    exit();
}
$stmt->bind_param('i', $fb_uid);
$stmt->execute();
$stmt->bind_result($user_id, $name);
$found_result = false;
while($stmt->fetch()) {
    echo '{ "user_id" : '.$user_id.', "name":"'.$name.'"}';
    $_SESSION['userid'] = $user_id;
    $found_result = true;
    break;
}
$stmt->close();

// if we found a user, we're done
if($found_result) { exit(); }

// if we didn't find the user, add them
$query = "insert into `users` (`name`, `fb_uid`) "
       . "values (?, ?)";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
    exit();
}
$name = $fb_name;
$stmt->bind_param("si", $name, $fb_uid);
$stmt->execute();
$stmt->close();


// now return the name
$query = "select `user_id`,`name` from `users` where `fb_uid` = ?";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
    exit();
}
$stmt->bind_param('i', $fb_uid);
$stmt->execute();
$stmt->bind_result($user_id, $name);
$found_result = false;
while($stmt->fetch()) {
    echo '{ "user_id" : '.$user_id.', "name":"'.$name.'"}';
    $_SESSION['userid'] = $user_id;
    $found_result = true;
    break;
}
$stmt->close();

if(!$found_result) {
    echo '{ "error" : "User not found and not able to be created"}';
    unset($_SESSION['userid']);
}

?>