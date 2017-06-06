<head>
<link rel="stylesheet" type="text/css" href="../css/style.css" />
<div class="w3-bar">
    <a id="home_button" href="http://45.55.57.30/resourcespace/pages/home.php" class="button">Back to Map</a>
</div>
</head>
<body id="direct_view_page">

<?php
if(empty($_REQUEST['ID'])){
    echo "<br /><h1 class=\"error_message\">There appears to be an error in your URL. The correct form is: </h2>";
    echo "<h2 class=\"error_message\">www....com/.../pages/direct_view.php?ID=<span style=\"color:blue\">*Insert Your Research ID Here*</span></h2>";
}

else {
#get researchID from URL
$ID =  htmlspecialchars($_GET["ID"]);

#get mysql access information from config.php
include "/var/www/resourcespace/include/config.php";
$host = $mysql_server;
$user = $mysql_username;
$pass = $mysql_password;
$database = $mysql_db;

//open connection to mySQL server
$connection = mysqli_connect($host, $user, $pass, $database);
if (!$connection) {
    echo "This page is not currently available. Please check back soon."; //for user
    //echo "Not connected : " . mysqli_connect_error(); //for debugging
}

//get metadata for the resource with the researchID in the URL
$query = "SELECT r.ref, r.field8 as title, rd1.value as ID, credit, rd2.value as age, r.field3 as zipcode, twitter, facebook, instagram
          FROM resource r
          INNER JOIN resource_data rd1 ON r.ref=rd1.resource
          INNER JOIN resource_data rd2 ON rd1.resource=rd2.resource
          LEFT JOIN (select resource, value as twitter from resource_data where resource_type_field = 84) as rd3 on rd2.resource = rd3.resource
          LEFT JOIN (select resource, value as facebook from resource_data where resource_type_field = 85) as rd4 on rd2.resource = rd4.resource
          LEFT JOIN (select resource, value as instagram from resource_data where resource_type_field = 85) as rd5 on rd2.resource = rd5.resource
          LEFT JOIN (select resource, value as credit from resource_data where resource_type_field = 10) as rd6 on rd2.resource = rd6.resource
          WHERE rd1.resource_type_field = 88 and rd1.value = '" . $ID . "' and rd2.resource_type_field = 89;";

//query returns title, researchID, age, zipcode
$result = mysqli_query($connection, $query);

//error message if debugging
/*
if (!$result) {
    echo "Invalid query: " . mysqli_error($connection);
}
*/

//error message for user if ID isn't in database
if(mysqli_num_rows($result) == 0){
    echo "<br />";
    echo "<h1 class=\"error_message\">This resource may still be processing. Explore the map on the home page and check back soon!</h2><br />";
}

//print the metadata
while ($row = mysqli_fetch_assoc($result)) {
    echo "<div id=\"title\">";
    echo $row['title'];
    echo "</div>";
    echo "<div id=\"metadata_container\">";
    foreach($row as $label => $data){
        if (in_array($label, array("twitter", "facebook", "instagram")) && !empty($data)){
        echo "<span class=\"metadata_label\">" . ucfirst($label) . "</span><br/><a id=\"social_metadata\" href=\"https://www." . $label . ".com/" . $data . "\" target=\"_blank\">" . $data . "</a><br/><br/>";
        }
        elseif (!in_array($label, array('title','ref','twitter','facebook','instagram')) && !empty($data)){
        echo "<span class=\"metadata_label\">" . ucfirst($label) . "</span><br/><span class=\"metadata\">" . $data . "</span><br/><br/>";
        }
    }
    echo "</div>";
    //show the image using ref_urls
    $imgref = $row['ref'];
    echo "<div id=\"clickthru_img\">";
    echo "<img src=\"http://45.55.57.30/resourcespace/plugins/ref_urls/file.php?ref=" . $imgref . "\" alt=\"Your Proposal Should Load Here\">";
    echo '</div>';
}
} //end else
?>
</body>
