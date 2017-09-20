<?php
function HookLeaflet_rsHomeAdditionalheaderjs() {
?>
    <!--TYPEKIT STYLESHEET -->
    <script src="https://use.typekit.net/sas2bse.js"></script>
    <script>try{Typekit.load({ async: true });}catch(e){}</script>
    <!--W3 STYLEHEET -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <!--NEIGHBORHOODS LAYER -->
    <script src="../plugins/leaflet_rs/js/neighborhoods.js"></script>
    <!--LEAFLET LIBRARIES -->
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin=""/>
    <!--ESRI LEAFLET LIBRARIES-->
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>
    <!-- MARKER CLUSTER LIBRARIES -->
    <link rel="stylesheet" href="../plugins/leaflet_rs/css/MarkerCluster.css" />
    <link rel="stylesheet" href="../plugins/leaflet_rs/css/MarkerCluster.Default.css" />
    <script src="../plugins/leaflet_rs/js/leaflet.markercluster-src.js"></script>
    <script src="../plugins/leaflet_rs/js/leaflet.markercluster.js"></script>
<?php
} //end additionalheaderjs hook

function HookLeaflet_rsHomeHomebeforepanels() {
?>
<div id ="home_leaflet_rs_container">
<div id="map_container" class="w3-content w3-mobile">
    <div id="leaflet_rs_map"></div>
</div>

<!-- This div is where selectors and buttons go if not on the map -->
<div class="w3-row" style="width = 100%">
    <div id="selector_container" class="w3-center w3-mobile">
        <div id="selectorDiv"></div>
    </div>
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

    //get resources that have a location
    $query = 'SELECT r.ref, r.geo_lat, r.geo_long, r.creation_date as creationDate, r.field8 as title, r.field3 as zipcode, rd1.value as researchID, age, twitter, facebook, instagram,
                 (SELECT GROUP_CONCAT(n.name SEPARATOR \', \')
                  FROM resource_node rn
                  LEFT JOIN node n ON rn.node = n.ref
                  WHERE rn.resource=r.ref and n.resource_type_field = 92
                  GROUP BY rn.resource) as topic,
                 (SELECT n.name
                  FROM resource_node rn
                  LEFT JOIN node n ON rn.node = n.ref
                  WHERE rn.resource=r.ref and n.resource_type_field = 98) as lab_location,
                  (SELECT GROUP_CONCAT(n.name SEPARATOR \', \')
                   FROM resource_node rn
                   LEFT JOIN node n ON rn.node = n.ref
                   WHERE rn.resource=r.ref and n.resource_type_field = 96
                   GROUP BY rn.resource) as proposalType
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
                'topicArray' => explode(', ',  $row["topic"]),
                'typeArray' => explode(', ',  $row["proposalType"]),
                'uppercaseName' => ucfirst(str_replace(['"', '\''], "", $row["title"])),
                'path' => get_resource_path($row["ref"],true, "",true),
                'creationDate' => $row["creationDate"],
                'labLocation' => $row["lab_location"]
                )
        );
    }
    ?>
    <!--GEOCODER LIBRARIES-->
    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>

    <script>
    //--------------------------------MAP---------------------------------------
    var map = L.map('leaflet_rs_map');

    //------------------------------LAYERS--------------------------------------
    //streets layer
    var tileLayer =  L.tileLayer('//{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    //neighborhoods layer
    var neighborhoodsStyle = {"color": "#ff6a39", "opacity": 0.8, "fillOpacity": 0.45, "weight": 1};
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
            //   console.log("layer already added");
            }
            else {
                neighborhoodsLayer.addTo(map);
                neighborhoodsLayer.bringToBack();
            }
        }
    });

    //creates a layer group of the neighborhoods layer and monument points
    //used by the selectors to remove and redraw points
    var group = new L.LayerGroup(jsonLyr, neighborhoodsLayer);
    map.addLayer(group);

    //----------------------------MONUMENTS-------------------------------------
    //get monument points from geojson array
    var jsonPts = <?php echo json_encode($to_geojson); ?>;
    console.log(jsonPts);    
    //format markers
    var geojsonMarkerOptions = {
        radius: 4,
        fillColor: "#77C5D5",
        color: "#737373",
        weight: 1,
        fillOpacity: 1
    };

    var geojsonMarkerOptions2 = {
        radius: 3,
        fillColor: "#0B5818",
        color: "#737373",
        weight: 1,
        fillOpacity: 1
    };

    var currentdate = new Date();
    var twoHours = 2 * 60 * 60 * 1000;
    var twoHoursAgo = new Date(currentdate.getTime() - twoHours)
    var twentyfourHours = 24 * 60 * 60 * 1000;
    var twentyfourHoursAgo = new Date(currentdate.getTime() - twentyfourHours)
    var testDate = new Date("September 16, 2017 9:13:00");

    //Create clustered and unclustered markers
    var markers = new L.markerClusterGroup({showCoverageOnHover: false});
    var selectedMarkers = new L.markerClusterGroup();
    var unclusteredMarkers = new L.featureGroup();
    var selectedUnclusteredMarkers = new L.FeatureGroup();

    //adds momuments to map
    var jsonLyr = L.geoJson(jsonPts, {
        onEachFeature: onEachFeature
        , pointToLayer: function (feature, latlng) {
          var dateCreated = new Date(feature.properties.creationDate);
          //style markers by date
          if (dateCreated > twentyfourHoursAgo) {
            var marker = L.circleMarker(latlng, geojsonMarkerOptions);
          }
          if (dateCreated < twentyfourHoursAgo) {
            var marker = L.circleMarker(latlng, geojsonMarkerOptions2);
          }
        return marker;
        }
    });
    markers.addLayer(jsonLyr);
	markers.addTo(map);    
    unclusteredMarkers.addLayer(jsonLyr);

    //true if marker clusters is selected from the toggle (default)
    var clustersOn = true;


    //------------------------------POP-UPS-------------------------------------
    function onEachFeature(feature, layer) {
        var html = "<a href='../plugins/leaflet_rs/pages/direct_view.php?ID=" + feature.properties.researchID +
            "'><img src='../filestore/" + feature.properties.path.substring(44) + "'" + "&size=thm' width=100 height=80 ></a>";
        var agePopup = "", zipcodePopup = "", socialMediaPopup = "";
        if (feature.properties && feature.properties.name && feature.properties.ref) {
            if (feature.properties.age){
                var agePopup =  "<b>"+"AGE: "+"</b>" + feature.properties.age + "<br/>";
            }
            if (feature.properties.topicArray) {
                var topic = feature.properties.topicArray + "<br/";
            }
            if (feature.properties.typeArray) {
                var type = feature.properties.typeArray + "<br/";
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
                 type + topic + agePopup + zipcodePopup + "</span>" + "<br />" + html + "<br />"  + socialMediaPopup + "<br />", {autoClose: false});

            //mouseover popups with momument title
            layer.on('mouseover', function(e) {
                //open popup;
                var popup = L.popup()
                .setLatLng(e.latlng)
                .setContent("<div style = 'font-family:sans-serif; letter-spacing:1px; font-weight:bold';>" + feature.properties.name + "</div>")
                .openOn(map);
            });
        }
    };

