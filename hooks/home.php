<?php
function HookLeaflet_rsHomeAdditionalheaderjs() {
?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin=""/>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
    <script src="../plugins/leaflet_rs/neighborhoods.js"></script>
<?php
} //end additionalheaderjs hook
//
//put map, selector, and keyword selector on home page
function HookLeaflet_rsHomeHomebeforepanels() {
?>
    <div class="w3-row w3-col" style="width:100%">
        <div id="map_container" class="w3-content w3-mobile">
        <div id="leaflet_rs_map"></div>
        </div>
    </div>

    <div id="selector_container" class="w3-row w3-mobile">

        <div id="selector"></div>
        <div id="zipcodeSelector"></div>

        <div id = "keywordBigDiv" class="w3-center">
            <br />
            <select id = "keywordSelect">
            <option value="arch">Arch</option>
            <option value="Community resource center">Community resource center</option>
            <option value="Conceptual">Conceptual</option>
            <option value="Digital project">Digital project</option>
            <option value="Garden">Garden</option>
            <option value="Image">Image</option>
            <option value="Interactive">Interactive</option>
            <option value="Memorial">Memorial</option>
            <option value="Mural">Mural</option>
            <option value="Park">Park</option>
            <option value="Song/sound">Song/sound</option>
            <option value="Statue/sculpture">Statue/scultpure</option>
            <option value="Tree">Tree</option>
            </select>
            <input class="w3-button w3-round w3-padding-small" type="submit" id = "searchButton" onclick="selectKeywords();" value="Search by keyword"/>
        </div>

        </div>

        <div id="showall_container" class="w3-row w3-mobile">
            <div class="w3-center">
            <br \>
            <input class="w3-button w3-large w3-round" type = "button" id = "showAll" value = "Show all" onClick = "showAll();"/>
            </div>
        </div>

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
    $query = 'SELECT r.ref, r.geo_lat, r.geo_long, r.field8 as title, r.field3 as zipcode, rd1.value as researchID, age, twitter, facebook, instagram
              FROM resource r
              INNER JOIN resource_data rd1 ON r.ref=rd1.resource
              LEFT JOIN (select resource, value as twitter from resource_data where resource_type_field = 84) as rd2 on rd1.resource = rd2.resource
              LEFT JOIN (select resource, value as facebook from resource_data where resource_type_field = 85) as rd3 on rd1.resource = rd3.resource
              LEFT JOIN (select resource, value as instagram from resource_data where resource_type_field = 86) as rd4 on rd1.resource = rd4.resource
              LEFT JOIN (select resource, value as age from resource_data where resource_type_field = 89) as rd5 on rd1.resource = rd5.resource
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
                    'zipcode' => $row["zipcode"],
                    'age' => $row["age"],
                    'twitter' => $row["twitter"],
                    'facebook' => $row["facebook"],
                    'instagram' => $row["instagram"],
                    'path' => get_resource_path($row["ref"],true, "",true),
                    'keywordArray' => ["image", "arch", "garden"]
                    )
            );
    }
