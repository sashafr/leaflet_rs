<?php global $baseurl; ?>
<head>
  <script src="https://use.typekit.net/sas2bse.js"></script>
  <script>try{Typekit.load({ async: true });}catch(e){}</script>
  <link rel="stylesheet" type="text/css" href="<?php echo $baseurl?>/plugins/leaflet_rs/css/style.css" />
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <div id="navbar" class="w3-bar w3-mobile">
    <a id="home_button" href="<?php echo $baseurl?>/pages/home.php" class="w3-bar-item w3-button w3-mobile">View Map</a>
  </div>
</head>
<body id="direct_view_page"><div id="fb-root">
</div>
  <script>
  //code from facebook for share button    (function(d, s, id) {        var js, fjs = d.getElementsByTagName(s)[0];        if (d.getElementById(id)) return;        js = d.createElement(s); js.id = id;        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.9";        fjs.parentNode.insertBefore(js, fjs);    } (document, 'script', 'facebook-jssdk'));
  </script>
  <?php

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

if(empty($_REQUEST['ID'])){
  //get ref
  $proposal_ref  =  htmlspecialchars($_GET["ref"]);

  // get ID from ref
  $id_query = "SELECT rd.value as ID
  FROM resource r
  INNER JOIN resource_data rd on r.ref = rd.resource
  WHERE r.ref =" . $proposal_ref . " and rd.resource_type_field = 88;";

  $ID_results = mysqli_query($connection, $id_query);
  while ($row = mysqli_fetch_assoc($ID_results)) {
    $ID = $row['ID'];
  }
}

else {
  //get researchID from URL
  $ID =  htmlspecialchars($_GET["ID"]);
}

//get metadata for the resource with the researchID in the URL
$query = "SELECT r.ref, r.field8 as title, rd1.value as ID, credit, age, r.field3 as zipcode, location,twitter, facebook, instagram, transcription
          FROM resource r
          INNER JOIN resource_data rd1 ON r.ref=rd1.resource
          LEFT JOIN (select resource, value as age from resource_data where resource_type_field = 89) as rd2 on rd1.resource = rd2.resource
          LEFT JOIN (select resource, value as twitter from resource_data where resource_type_field = 84) as rd3 on rd1.resource = rd3.resource
          LEFT JOIN (select resource, value as facebook from resource_data where resource_type_field = 85) as rd4 on rd1.resource = rd4.resource
          LEFT JOIN (select resource, value as instagram from resource_data where resource_type_field = 86) as rd5 on rd1.resource = rd5.resource
          LEFT JOIN (select resource, value as credit from resource_data where resource_type_field = 10) as rd6 on rd1.resource = rd6.resource
          LEFT JOIN (select resource, value as transcription from resource_data where resource_type_field = 91) as rd7 on rd1.resource = rd7.resource
          LEFT JOIN (select resource, value as location from resource_data where resource_type_field = 90) as rd8 on rd1.resource = rd8.resource
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
    echo "<h1 class=\"error_message\">The data team is processing this proposal.</h2><br />";
    echo "<h2 class=\"error_message\"> While you wait for the image to be transcribed and analyzed, please explore the map on the home page and check back soon!</h2><br />";

}

//if there were two files with the same research ID, display the first one
if(mysqli_num_rows($result) > 1){
    $result = $result[0];
}

//print the metadata
//add tweet button to the title div
while ($row = mysqli_fetch_assoc($result)) {
    echo "<header class=\"w3-content w3-container w3-center w3-mobile\" id=\"titleWrapper\"><h1 id=\"title\">";
    echo $row['title'];
    echo "</h1></header><br/>";

    echo "<div id=\"parent_container\" class=\"w3-row-padding\">"; //start w3-row for metadata and image
        echo "<div class=\"w3-content w3-container w3-mobile\" id=\"metadata_container\">";
        foreach($row as $label => $data){
            if (in_array($label, array("twitter", "facebook", "instagram")) && !empty($data)){
                if ($data[0] === '@'){
                    $data = substr($data,1);
                }
                echo "<p class=\"metadata_label\">" . ucfirst($label) . "</p><a id=\"social_metadata\" href=\"https://www." . $label . ".com/" . $data . "\" target=\"_blank\">" . '@' . $data . "</a>";
            }
            elseif (!in_array($label, array('ID','title','ref','twitter','facebook','instagram','transcription')) && !empty($data)){
            echo "<p><span class=\"metadata_label\">" . ucfirst($label) .": ". "</span><span class=\"metadata\">" . $data . "</span></p>";
            }
            elseif ($label == 'transcription' && !empty($data)) {
              echo "<p><button class=\"w3-button\" id=\"transcriptButton\" onclick=\"showTranscription()\">". 'View ' . ucfirst($label) ."</button></p>";
              echo "<div id=\"transcription\" style = \"display: none\">";
              echo $data;
              echo "</div>";
            }
        }
    echo "</div>";

    //show the image
    echo "<div class=\"w3-content w3-container w3-mobile\" id=\"proposal_img_container\">";
    $imgref = $row['ref'];
    echo "<img id=\"proposal_img\" src=\"".$baseurl."/pages/download.php?ref=".$imgref."\" alt=\"This image is not currently available.\">";
    echo "</div>";

    echo "<div id= \"sharebuttons_container\" class=\"w3-content w3-container w3-center w3-mobile\" ><a href=\"https://twitter.com/share\" data-size = \"large\" class=\"twitter-share-button\" data-show-count=\"false\">Tweet</a><script async src=\"//platform.twitter.com/widgets.js\" charset=\"utf-8\"></script></div>";
    echo "</div>"; //end w3 row
}

    include "/var/www/resourcespace/include/config.php";
    ?>
    <div class="clearer"></div>
    <div id="footerdiv">
        <div class="footertext"><p>Research system created with support from   <a href="https://github.com/sashafr/leaflet_rs"><img src="<?php echo $baseurl ?>/plugins/leaflet_rs/assets/ds_team_stamp_tiny.png" /></a>&nbsp;&nbsp;&&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://pricelab.sas.upenn.edu/"><img src="<?php echo $baseurl ?>/plugins/leaflet_rs/assets/price_lab_logo_tiny.png" /></a></div>
    </div>
<script>
/* When image clicked, open in new window or tab - open im_preview page*/
var proposal_element = document.getElementById("proposal_img")
proposal_element.addEventListener('click', function(e) {
        window.open("img_preview.php?ref=" + <?php echo $imgref; ?>);
    })

function showTranscription() {
  var x = document.getElementById('transcription');
  if (x.style.display === 'none') {
    x.style.display = 'block';
  } else {
    x.style.display = 'none';
  }
}
</script>

<script> // add facebook share button to the title div
var title = document.getElementById('sharebuttons_container');
title.innerHTML += "<br/>";
title.innerHTML += '<div style = "margin-top: 8px" class="fb-share-button" data-href="' + document.URL + '" data-layout="button_count" data-size="large" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse">Share</a></div>';
</script>
</body>