//-------------------------------------LEGEND-----------------------------------

    // var legend = L.control({position: 'topleft'});
    //
    // legend.onAdd = function (map) {
    //   var legendDiv = L.DomUtil.create('div', 'legend');
    //   legendDiv.innerHTML += "<span id = 'legendTitle'>Legend</span><br/>";
    //   legendDiv.innerHTML += "<div style = 'margin:8px'><div id = 'legendCircle1'></div>     Recently added</div>";
    //   legendDiv.innerHTML += "<div style = 'margin:8px'><div id = 'legendCircle2'></div>     Earlier submissions</div>";
    //   return legendDiv;
    //   }
    // legend.addTo(map);

    //----------------------------GEOCODER--------------------------------------
    //restrict geocoder searchBounds to the greater Philadelphia area
    var corner1 = L.latLng(40.11194, -75.30556);
    var corner2 = L.latLng(39.84556, -74.95556);
    var markerOptions = {
      color: "#737373"
    };
    bounds = L.latLngBounds(corner1, corner2);
    var geoOptions = {title: "Search Location", position: 'bottomright', searchBounds: bounds, expanded:true, collapseAfterResult: false};
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

    //-------------------------SHOW ALL BUTTON----------------------------------
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
        if (clustersOn == true) {
          map.removeLayer(selectedMarkers);
          markers.addTo(map);
        }
        if (clustersOn == false) {
          map.removeLayer(selectedUnclusteredMarkers);
          unclusteredMarkers.addTo(map);
        }

        map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
        //reset dropdown to default text
        var dropdowns = document.querySelectorAll(".selector")
        for (var i = 0; i < dropdowns.length; i++) {
          dropdowns[i].selectedIndex = 0;
        }
    };