?>

    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>

    <script>
    //create map
    var searching;
    var map = L.map('leaflet_rs_map');

    var tileLayer =  L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    //option to style neighborhood layer
    var neighborhoodsStyle = {
        //"color": "#ff7800",
        //"weight": 5,
        "color": "#4b4ba0",
        "opacity": 0.8,
        "fillOpacity": 0.45,
        "weight": 1
    };

    var neighborhoodsLayer = L.geoJson(neighborhoods, {style: neighborhoodsStyle}).addTo(map);
    map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});

    var pointsLayer = new L.FeatureGroup();

    //HOW TO SELECT BY KEYWORD IF THE KEYWORDS ARE IN AN ARRAY THAT IS A PROPERTY OF THE FEATURE
    //THE VALUE IN THE VALUE = PART OF THE SELECTOR (STARTING AT LINE 20) MUST EXACTLY MATCH THE KEYWORD AS IT APPEARS
    //IN THE DATABASE (E.G. LOWERCASE VS UPPERCASE, WORD OR NUMBER)
    function selectKeywords() {
        var keyword = document.getElementById('keywordSelect').value;
        var jsonLyr2 = jsonLyr;
        map.removeLayer(jsonLyr);
        pointsLayer.clearLayers();
        map.removeLayer(group);
        jsonLyr2.eachLayer(function(layer) {
        // check if the keywords are in a property array called feature.properties.keywordArray
        if (isInArray(keyword, layer.feature.properties.keywordArray) == true) {
            map.removeLayer(jsonLyr);
            pointsLayer.addLayer(layer);
            map.addLayer(pointsLayer);
        }
    });
    }

    //HOW TO SELECT BY KEYWORD IF EACH KEYWORD IS ITS OWN VARIABLE THAT IS EITHER NULL OR THE
    //WORD ITSELF OR A NUMBER

    /* function selectKeywords() {
    var keyword = document.getElementById('keyword_select').value;
    var jsonLyr2 = jsonLyr;
    map.removeLayer(jsonLyr);
    pointsLayer.clearLayers();
    //   neighborhoodsLayer.clearLayers();
    map.removeLayer(group);
    jsonLyr2.eachLayer(function(layer) {

    if (feature.properties.arch == keyword || feature.properties.crc == keyword || etc.) {
      //or you can do
    //  if (keyword = "# that corresponds to arch")
      //  { if (layer.feature.properties.arch) {
      //map.removeLayer(jsonLyr);
      //pointsLayer.addLayer(layer);
      //map.addLayer(pointsLayer);
        //}
      //}
      map.removeLayer(jsonLyr);
      pointsLayer.addLayer(layer);
      map.addLayer(pointsLayer);
    }
    });
    }
    */

    function onEachFeature(feature, layer) {
        var html = "<a href='http://45.55.57.30/resourcespace/plugins/leaflet_rs/pages/direct_view.php?ID=" + feature.properties.researchID + "'><img src='http://45.55.57.30/resourcespace/filestore/" + feature.properties.path.substring(44) + "'" + "&size=thm' width=100 height=80 ></a>";
        if (feature.properties && feature.properties.name && feature.properties.ref) {
            var zipcodePopup;
            var agePopup;
            var twitterPopup;
            var facebookPopup;
            var instagramPopup;
            //if Twitter, Facebook and/or Instagram accounts are provided, display links to them; otherwise display nothing
            if (feature.properties.age){
              var agePopup =  "<b>"+"AGE: "+"</b>" + feature.properties.age + "<br/>";
            }
            else {
              var agePopup = "";
            }

            if (feature.properties.zipcode){
              var zipcodePopup =  "<b>"+"ZIPCODE: "+"</b>" + feature.properties.zipcode + "<br/>";
            }

            else {
              var zipcodePopup = "";
            }

            if (feature.properties.twitter){
                var twitterPopup = "<div style = 'padding:0px; margin:0px 0px 12px 0px; height: 10px; font-family:Helvetica Neue Pro; font-weight:bold; color:white';>"
                +  "<img src = 'http://45.55.57.30/resourcespace/plugins/leaflet_rs/Twitter_Social_Icon_Circle_Color.png' style='padding: 4px 4px 4px 0px; vertical-align: middle; width:16px; height:16px';>"
                + "<a href='https://twitter.com/" +
                 feature.properties.twitter + "'style='font-size: 10px; color:white';>" + "@" + feature.properties.twitter + "</a></div>";
            }

            else {
                var twitterPopup = "";
            }

            if (feature.properties.facebook){
                var facebookPopup = "<div style = 'padding:0px; margin:0px 0px 0px 0px; height: 4px; font-family:Helvetica Neue Pro; font-weight:bold; color:white';>"
                +  "<img src = 'http://45.55.57.30/resourcespace/plugins/leaflet_rs/FB-f-Logo__blue_29.png' style='padding: 0px 4px 0px 0px; vertical-align: middle; width:16px; height:16px';>"
                + "<a href='https://facebook.com/" +
                feature.properties.facebook + "'style='font-size: 10px; color:white';>" + feature.properties.facebook + "</a></div>" + "<br/>";
            }

            else {
                var facebookPopup = "";
            }

            if (feature.properties.instagram){
                var instagramPopup = "<div style = 'padding:0px; margin:0px 0px 0px 0px; height: 8px; font-family:Helvetica Neue Pro; font-weight:bold; color:white';>"
                +  "<img src = 'http://45.55.57.30/resourcespace/plugins/leaflet_rs/glyph-logo_May2016.png' style='padding: 0px 4px 0px 0px; vertical-align: middle; width:16px; height:16px';>"
                + "<a href='https://www.instagram.com/" +
                 feature.properties.instagram + "/" + "'style='font-size: 10px; color:white';>" + feature.properties.instagram + "</a></div>";
            }

            else {
                var instagramPopup = "";
            }

            layer.bindPopup("<span style = 'font-family:Arial';>" + "<b>"+"TITLE: "+"</b>"+ feature.properties.name + "</span>" + "<br />" + agePopup  + zipcodePopup +"<br />"+ html + "<br />"  + twitterPopup + facebookPopup + instagramPopup + "<br />" );

            //show just the monument name on mouseover
            layer.on('mouseover', function(e) {
                //open popup;
                var popup = L.popup()
                .setLatLng(e.latlng)
                .setContent("<div style = 'font-family:Arial; font-weight:bold';>" + feature.properties.name + "</div>")
                .openOn(map);
                });

        } //end outer if statement
    }; //end onEachFeature function

    //format map markers
    var geojsonMarkerOptions = {
        radius: 4,
        fillColor: "#fbaf3f",
        /*color: "#000",*/
        weight: 1,
        /*opacity: 1,*/
        fillOpacity: 1
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

    //Remove the neighborhoods layer when the user zooms in past a certain point
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
        map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
    };

    var group = new L.LayerGroup(jsonLyr, neighborhoodsLayer);
    map.addLayer(group);

    //select points by attribute
    //var neighborhoodsLayer;
    function searchPoints(elementID, property){
        var searching = true;
        var jsonLyr2 = jsonLyr;
        var keyword = document.getElementById(elementID).value;
        map.removeLayer(jsonLyr);
        pointsLayer.clearLayers();
        map.removeLayer(group);
        jsonLyr2.eachLayer(function(layer) {
            property2 = eval(property);
            if (property2 == keyword) {
                map.removeLayer(jsonLyr);
                pointsLayer.addLayer(layer);
                map.addLayer(pointsLayer);
            }
       });
    };

    //Create selector that will populate dynamically
    var selector = L.control({position: 'bottomright'});
    selector.onAdd = function(map) {
        //create div container
        var div = L.DomUtil.create('div', 'mySelector');
        //create select element within container (with id, so it can be populated later
        div.innerHTML = '<select id="name_select"><option value="init">(select by title)</option></select>';
        return div;
    };
    selector.addTo(map);

    /*
    //Put the selector in a new div that is outside the map and beside the other search otpions
    var newSelectorDiv = document.getElementById('selector');
    var nameSelect = document.getElementById('name_select');
    newSelectorDiv.appendChild(nameSelect);
    */

    //Add each name to a list
    var myList = [];
    jsonLyr.eachLayer(function(layer) {
        myList.push(layer.feature.properties.name);
    });

    //sort the list
    //var lowerCase = myList.toLowerCase();
    //var sortedList = myList.map(function(value){
    //return value.toLowerCase();
    //}).sort();
    sortedList = myList.sort();
    //add the items in the sorted list into the selector
    for (var i = 0; i < sortedList.length; i++) {
        var optionElement = document.createElement("option");
        optionElement.innerHTML = sortedList[i];
        L.DomUtil.get("name_select").appendChild(optionElement);
    }

    var name_select = L.DomUtil.get("name_select");

    //prevent clicks on the selector from propagating through to the map
    //without this popups close immediately after opening
    L.DomEvent.addListener(name_select, 'click', function(e) {
        L.DomEvent.stopPropagation(e);
    });

    //Listen for user to select item from the dropdown
    L.DomEvent.addListener(name_select, 'change', changeHandler);

    //When a name is selected, open that monument's popup
    function changeHandler(e) {
        searchPoints("name_select", 'layer.feature.properties.name');
    }

    //Create selector by zip code
    var zipcodeSelector = L.control({position: 'bottomright'});
    zipcodeSelector.onAdd = function(map) {
        //create div container
        var div2 = L.DomUtil.create('div', 'mySelector2');
        //create select element within container (with id, so it can be populated later
        div2.innerHTML = '<select id="zip_select"><option value="init">(select by zipcode of creator)</option></select>';
        return div2;
    };
    zipcodeSelector.addTo(map);

    /*
    //Put the selector in a new div that is outside the map and beside the other search otpions
    var zipSelectorDiv = document.getElementById('zipcodeSelector');
    var zipSelect = document.getElementById('zip_select');
    zipSelectorDiv.appendChild(zipSelect);
    */

    //Add each name to a list
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

    //sort the list
    var sortedZipList = myZipList.sort();
    //add the items in the sorted list into the selector
    for (var i = 0; i < sortedZipList.length; i++) {
        var optionElement = document.createElement("option");
        optionElement.innerHTML = sortedZipList[i];
        L.DomUtil.get("zip_select").appendChild(optionElement);
    }

    var zip_select = L.DomUtil.get("zip_select");

    //prevent clicks on the selector from propagating through to the map
    //(otherwise popups will close immediately after opening)
    L.DomEvent.addListener(zip_select, 'click', function(e) {
        L.DomEvent.stopPropagation(e);
    });

    //Listen for user to select item from the dropdown
    L.DomEvent.addListener(zip_select, 'change', changeHandler2);

    //When a name is selected, remove all points but that one
    function changeHandler2() {
      searchPoints("zip_select", 'layer.feature.properties.zipcode');
    }

    </script>

<?php
} //end footerbottom hook
?>
