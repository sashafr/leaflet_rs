
<?php
function HookLeaflet_rsThemesThemeHeader() {

    $host = "localhost";
    $user = "root";
    $pass = "Violet7Rift";
    $database = "resourcespace";

    //static $connection; //avoid connection with every query

    // Opens a connection to a mySQL server
    $connection = mysqli_connect($host, $user, $pass, $database);
    if (!$connection) {
      //die("Not connected : " . mysql_error());
      echo "Not connected : " . mysqli_connect_error();
    }

    // Search the rows in the markers table
    $query = "SELECT field8, geo_lat, geo_long FROM resource";

    $result = mysqli_query($connection,$query);
    if (!$result) {
      echo "Invalid query: " . mysqli_error($connection);
    }

    $geojson = array(
      'type' => 'Feature',
      'geometry' => array()
    );

    while ($geojson = mysqli_fetch_assoc($result)) {
           echo $geojson["field8"], $geojson["geo_lat"], $geojson["geo_long"];
       }


} //end hook

?>
