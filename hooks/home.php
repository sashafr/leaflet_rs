<?php
function HookLeaflet_rsHomeAdditionalheaderjs() {
?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
<?php
} //end additionalheaderjs hook

//put map, selector, and keyword selector on home page
function HookLeaflet_rsHomeHomebeforepanels() {
?>
    <div id="leaflet_rs_map"></div>

    <div id="selector"></div>

    <select id = "keywordSelect">
    <option value="koala">koala</option>
    <option value="politicians ruin everything">politicians ruin everything</option>
    </select>
    <input type="submit" id = "searchButton" onclick="searchPoints();" value="Search by name"/>
    <input type = "button" id = "showAll" value = "Show all" onClick = "showAll();"/>
<?php
} //end homebeforepanels hook

function HookLeaflet_rsHomeFooterbottom() {

    //database configuration information -- from var/www/resourcespace/include/config.php
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

    //get resources with lat, long and researchID to add to map
    $query = 'SELECT r.ref, r.geo_lat, r.geo_long, r.field8 as title, rd1.value as researchID, twitter, facebook, instagram
              FROM resource r
              INNER JOIN resource_data rd1 ON r.ref=rd1.resource
              LEFT JOIN (select resource, value as twitter from resource_data where resource_type_field = 84) as rd2 on rd1.resource = rd2.resource
              LEFT JOIN (select resource, value as facebook from resource_data where resource_type_field = 85) as rd3 on rd1.resource = rd3.resource
              LEFT JOIN (select resource, value as instagram from resource_data where resource_type_field = 85) as rd4 on rd1.resource = rd4.resource
              WHERE r.geo_lat != "NULL" and rd1.resource_type_field = 88;';

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
                    'coordinates' => [(float)$row["geo_long"], (float)$row["geo_lat"]]
                  ),
                'properties' => array(
                    'name' => $row["title"],
                    'ref' => $row["ref"],
                    'researchID' => $row["researchID"],
                    'twitter' => $row["twitter"],
                    'facebook' => $row["facebook"],
                    'instagram' => $row["instagram"],
                    'path' => get_resource_path($row["ref"],true, "",true)
                    )
            );
    }