//----------------------------MARKER CLUSTER TOGGLE--------------------------
  //Create and populate the div to toggle marker clusters on and off
    var toggleDiv = L.DomUtil.create('div', 'toggleDiv');
    var toggle = L.control();
    toggle.onAdd = function(map) {
      toggleDiv.innerHTML = "'<div id = 'toggle'>" +
                "<div class='layerText'>Show clusters</div>" +
                "<label class='switch' style = 'vertical-align:middle'>" +
                  "<input type='checkbox'>" +
                  "<span id = 'toggleLayers' class='slider'>" +
                  "</span>" +
                "</label>" +
                "<div class='layerText' style = 'margin-left:4px' >Show points</div>"+
              "</div>" +
              "</div>";
      return toggleDiv;
      alert("returned");

    }
    toggle.addTo(map);

    //when the toggle is clicked, toggle the clusters
    var toggleLayers = document.getElementById("toggleLayers");
    L.DomEvent.addListener(toggleLayers, 'click', function(e) {
      if (clustersOn == true) {
        map.removeLayer(markers);
        unclusteredMarkers.addTo(map);
        clustersOn = false;
        //if points are selected and the cluster toggle is changed, redo the selection
        //with the new clustering option
        if (document.querySelector("#location_select").selectedIndex != 0) {
          map.removeLayer(selectedMarkers);
          changeHandler();
        }
        if (document.querySelector("#type_select").selectedIndex != 0) {
          map.removeLayer(selectedMarkers);
          changeHandler2();
        }
        if (document.querySelector("#key_select").selectedIndex != 0) {
          map.removeLayer(selectedMarkers);
          changeHandler3();
        }
        return;
      }

      if (clustersOn == false) {
        map.removeLayer(unclusteredMarkers);
        markers.addTo(map);
        clustersOn = true;
        //if points are selected and the cluster toggle is changed, redo the selection
        //with the new clustering option
        if (document.querySelector("#location_select").selectedIndex != 0) {
          map.removeLayer(selectedUnclusteredMarkers)
          changeHandler();
        }
        if (document.querySelector("#type_select").selectedIndex != 0) {
          map.removeLayer(selectedUnclusteredMarkers)
          changeHandler2();
        }
        if (document.querySelector("#key_select").selectedIndex != 0) {
          map.removeLayer(selectedUnclusteredMarkers)
          changeHandler3();
        }
        return;
      }
    });

    //-------------------FUNCTION FOR POPULATING SELECTORS----------------------
    function isInArray(value, array) {
        return array.indexOf(value) > -1;
    }

    //--------------------------TITLE SELECTOR----------------------------------
    //create selector that will populate dynamically
/*  var titleSelector = L.control({position: 'bottomright'});
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
*/
    //-------------------------ZIPCODE SELECTOR---------------------------------
    //create selector by zip code
/*  var zipcodeSelector = L.control({position: 'bottomright'});
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
*/

