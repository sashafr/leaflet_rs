<?php

function HookLeaflet_rsHomeAdditionalheaderjs() {


?>

<!--W3 STYLEHEET -->
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

<!--NEIGHBORHOODS LAYER -->
<script src="http://45.55.57.30/resourcespace/plugins/leaflet_rs/neighborhoods.js"></script>


<!--LEAFLET LIBRARIES -->
<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin=""/>

<!--ESRI LEAFLET LIBRARIES-->
<link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
<script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
<script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>


<!-- MARKER CLUSTER LIBRARIES -->
<link rel="stylesheet" href="http://45.55.57.30/resourcespace/plugins/leaflet_rs/MarkerCluster.css" />
<link rel="stylesheet" href="http://45.55.57.30/resourcespace/plugins/leaflet_rs/MarkerCluster.Default.css" />
<script src="http://45.55.57.30/resourcespace/plugins/leaflet_rs/leaflet.markercluster-src.js"></script>
<script src="http://45.55.57.30/resourcespace/plugins/leaflet_rs/leaflet.markercluster.js"></script>


<?php

} //end additionalheaderjs hook

function HookLeaflet_rsHomeHomebeforepanels() {


?>
    <div class="w3-row w3-col" style="width:100%">
        <div id="map_container" class="w3-content w3-mobile">
        <div id="leaflet_rs_map"></div>
        </div>
    </div>
<?php
    include "/var/www/resourcespace/include/config.php";
    echo '<div id = "footerBar" class = "w3-row w3-mobile">
        <div class = "w3-center">
        <a href = "http://monumentlab.muralarts.org">
        <img class="logo w3-mobile" src= "' . $baseurl . '/plugins/leaflet_rs/assets/ML_social-sharing.jpg" width = "100"/>
        </a>
        <a href = "https://www.muralarts.org/">
        <img class = "logo w3-mobile" src="' . $baseurl . '/plugins/leaflet_rs/assets/mural_arts_logo.svg" width = "280"/>
        </a>
        <a href = "https://pricelab.sas.upenn.edu/">
        <img class = "logo w3-mobile" src="' . $baseurl .'/plugins/leaflet_rs/assets/pricelab_logo.png" width = "140"/>
        </a>
        </div>
    </div>';
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

    //get resources that have a location
    $query = 'SELECT r.ref, r.geo_lat, r.geo_long, r.field8 as title, r.field3 as zipcode, rd1.value as researchID, age, twitter, facebook, instagram,
                 (SELECT GROUP_CONCAT(n.name SEPARATOR \', \')
                  FROM resource_node rn
                  LEFT JOIN node n ON rn.node = n.ref
                  WHERE rn.resource=r.ref
                  GROUP BY rn.resource) as keywords
              FROM resource r
              INNER JOIN resource_data rd1 ON r.ref=rd1.resource
              LEFT JOIN (select resource, value as twitter from resource_data where resource_type_field = 84) as rd2 on rd1.resource = rd2.resource
              LEFT JOIN (select resource, value as facebook from resource_data where resource_type_field = 85) as rd3 on rd1.resource = rd3.resource
              LEFT JOIN (select resource, value as instagram from resource_data where resource_type_field = 86) as rd4 on rd1.resource = rd4.resource
              LEFT JOIN (select resource, value as age from resource_data where resource_type_field = 89) as rd5 on rd1.resource = rd5.resource
              WHERE r.geo_lat != "NULL" and rd1.resource_type_field = 88;';

    $result = mysqli_query($connection, $query);
    if (!$result) {
        echo "Invalid query: " . mysqli_error($connection);
    }

    //write query results to geoJSON format array
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
                'zipcode' => $row["zipcode"],
                'age' => $row["age"],
                'twitter' => $row["twitter"],
                'facebook' => $row["facebook"],
                'instagram' => $row["instagram"],
                'keywordArray' => explode(', ',  $row["keywords"]),
                'uppercaseName' => ucfirst(str_replace(['"', '\''], "", $row["title"])),
                'path' => get_resource_path($row["ref"],true, "",true),
                )
        );
    }
