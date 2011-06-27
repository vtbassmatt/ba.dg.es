<?php

session_start();
if(!isset($_SESSION['userid']) || is_null($_SESSION['userid']))
{
    header('Location: notloggedin.php');
    exit();
}

// thanks Facebook, you were actually useful for a change
// http://developers.facebook.com/docs/authentication/

$app_id = "replace me with foursquare app id";
$app_secret = "replace me with foursquare app secret";
require_once('4sq_secret.inc');

$my_url = "http://badges.mattandchristy.net/oauth/auth_4sq.php";
$their_url = "https://foursquare.com/oauth2/authenticate?" .
    "client_id=" . $app_id .
    "&response_type=code" .
    "&redirect_uri=" . urlencode($my_url);
$access_token_url = "https://foursquare.com/oauth2/access_token?" .
    "client_id=" . $app_id .
    "&client_secret=" . $app_secret .
    "&grant_type=authorization_code" .
    "&redirect_uri=" . urlencode($my_url);
$authorization_url = "https://foursquare.com/oauth2/authorize";

session_start();
$code = $_REQUEST["code"];

if(empty($code)) {
    // CSRF protection
    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
    
    $dialog_url = $their_url . "&state=" . $_SESSION['state'];
    echo("<script> top.location.href='" . $dialog_url . "'</script>");
}

if($_REQUEST['state'] == $_SESSION['state']) {
    $response = file_get_contents($access_token_url . "&code=" . $code);
    $params = json_decode($response, true);
    
    require_once('../mysqli.php');
    
    // drop any existing tokens
    $query = "delete from `auth_tokens` where `user_id` = ? and `site` = ?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt) {
        echo "Failed to prepare statement: " . $mysqli->error;
        exit();
    }
    $user_id = $_SESSION['userid'];
    $site_id = SITE_FOURSQUARE;
    $stmt->bind_param("ii", $user_id, $site_id);
    $stmt->execute();
    
    // add the current token
    $query = "insert into `auth_tokens` (`user_id`, `site`, `token`) values (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    if(!$stmt) {
        echo "Failed to prepare statement: " . $mysqli->error;
        exit();
    }
    $user_id = $_SESSION['userid'];
    $site_id = SITE_FOURSQUARE;
    $stmt->bind_param("iis", $user_id, $site_id, $params['access_token']);
    $stmt->execute();
    if($stmt->affected_rows == 1)
    {
        // return the user to the front page
        header('Location: ../index.php');
    } else {
        echo "Affected " . $stmt->affected_rows . "; this is probably a failure";
    }
    
} else {
    echo("State failure.  CSRF?");
}