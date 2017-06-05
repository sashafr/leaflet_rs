<?php
#get researchID from URL
$researchID =  htmlspecialchars($_GET["researchID"]);
echo 'ResearchID from URL: ', $researchID;
echo '<br />'; echo '<br />';

#get mysql access information from config.php
include "/var/www/resourcespace/include/config.php";
$host = $mysql_server;
$user = $mysql_username;
$pass = $mysql_password;
$database = $mysql_db;

//open connection to mySQL server
$connection = mysqli_connect($host, $user, $pass, $database);
if (!$connection) {
    echo "Not connected : " . mysqli_connect_error();
}

//get metadata for the resource with the researchID in the URL
$query = 'SELECT r.ref, r.field8 as title, rd1.value as researchID, rd2.value as age, r.field3 as zipcode
          FROM resource r
          INNER JOIN resource_data rd1 ON r.ref=rd1.resource
          INNER JOIN resource_data rd2 ON rd1.resource=rd2.resource
          WHERE rd1.resource_type_field = 88 and rd1.value = ' . $researchID . ' and rd2.resource_type_field = 89;';

//query returns title, researchID, age, zipcode
$result = mysqli_query($connection, $query);
if (!$result) {
    echo "Invalid query: " . mysqli_error($connection);
}

//print the metadata
while ($row = mysqli_fetch_assoc($result)) {
  foreach($row as $label => $data){
      echo "<div id=\"metadata\">";
      echo $label . ": " . $data;
      echo "</div>";
  }
  //show the image using ref_urls
  $imgref = $row['ref'];
  echo "<div id=\"clickthru_img\">";
  echo "<img src=\"http://45.55.57.30/resourcespace/plugins/ref_urls/file.php?ref=" . $imgref . "\" alt=\"Your Proposal Should Load Here\">";
  echo '</div>';
}
?>