?>

    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>

    <script>

    //--------------------------------MAP---------------------------------------
    var map = L.map('leaflet_rs_map');

    //------------------------------LAYERS--------------------------------------
    //streets layer
    var tileLayer =  L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    //neighborhoods layer
    var neighborhoodsStyle = {"color": "#4b4ba0", "opacity": 0.8, "fillOpacity": 0.45, "weight": 1};
    var neighborhoodsLayer = L.geoJson(neighborhoods, {style: neighborhoodsStyle}).addTo(map);
    map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
    //remove the neighborhoods layer when the user zooms in past a certain point
    map.on('zoomend', function() {
        if (map.getZoom() > 14){
            if (map.hasLayer(neighborhoodsLayer)) {
                map.removeLayer(neighborhoodsLayer);
            }
        }
        if (map.getZoom() <= 14){
            if (map.hasLayer(neighborhoodsLayer)){
               console.log("layer already added");
            }
            else {
                neighborhoodsLayer.addTo(map);
                neighborhoodsLayer.bringToBack();
            }
        }
    });

    //creates a layer group of the neighborhoods layer and monument points
    //Used by the selectors to remove and redraw points
    var group = new L.LayerGroup(jsonLyr, neighborhoodsLayer);
    map.addLayer(group);

    //----------------------------MONUMENTS-------------------------------------
    //get monument points from geojson array
    var jsonPts = <?php echo json_encode($to_geojson); ?>;
    //format markers
    var geojsonMarkerOptions = {
        radius: 4,
        fillColor: "#fbaf3f",
        color: "#737373",
        weight: 1,
        fillOpacity: 1
    };
    var markers =   new L.markerClusterGroup();
    var selectedMarkers = new L.markerClusterGroup();
    var markersList = [];
    //adds momuments to map
    var jsonLyr = L.geoJson(jsonPts, {
        onEachFeature: onEachFeature
        , pointToLayer: function (feature, latlng) {
        //  layer.bindPopup(feature.properties.name);
        var marker = L.circleMarker(latlng, geojsonMarkerOptions);
        return marker;
        }
    });

    markers.addLayer(jsonLyr);
		markers.addTo(map);
    //jsonLyr.addTo(map);

    //------------------------------POP-UPS-------------------------------------
    function onEachFeature(feature, layer) {
        var html = "<a href='../plugins/leaflet_rs/pages/direct_view.php?ID=" + feature.properties.researchID +
            "'><img src='../filestore/" + feature.properties.path.substring(44) + "'" + "&size=thm' width=100 height=80 ></a>";
        var agePopup = "", zipcodePopup = "", socialMediaPopup = "";
        if (feature.properties && feature.properties.name && feature.properties.ref) {
            if (feature.properties.age){
                var agePopup =  "<b>"+"AGE: "+"</b>" + feature.properties.age + "<br/>";
            }
            if (feature.properties.zipcode){
                var zipcodePopup =  "<b>"+"ZIPCODE: "+"</b>" + feature.properties.zipcode + "<br/>";
            }
            if (feature.properties.twitter) {
                socialMediaPopup = socialMediaPopup + "<a href='https://twitter.com/" + feature.properties.twitter +
                "'><img class='social_icon' src ='../plugins/leaflet_rs/assets/Twitter_Social_Icon_Circle_White.png'></a>";
            }
            if (feature.properties.instagram) {
                socialMediaPopup = socialMediaPopup + "<a href='https://www.instagram.com/" + feature.properties.instagram +
                "'><img class='social_icon' src ='../plugins/leaflet_rs/assets/white_glyph-logo_May2016.png'></a>";
            }
            if (feature.properties.facebook) {
                socialMediaPopup = socialMediaPopup + "<a href='https://facebook.com/" + feature.properties.facebook +
                "'><img class='social_icon' src ='../plugins/leaflet_rs/assets/FB-f-Logo__white_29.png'></a>";
            }
            layer.bindPopup("<span style='font-family:sans-serif; font-size: 14px; letter-spacing:1px'><b>TITLE: </b>" + feature.properties.name + "<br />" +
                agePopup + zipcodePopup + "</span>" + "<br />" + html + "<br />"  + socialMediaPopup + "<br />", {autoClose: false});

            //uncomment this section for mouseover popups with momument title
            //show just the monument name on mouseover
            layer.on('mouseover', function(e) {
                //open popup;
                var popup = L.popup()
                .setLatLng(e.latlng)
                .setContent("<div style = 'font-family:sans-serif; letter-spacing:1px; font-weight:bold';>" + feature.properties.name + "</div>")
                .openOn(map);
            });
        }
    };

    //----------------------------GEOCODER--------------------------------------

    //restrict geocoder searchBounds to the greater Philadelphia area
    var corner1 = L.latLng(40.11194, -75.30556);
    var corner2 = L.latLng(39.84556, -74.95556);
    var markerOptions = {
      color: "#737373"
    };
    bounds = L.latLngBounds(corner1, corner2);
    var geoOptions = {title: "Search Location", searchBounds: bounds};
    var searchControl = L.esri.Geocoding.geosearch(geoOptions).addTo(map);
    var results = L.layerGroup().addTo(map);

    //listen for the results event and add every result to the map
    searchControl.on("results", function(data) {
        results.clearLayers();
        for (var i = data.results.length - 1; i >= 0; i--) {
           results.addLayer(L.marker(data.results[0].latlng, markerOptions));
        }
    });

    //Remove geocoder results if the user clicks elsewhere
    L.DomEvent.addListener(map, 'click', function(e) {
        results.clearLayers();
    });

    //-------------------------SHOW ALL BUTTON ---------------------------------

    var allSelector = L.control();
    allSelector.onAdd = function(map) {
        //create div container
        var div = L.DomUtil.create('div', 'mySelector4');
        //create select element within container (with id, so it can be populated later
        div.innerHTML = '<input class="w3-button w3-large w3-round" type="button" id="showAll" value="Show all" onClick="showAll();"/></div>';
        return div;
    };
    allSelector.addTo(map);

    function showAll(){
        selectedMarkers.clearLayers();
      //  jsonLyr.addTo(map);
        markers.addTo(map);
        map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
        //reset dropdown to default text
        var dropdowns = document.querySelectorAll(".selector")
        for (var i = 0; i < dropdowns.length; i++) {
            dropdowns[i].selectedIndex = 0;
        }
    };

    //--------------------------TITLE SELECTOR----------------------------------
    //create selector that will populate dynamically
    var titleSelector = L.control({position: 'bottomright'});
    titleSelector.onAdd = function(map) {
        //create div container
        var div = L.DomUtil.create('div', 'mySelector');
        //create select element within container (with id, so it can be populated later
        div.innerHTML = '<select id="name_select" class="selector"><option value="init">(select by title)</option></select>';
        return div;
    };
    titleSelector.addTo(map);

    //make list of titles
    var titleList = [];
    jsonLyr.eachLayer(function(layer) {
        titleList.push(layer.feature.properties.uppercaseName);
    });
    //sort dropdown list
    sortedTitles = titleList.sort();
    //put list in selector dropdown
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    for (var i = 0; i < sortedTitles.length; i++) {
        var optionElement = document.createElement("option");
        var capitalizeFirst = capitalizeFirstLetter(sortedTitles[i]);
        optionElement.innerHTML = capitalizeFirst;
        L.DomUtil.get("name_select").appendChild(optionElement);
    }
    var name_select = L.DomUtil.get("name_select");
    //prevent clicks on the selector from propagating through to the map
    //without this popups close immediately after opening
    L.DomEvent.addListener(name_select, 'click', function(e) {
        L.DomEvent.stopPropagation(e);
    });
    //listen for user to select item from the dropdown
    L.DomEvent.addListener(name_select, 'change', changeHandler);
    //when a name is selected, open that monument's popup
    function changeHandler(e) {
        searchPoints("name_select", "layer.feature.properties.uppercaseName");
        document.querySelector("#zip_select").selectedIndex = 0;
        document.querySelector("#key_select").selectedIndex = 0;
    }

    //-------------------------ZIPCODE SELECTOR---------------------------------
    //create selector by zip code
    var zipcodeSelector = L.control({position: 'bottomright'});
    zipcodeSelector.onAdd = function(map) {
        //create div container
        var div2 = L.DomUtil.create('div', 'mySelector2');
        //create select element within container (with id, so it can be populated later
        div2.innerHTML = '<select id="zip_select" class="selector"><option value="init">(select by zipcode of creator)</option></select>';
        return div2;
    };
    zipcodeSelector.addTo(map);
    //add each name to a list
    var myZipList = [];
    jsonLyr.eachLayer(function(layer) {
        //if the name is already in the list, don't add it
        if (isInArray(layer.feature.properties.zipcode, myZipList) == true) {
            return;
        }
        else {
            myZipList.push(layer.feature.properties.zipcode);
        }
    });
    //check to see if the item is already in the list
    function isInArray(value, array) {
        return array.indexOf(value) > -1;
    }
    var sortedZipList = myZipList.sort();
    //add the items in the sorted list into the selector
    for (var i = 0; i < sortedZipList.length; i++) {
        var optionElement = document.createElement("option");
        optionElement.innerHTML = sortedZipList[i];
        L.DomUtil.get("zip_select").appendChild(optionElement);
    }
    var zip_select = L.DomUtil.get("zip_select");
    //prevent clicks on the selector from propagating through to the map
    L.DomEvent.addListener(zip_select, 'click', function(e) {
        L.DomEvent.stopPropagation(e);
    });
    //listen for user to select item from the dropdown
    L.DomEvent.addListener(zip_select, 'change', changeHandler2);
    //when a zipcode is selected, remove all other points
    function changeHandler2() {
      searchPoints("zip_select", 'layer.feature.properties.zipcode');
      document.querySelector("#name_select").selectedIndex = 0;
      document.querySelector("#key_select").selectedIndex = 0;
    }

    //-------------------------KEYWORD SELECTOR---------------------------------
    var keywordSelector = L.control({position: 'bottomright'});
    //put keyword selector on map
    keywordSelector.onAdd = function(map) {
        var div = L.DomUtil.create('div', 'mySelector3');
        div.innerHTML = '<select id="key_select" class="selector"><option value="init">(select by keyword)</option></select>';
        return div;
    };
    keywordSelector.addTo(map);
    //populate dropdown
    var keywordList = [];
    jsonLyr.eachLayer(function(layer) {
        for (var i = 0; i < layer.feature.properties.keywordArray.length; i++){
            var word = layer.feature.properties.keywordArray[i];
            if (word && isInArray(word, keywordList) == false) {
                keywordList.push(word);
            }
        }
    });
    sortedKeywordList = keywordList.sort();
    for (var i = 0; i < sortedKeywordList.length; i++) {
        var optionElement = document.createElement("option");
        optionElement.innerHTML = sortedKeywordList[i];
        L.DomUtil.get("key_select").appendChild(optionElement);
    }
    var key_select = L.DomUtil.get("key_select");
    //call function on click
    L.DomEvent.addListener(key_select, 'change', changeHandler3);
    function changeHandler3(e) {
        selectKeywords();
        document.querySelector("#name_select").selectedIndex = 0;
        document.querySelector("#zip_select").selectedIndex = 0;
    }

    //----------FUNCTIONS EXECUTED ON SELECTOR DROPDOWN CLICKS------------------
    var pointsLayer = new L.FeatureGroup(); //selected points

    function selectKeywords() {
        var keyword = document.getElementById('key_select').value;
        var jsonLyr2 = jsonLyr;
        map.removeLayer(jsonLyr);
        map.removeLayer(markers);
        selectedMarkers.clearLayers();
        pointsLayer.clearLayers();
        map.removeLayer(group);
        jsonLyr2.eachLayer(function(layer) {
        if (isInArray(keyword, layer.feature.properties.keywordArray) == true) {
            map.removeLayer(jsonLyr);
            //map.removeLayer(selectedMarkers);
            pointsLayer.addLayer(layer);
            selectedMarkers.addLayer(pointsLayer);
            map.addLayer(selectedMarkers);
        }
        });
    }
    function searchPoints(elementID, property) {
        var jsonLyr2 = jsonLyr;
        var keyword = document.getElementById(elementID).value;
        map.removeLayer(jsonLyr);
        map.removeLayer(markers);
        selectedMarkers.clearLayers();
        pointsLayer.clearLayers();
        map.removeLayer(group);
        jsonLyr2.eachLayer(function(layer) {
            property2 = eval(property);
            if (property2.toString() === keyword.toString()) {
                map.removeLayer(jsonLyr);
                pointsLayer.addLayer(layer);
                selectedMarkers.addLayer(pointsLayer);
                map.addLayer(selectedMarkers);
            }
       });
    };
    </script>
<?php
} //end footerbottom hook
?>
