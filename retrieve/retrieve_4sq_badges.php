<?php

session_start();
if(!isset($_SESSION['userid']) || is_null($_SESSION['userid']))
{
    echo '{"error": "Not logged in"}';
    exit();
}

$badges_endpoint = "https://api.foursquare.com/v2/users/self/badges?v=20110625";

require_once('../mysqli.php');

// get the user's FourSquare token
$token = "";
$query = "select `token` from `auth_tokens` where `site` = ? and `user_id` = ?";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
    exit();
}
$site_id = SITE_FOURSQUARE;
$user_id = $_SESSION['userid'];
$stmt->bind_param("ii", $site_id, $user_id);
$stmt->execute();
$stmt->bind_result($token);
$found_token = false;
while($stmt->fetch()) {
    $found_token = true;
    break;
}
$stmt->close();

if(!$found_token) {
    echo '{"status":"requireauth"}';
    exit();
}


// get the list of badges already known in the database
$existing_badges = array();
$query = "select `badge_id` from `badges` where `site` = ?";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
    exit();
}
$site_id = SITE_FOURSQUARE;
$stmt->bind_param("i", $site_id);
$stmt->execute();
$stmt->bind_result($badge_id);
while($stmt->fetch()) {
    $existing_badges[] = $badge_id;
}
$stmt->close();



// retrieve the badges Foursquare says the user has earned
@$badge_response = file_get_contents($badges_endpoint . "&oauth_token=" . $token);

// if we get a 401, we need the user to allow us on Foursquare again
$found_401 = false;
foreach($http_response_header as $header) {
    if(substr($header, 0, 5) == "HTTP/" && substr($header, 9, 3) == "401") {
        $found_401 = true;
        break;
    }
}
if($found_401) {
    echo '{"status":"requireauth"}';
    exit();
}

$badges = json_decode($badge_response, true);

// drop the badges we thought the user had
$query = "delete from `user_badges` where `user_id` = ? and `site` = ?";
$stmt = $mysqli->prepare($query);
if(!$stmt) {
    echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
    exit();
}
$user_id = $_SESSION['userid'];
$site_id = SITE_FOURSQUARE;
$stmt->bind_param("ii", $user_id, $site_id);
$stmt->execute();
$stmt->close();


$failures = array();

// process the badges Foursquare says the user has earned
foreach($badges['response']['badges'] as $badge)
{
    
    if($badge['unlocks']) {
    
        $id = $badge['badgeId'];
        
        // if the badge is not yet in the database, add it
        if(!in_array($id, $existing_badges)) {
            $name = $badge['name'];
            
            $image = $badge['image'];
            $img_url = $image['prefix'] . $image['sizes'][1] . $image['name'];
            $mobile_img_url = $image['prefix'] . $image['sizes'][0] . $image['name'];
            
        
            $query = "insert into `badges` ".
                "(`site`, `badge_id`, `name`, `img_url`, `mobile_img_url`) ".
                "values (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if(!$stmt) {
                echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
                exit();
            }
            $user_id = $_SESSION['userid'];
            $site_id = SITE_FOURSQUARE;
            $stmt->bind_param("issss", $site_id, $id, $name, $img_url, $mobile_img_url);
            $stmt->execute();
            if($stmt->affected_rows == 1)
            {
                //echo "Succeeded in creating a badge";
            } else {
                $failures[] = "Failed to add badge";
            }
            $stmt->close();
        } else {
            //echo "Badge already exists<br/>";
        }
        
        // now add it to the user's list
        $query = "insert into `user_badges` ".
            "(`user_id`, `site`, `badge_id`) ".
            "values (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        if(!$stmt) {
            echo '{ "error" : "Failed to prepare statement: ' . $mysqli->error . '" }';
            exit();
        }
        $user_id = $_SESSION['userid'];
        $site_id = SITE_FOURSQUARE;
        $stmt->bind_param("iis", $user_id, $site_id, $id);
        $stmt->execute();
        if($stmt->affected_rows == 1)
        {
            //echo "Succeeded in recording badge<br/>";
        } else {
            $failures[] = "Failed to record badge";
        }
        $stmt->close();

    }
}

if(count($failures) > 0) {
    echo '{ "error" : "Failure(s) encountered when trying to update badge list"}';
} else {
    echo '{"status":"success"}';
}