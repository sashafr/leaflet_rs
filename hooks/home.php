<?php
function HookLeaflet_rsHomeAdditionalheaderjs() {
    ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>

    <?php
}
function HookLeaflet_rsHomeHomebeforepanels() {
    ?>

    <style>
      #query {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        padding: 1em;
      }

      #query select {
        font-size: 16px;
      }
    </style>

    <div id="leaflet_rs_map"> </div>

    <select id = "nameSelect">
		<option value="Monument Lab Image">Monument Lab Image</option>
		<option value="doc_test">doc_test</option>
		<option value="ace_test">ace_test</option>
		</select>

		<input type="submit" onclick="searchPoints();" value="Search by name"/>

    <input type = "button" value = "Show all" onClick = "showAll();"/>

    <?php
}

function HookLeaflet_rsHomeFooterbottom() {

    //database configuration information -- from var/www/resourcespace/include/config.php
    include "/var/www/resourcespace/include/config.php";
    $host = $mysql_server;
    $user = $mysql_username;
    $pass = $mysql_password;
    $database = "resourcespace";

    //static $connection; //avoid connection with every query

    //open connection to mySQL server
    $connection = mysqli_connect($host, $user, $pass, $database);
    if (!$connection) {
      //die("Not connected : " . mysql_error());
      echo "Not connected : " . mysqli_connect_error();
    }

    //query rows in resource table that have a lat and long
    $query = 'SELECT field8, geo_lat, geo_long FROM resource WHERE geo_lat != "NULL"';

    //query returns title, geo_lat, geo_long
    $result = mysqli_query($connection, $query);
    if (!$result) {
      echo "Invalid query: " . mysqli_error($connection);
    }

    //write query results in geoJSON format array
    while ($row = mysqli_fetch_assoc($result)) {
            $to_geojson[] = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => [(float)$row["geo_long"], (float)$row["geo_lat"]],
                  ),
                'properties' => array(
                    'name' => $row["field8"],
                    )
            );
       }

    ?>

<!--    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script> -->
    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>

    <script>
    //create map
    var map = L.map('leaflet_rs_map');
    //.setView([39.9526, -75.1652], 13);
    L.esri.basemapLayer('Streets').addTo(map);

    //add popups to map features
    function onEachFeature(feature, layer) {
        // does this feature have a property named popupContent?
        if (feature.properties && feature.properties.name) {
            layer.bindPopup("<b>"+"NAME: "+"</b>"+ feature.properties.name + "<br />"  +"<br />"+ "<a href='http://45.55.57.30/resourcespace/plugins/ref_urls/file.php?ref=1'><img src='http://45.55.57.30/resourcespace/plugins/ref_urls/file.php?ref=1&size=thm' width=60 height=50 ></a>", {maxWidth:60});
        }
    };

    //format map markers
    var geojsonMarkerOptions = {
        radius: 4,
        fillColor: "#ff7800",
        color: "#000",
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
    };

    //get monument points from geojson array
    var jsonPts = <?php echo json_encode($to_geojson); ?>;

    //adds momuments to map
    var jsonLyr = L.geoJson(jsonPts, {
        onEachFeature: onEachFeature
        , pointToLayer: function (feature, latlng) {
      var marker = L.circleMarker(latlng, geojsonMarkerOptions);
        return marker;
        }
      });
      jsonLyr.addTo(map);
      map.fitBounds(jsonLyr.getBounds());

    //restrict geocoder searchBounds to the greater Philadelphia area
    var corner1 = L.latLng(40.11194, -75.30556);
    var corner2 = L.latLng(39.84556, -74.95556);
    bounds = L.latLngBounds(corner1, corner2);

    var geoOptions = {
        title: "Search Location",
        searchBounds: bounds
    };

    //Create geocoder
    var searchControl = L.esri.Geocoding.geosearch(geoOptions).addTo(map);
    var results = L.layerGroup().addTo(map);

    // listen for the results event and add every result to the map
    searchControl.on("results", function(data) {
        results.clearLayers();
        for (var i = data.results.length - 1; i >= 0; i--) {
            results.addLayer(L.marker(data.results[0].latlng));
        }
    });

    //Add all points back onto the map
    function showAll(){
        jsonLyr.addTo(map);
        map.fitBounds(jsonLyr.getBounds());
    };

    var pointsLayer = new L.FeatureGroup();

    //Select points by attribute
    function searchPoints(){
        var jsonLyr2 = jsonLyr;
        var title = document.getElementById('nameSelect').value;
        map.removeLayer(jsonLyr);
        pointsLayer.clearLayers();
        jsonLyr2.eachLayer(function(layer) {
          if (layer.feature.properties.name == title) {
            pointsLayer.addLayer(layer);
            map.addLayer(pointsLayer);
          }
        });
        //set bounds to the selected features
        var latlngbounds = new L.latLngBounds(pointsLayer.getBounds());
        map.fitBounds(latlngbounds);
    };
    </script>

<?php
}
?>
