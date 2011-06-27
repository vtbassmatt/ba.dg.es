<?php

define("SITE_FOURSQUARE", 1);

define("MYSQL_HOST", "mysql.mattandchristy.net");
define("MYSQL_DB",   "badges_test");
define("MYSQL_USER", "badges_tester");
define("MYSQL_PASS", "b4dg3s!!");


$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
if(mysqli_connect_errno()) {
    echo "Failed to connect to database: " . mysqli_connect_error();
    exit();
}

?>