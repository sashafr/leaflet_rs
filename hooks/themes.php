
<?php
//**** THIS CODE WILL BE MOVED TO HOME.php, homebeforepanels hook FOR USE WITH LEAFLET MAP ****
function HookLeaflet_rsThemesThemeHeader() {

    //database configuration information -- from var/www/resourcespace/include/config.php
    $host = "localhost";
    $user = "root";
    $pass = "Violet7Rift";
    $database = "resourcespace";

    //static $connection; //avoid connection with every query

    //open connection to mySQL server
    $connection = mysqli_connect($host, $user, $pass, $database);
    if (!$connection) {
      //die("Not connected : " . mysql_error());
      echo "Not connected : " . mysqli_connect_error();
    }

    //query rows in resource table that have a lat and long
    $query = 'SELECT field8, geo_lat, geo_long FROM resource WHERE geo_lat != "NULL" LIMIT 1';

    //query returns title, geo_lat, geo_long
    $result = mysqli_query($connection, $query);
    if (!$result) {
      echo "Invalid query: " . mysqli_error($connection);
    }

    //write query results in geoJSON format array
    while ($rows = mysqli_fetch_assoc($result)) {
            $to_geojson = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => [$rows["geo_lat"], $rows["geo_long"]],
                    ),
                'properties' => array(
                    'name' => $rows["field8"],
                    )
            );
       }

       // encodes the array into a string in JSON format (JSON_PRETTY_PRINT - uses whitespace in json-string, for human readable)
       $geojson = json_encode($to_geojson, JSON_PRETTY_PRINT);
       echo 'test database retrieval to geoJSON <br>';
       echo $geojson;

} //end hook

?>