?>

    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>

    <script>
    //create map
    var map = L.map('leaflet_rs_map');
    //add streets layer to map
    L.esri.basemapLayer('Streets').addTo(map);

    //add popups to map features

    function onEachFeature(feature, layer) {
        var html = "<a href='http://45.55.57.30/resourcespace/plugins/leaflet_rs/pages/direct_view.php?ref=" + feature.properties.ref + "&researchID=" + feature.properties.researchID + "'><img src='http://45.55.57.30/resourcespace/filestore/" + feature.properties.path.substring(44) + "'" + "&size=thm' width=100 height=80 ></a>";
        if (feature.properties && feature.properties.name && feature.properties.ref) {
            var twitterPopup;
            var facebookPopup;
            var instagramPopup;
            //if Twitter, Facebook and/or Instagram accounts are provided, display links to them; otherwise display nothing
            if (feature.properties.twitter){
                var twitterPopup = "<div style = 'padding:0px; margin:0px 0px 12px 0px; height: 10px; font-family:Helvetica Neue Pro; font-weight:bold';>"
                +  "<img src = 'http://45.55.57.30/resourcespace/plugins/leaflet_rs/Twitter_Social_Icon_Circle_Color.png' style='padding: 4px 4px 4px 0px; vertical-align: middle; width:16px; height:16px';>"
                + "<a href='https://twitter.com/" +
                 feature.properties.twitter + "'style='font-size: 8px';>" + "@" + feature.properties.twitter + "</a></div>";
            }

            else {
                var twitterPopup = "";
            }

            if (feature.properties.facebook){
                var facebookPopup = "<div style = 'padding:0px; margin:0px 0px 0px 0px; height: 4px; font-family:Helvetica Neue Pro; font-weight:bold';>"
                +  "<img src = 'http://45.55.57.30/resourcespace/plugins/leaflet_rs/FB-f-Logo__blue_29.png' style='padding: 0px 4px 0px 0px; vertical-align: middle; width:16px; height:16px';>"
                + "<a href='https://facebook.com/" +
                feature.properties.facebook + "'style='font-size: 8px';>" + feature.properties.facebook + "</a></div>" + "<br/>";
            }

            else {
                var facebookPopup = "";
            }

            if (feature.properties.instagram){
                var instagramPopup = "<div style = 'padding:0px; margin:0px 0px 0px 0px; height: 8px; font-family:Helvetica Neue Pro; font-weight:bold';>"
                +  "<img src = 'http://45.55.57.30/resourcespace/plugins/leaflet_rs/glyph-logo_May2016.png' style='padding: 0px 4px 0px 0px; vertical-align: middle; width:16px; height:16px';>"
                + "<a href='https://www.instagram.com/" +
                 feature.properties.instagram + "/" + "'style='font-size: 8px';>" + feature.properties.instagram + "</a></div>";
            }

            else {
                var instagramPopup = "";
            }

            layer.bindPopup("<b>"+"NAME: "+"</b>"+ feature.properties.name + "<br />" +"<br />"+ html + "<br />"  + twitterPopup + facebookPopup + instagramPopup + "<br />");
        } //end outer if statement
    }; //end onEachFeature function

    //format map markers
    var geojsonMarkerOptions = {
        radius: 4,
        fillColor: "#6495ED",
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
    map.fitBounds(jsonLyr.getBounds(), {padding: [10, 10]});

    //restrict geocoder searchBounds to the greater Philadelphia area
    var corner1 = L.latLng(40.11194, -75.30556);
    var corner2 = L.latLng(39.84556, -74.95556);
    bounds = L.latLngBounds(corner1, corner2);
    var geoOptions = {
        title: "Search Location",
        searchBounds: bounds
    };

    //create geocoder
    var searchControl = L.esri.Geocoding.geosearch(geoOptions).addTo(map);
    var results = L.layerGroup().addTo(map);

    //listen for the results event and add every result to the map
    searchControl.on("results", function(data) {
        results.clearLayers();
        for (var i = data.results.length - 1; i >= 0; i--) {
            results.addLayer(L.marker(data.results[0].latlng));
        }
    });

    //Add all points back onto the map
    function showAll(){
        jsonLyr.addTo(map);
        map.fitBounds(jsonLyr.getBounds(), {padding: [10, 10]});
    };

    var pointsLayer = new L.FeatureGroup();

    //Select points by attribute
    function searchPoints(){
        var jsonLyr2 = jsonLyr;
        var title = document.getElementById('keywordSelect').value;
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
        map.fitBounds(latlngbounds, {padding: [10, 10]});
    };

    var selector = L.control();

    //Create selector that will populate dynamically
    selector.onAdd = function(map) {
        //create div container
        var div = L.DomUtil.create('div', 'mySelector');
        //create select element within container (with id, so it can be populated later
        div.innerHTML = '<select id="name_select"><option value="init">(select name)</option></select>';
        return div;
    };
    selector.addTo(map);

    //Put the selector in a new div that is outside the map and beside the other search otpions
    var newSelectorDiv = document.getElementById('selector');
    var nameSelect = document.getElementById('name_select');
    newSelectorDiv.appendChild(nameSelect);

    //Add each name to a list
    var myList = [];
    jsonLyr.eachLayer(function(layer) {
        myList.push(layer.feature.properties.name);
    });

    //sort the list
    var sortedList = myList.sort();
    //add the items in the sorted list into the selector
    for (var i = 0; i < sortedList.length; i++) {
        var optionElement = document.createElement("option");
        optionElement.innerHTML = myList[i];
        L.DomUtil.get("name_select").appendChild(optionElement);
    }

    var name_select = L.DomUtil.get("name_select");

    //prevent clicks on the selector from propagating through to the map
    //(otherwise popups will close immediately after opening)
    L.DomEvent.addListener(name_select, 'click', function(e) {
        L.DomEvent.stopPropagation(e);
    });

    //Listen for user to select item from the dropdown
    L.DomEvent.addListener(name_select, 'change', changeHandler);

    //When a name is selected, open that monument's popup
    function changeHandler(e) {
        if (e.target.value == "init") {
            map.closePopup();
        } else {
            jsonLyr.eachLayer(function(layer) {
            if (layer.feature.properties.name == e.target.value){
                layer.openPopup();
            }});
            }
    }
    </script>

<?php
} //end footerbottom hook
?>