//---------------------------LAB LOCATION SELECTOR---------------------------
//     var locationSelector = L.control({position: 'bottomright'});
//     //put selector on map
//     locationSelector.onAdd = function(map) {
//         var locationDiv = L.DomUtil.create('div', 'locationSelectorDiv');
//         locationDiv.innerHTML = '<select id="location_select" class="selector"><option value="init">Select by Lab Location</option></select>';
//         return locationDiv;
//     };
//
//     locationSelector.addTo(map);
//     //Put the selector in a new div that is outside the map and beside the other search otpions
//     //Comment this out to put the selector back on the map
//     var selectorDiv = document.getElementById('selectorDiv');
//     var locationSelect = document.getElementById('location_select');
//     selectorDiv.appendChild(locationSelect);
//
//     var locationList = [];
//     jsonLyr.eachLayer(function(layer) {
//         if (layer.feature.properties.labLocation) {
//           //if the name is already in the list, don't add it
//           if (isInArray(layer.feature.properties.labLocation, locationList) == true) {
//               return;
//           }
//           else {
//               locationList.push(layer.feature.properties.labLocation);
//           }
//       }
//     });
//
//     var sortedLocationList = locationList.sort();
//     //add the items in the sorted list into the selector
//     for (var i = 0; i < sortedLocationList.length; i++) {
//         var optionElement = document.createElement("option");
//         optionElement.innerHTML = sortedLocationList[i];
//         locationSelect.appendChild(optionElement);
//     }
//
//     var location_select = L.DomUtil.get("location_select");
//     //prevent clicks on the selector from propagating through to the map
//     L.DomEvent.addListener(location_select, 'click', function(e) {
//         L.DomEvent.stopPropagation(e);
//     });
//     //listen for user to select item from the dropdown
//     L.DomEvent.addListener(location_select, 'change', changeHandler);
//
//     function changeHandler() {
//       searchPoints('location_select', 'layer.feature.properties.labLocation');
//       document.querySelector("#key_select").selectedIndex = 0;
//       document.querySelector("#type_select").selectedIndex = 0;
//     }
//
// //------------------------------TYPE SELECTOR--------------------------------
//
//     var typeSelector = L.control({position: 'bottomright'});
//     //put selector on map
//     typeSelector.onAdd = function(map) {
//         var typeDiv = L.DomUtil.create('div', 'mySelector4');
//         typeDiv.innerHTML = '<select id="type_select" class="selector"><option value="init">Select by Type</option></select>';
//         return typeDiv;
//     };
//
//     typeSelector.addTo(map);
//     //Put the selector in a new div that is outside the map and beside the other search otpions
//     //Comment this out to put the selector back on the map
//
//     var typeSelect = document.getElementById('type_select');
//     selectorDiv.appendChild(typeSelect);
//
//     //populate dropdown
//     var typeList = [];
//     jsonLyr.eachLayer(function(layer) {
//         for (var i = 0; i < layer.feature.properties.typeArray.length; i++){
//             var word = layer.feature.properties.typeArray[i];
//             if (word && isInArray(word, typeList) == false && isInArray(word, ["Mapped", "Unprocessed", "Transcribed"]) ==false ) {
//                 typeList.push(word);
//             }
//         }
//     });
//     sortedTypeList = typeList.sort();
//     for (var i = 0; i < sortedTypeList.length; i++) {
//         var optionElement = document.createElement("option");
//         optionElement.innerHTML = sortedTypeList[i];
//         L.DomUtil.get("type_select").appendChild(optionElement);
//     }
//
//     var type_select = L.DomUtil.get("type_select");
//     //call function on change
//     L.DomEvent.addListener(type_select, 'change', changeHandler2);
//     function changeHandler2() {
//         selectKeywords('type_select', 'layer.feature.properties.typeArray');
//         document.querySelector("#location_select").selectedIndex = 0;
//         document.querySelector("#key_select").selectedIndex = 0;
//     }
//
//     //-------------------------TOPIC SELECTOR---------------------------------
//     var keywordSelector = L.control({position: 'bottomright'});
//     //put keyword selector on map
//     keywordSelector.onAdd = function(map) {
//         var div = L.DomUtil.create('div', 'mySelector3');
//         div.innerHTML = '<select id="key_select" class="selector"><option value="init">Select by Topic</option></select>';
//         return div;
//     };
//
//     keywordSelector.addTo(map);
//     //Put the selector in a new div that is outside the map and beside the other search otpions
//     //Comment this out to put the selector back on the map
//     var keySelect = document.getElementById('key_select');
//     selectorDiv.appendChild(keySelect);
//
//     //populate dropdown
//     var keywordList = [];
//     jsonLyr.eachLayer(function(layer) {
//         for (var i = 0; i < layer.feature.properties.topicArray.length; i++){
//             var word = layer.feature.properties.topicArray[i];
//             if (word && isInArray(word, keywordList) == false && isInArray(word, ["Mapped", "Unprocessed", "Transcribed"]) ==false ) {
//                 keywordList.push(word);
//             }
//         }
//     });
//     sortedKeywordList = keywordList.sort();
//     for (var i = 0; i < sortedKeywordList.length; i++) {
//         var optionElement = document.createElement("option");
//         optionElement.innerHTML = sortedKeywordList[i];
//         L.DomUtil.get("key_select").appendChild(optionElement);
//     }
//     var key_select = L.DomUtil.get("key_select");
//     //call function on click
//     L.DomEvent.addListener(key_select, 'change', changeHandler3);
//     function changeHandler3() {
//         selectKeywords('key_select','layer.feature.properties.topicArray');
//         document.querySelector("#location_select").selectedIndex = 0;
//         document.querySelector("#type_select").selectedIndex = 0;
//     }

    //----------FUNCTIONS EXECUTED ON SELECTOR DROPDOWN CLICKS------------------
    var pointsLayer = new L.FeatureGroup(); //selected points

    function selectKeywords(elementID, property) {
        var keyword = document.getElementById(elementID).value;
        var jsonLyr2 = jsonLyr;
        map.removeLayer(jsonLyr);
        if (clustersOn == true) {
          map.removeLayer(markers);
          selectedMarkers.clearLayers();
        }
        if (clustersOn == false) {
          map.removeLayer(unclusteredMarkers);
          selectedUnclusteredMarkers.clearLayers();
        }
        pointsLayer.clearLayers();
        map.removeLayer(group);
        jsonLyr2.eachLayer(function(layer) {
          var property2 = eval(property);
        if (isInArray(keyword, property2) == true) {
            map.removeLayer(jsonLyr);
            pointsLayer.addLayer(layer);
            if (clustersOn == true) {
              selectedMarkers.addLayer(pointsLayer);
              map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
              map.addLayer(selectedMarkers);
            }
            if (clustersOn == false) {
              selectedUnclusteredMarkers.addLayer(pointsLayer);
              map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
              map.addLayer(selectedUnclusteredMarkers);
            }
          }
        });
    }

    function searchPoints(elementID, property) {
        var jsonLyr2 = jsonLyr;
        var criteria = document.getElementById(elementID).value;
        map.removeLayer(jsonLyr);
        if (clustersOn == true) {
          map.removeLayer(markers);
          selectedMarkers.clearLayers();
        }
        if (clustersOn == false) {
          map.removeLayer(unclusteredMarkers);
          selectedUnclusteredMarkers.clearLayers();
        }
        pointsLayer.clearLayers();
        map.removeLayer(group);
        jsonLyr2.eachLayer(function(layer) {
            property2 = eval(property);
            if (property2 === criteria) {
                map.removeLayer(jsonLyr);
                pointsLayer.addLayer(layer);
                if (clustersOn == true) {
                  selectedMarkers.addLayer(pointsLayer);
                  map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
                  map.addLayer(selectedMarkers);
                }
                if (clustersOn == false) {
                  selectedUnclusteredMarkers.addLayer(pointsLayer);
                  map.fitBounds(neighborhoodsLayer.getBounds(), {padding: [10, 10]});
                  map.addLayer(selectedUnclusteredMarkers);
                }

            }
       });
    };
    </script>
<?php
} //end footerbottom hook
?>
