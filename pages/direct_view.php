<?php

$researchID =  htmlspecialchars($_GET["researchID"]) . '!';
echo 'Direct URL from ResearchID';
echo '<br />'

/*
include "/var/www/resourcespace/include/config.php";
$host = $mysql_server;
$user = $mysql_username;
$pass = $mysql_password;
$database = $mysql_db;

//static $connection; //avoid connection with every query

//open connection to mySQL server
$connection = mysqli_connect($host, $user, $pass, $database);
if (!$connection) {
    echo "Not connected : " . mysqli_connect_error();
}

//get resources with lat, long and researchID to add to map
$query = 'SELECT r.field8 as title, r.field3 as zipcode, rd1.value as researchID, rd2.value as age
          FROM resource r
          INNER JOIN resource_data rd1 ON r.ref=rd1.resource
          INNER JOIN resource_data rd2 ON r.ref=rd2.resource
          WHERE geo_lat != "NULL" and rd1.resource_type_field =' . $researchID . ' and rd2.resource_type_field = 89;';

//query returns title, geo_lat, geo_long
$result = mysqli_query($connection, $query);
if (!$result) {
    echo "Invalid query: " . mysqli_error($connection);
}

while ($row = mysqli_fetch_assoc($result)) {
  echo $row;
}
*/
?>
