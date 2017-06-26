<head>
    <link rel="stylesheet" type="text/css" href="../css/style.css" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <div id="navbar" class="w3-bar w3-mobile">
        <a id="home_button" href="http://45.55.57.30/resourcespace/pages/home.php" class="w3-bar-item w3-button w3-mobile">View Map</a>
    </div>
</head>

<body id="direct_view_page">

<div id="fb-root"></div>
<script>
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.9";
        fjs.parentNode.insertBefore(js, fjs);
    } (document, 'script', 'facebook-jssdk'));
</script>

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
$query = "SELECT r.ref, r.field8 as title, rd1.value as ID, credit, age, r.field3 as zipcode, twitter, facebook, instagram
          FROM resource r
          INNER JOIN resource_data rd1 ON r.ref=rd1.resource
          LEFT JOIN (select resource, value as age from resource_data where resource_type_field = 89) as rd2 on rd1.resource = rd2.resource
          LEFT JOIN (select resource, value as twitter from resource_data where resource_type_field = 84) as rd3 on rd1.resource = rd3.resource
          LEFT JOIN (select resource, value as facebook from resource_data where resource_type_field = 85) as rd4 on rd1.resource = rd4.resource
          LEFT JOIN (select resource, value as instagram from resource_data where resource_type_field = 86) as rd5 on rd1.resource = rd5.resource
          LEFT JOIN (select resource, value as credit from resource_data where resource_type_field = 10) as rd6 on rd1.resource = rd6.resource
          WHERE rd1.resource_type_field = 88 and rd1.value = '" . $ID . "' and r.ref > 0;";

//query returns title, researchID, age, zipcode
$result = mysqli_query($connection, $query);

//error message if debugging
/*if (!$result) {
    echo "Invalid query: " . mysqli_error($connection);
} */

//error message for user if ID isn't in database
if(mysqli_num_rows($result) == 0){
    echo "<br />";
    echo "<h1 class=\"error_message\">This resource may still be processing. Explore the map on the home page and check back soon!</h2><br />";
}

//if there were two files with the same research ID, display the first one
if(mysqli_num_rows($result) > 1){
    $result = $result[0];
}

//print the metadata
//add tweet button to the title div
while ($row = mysqli_fetch_assoc($result)) {
    echo "<header class=\"w3-container w3-center w3-mobile\"><h1 id=\"title\">";
    echo '<div id = "shareButtons" class="w3-container w3-quarter w3-center w3-mobile" style = "float:right; position:relative; top: -50%" ><a href="https://twitter.com/share" data-size = "large" class="twitter-share-button" style = "margin-bottom: 10px" data-show-count="false">Tweet</a><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script></div>';
    echo $row['title'];
    echo "</h1></header><br/>";
    echo "<div class=\"w3-row-padding\">"; //start w3-row for metadata and image
        echo "<div class=\"w3-container w3-quarter w3-center w3-mobile\" id=\"metadata_container\">";
        foreach($row as $label => $data){
            if (in_array($label, array("twitter", "facebook", "instagram")) && !empty($data)){
            echo "<p class=\"metadata_label\">" . ucfirst($label) . "</p><a id=\"social_metadata\" href=\"https://www." . $label . ".com/" . $data . "\" target=\"_blank\">@" . $data . "</a>";
            }
            elseif (!in_array($label, array('title','ref','twitter','facebook','instagram')) && !empty($data)){
            echo "<p class=\"metadata_label\">" . ucfirst($label) . "</p><p class=\"metadata\">" . $data . "</p>";
            }
        }
        echo "</div>";
        //show the image
        $imgref = $row['ref'];
        echo "<div class=\"w3-container w3-threequarter w3-mobile\" id=\"proposal_img_container\">";
        echo "<img id=\"proposal_img\" src=\"http://45.55.57.30/resourcespace/pages/download.php?ref=".$imgref."\" alt=\"This image is not currently available.\">";
        echo "</div>";
    echo "</div>"; //end w3 row
}
} //end else
?>
<script>
//add facebook share button to the title div
var title = document.getElementById('shareButtons');
title.innerHTML += "<br/>";
title.innerHTML += '<div style = "margin-top: 8px" class="fb-share-button" data-href="' + document.URL + '" data-layout="button_count" data-size="large" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse">Share</a></div>';
</script>
</body>
