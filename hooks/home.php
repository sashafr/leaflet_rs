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
<!--
    <div id="query" class="leaflet-bar">
    <label>
      Name
      <select id="name">  -->
        <!-- make sure to encase string values in single quotes for valid sql -->
<!--        <option value='1=1'>Any</option>
        <option value="Name='Monument Lab Image'">Monument Lab Image</option>
        <option value="direction='doc_test'">doc_test</option>
        <option value="direction='ace_test'">ace_test</option>
      </select>
    </label>
    </div>
  -->
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

       // encodes the array into a string in JSON format (JSON_PRETTY_PRINT - uses whitespace in json-string, for human readable)
       //$geojson = json_encode($to_geojson, JSON_PRETTY_PRINT);

    ?>

    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.4/dist/esri-leaflet-geocoder.css">
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.4"></script>

    <script type="text/javascript" src="leaflet.js"></script>
    <script type="text/javascript" src="tabletop.min.js"></script>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript" src="app.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="//labs.easyblog.it/maps/leaflet-search/src/leaflet-search.css">
    <script type="text/javascript" src="//labs.easyblog.it/maps/leaflet-search/src/leaflet-search.js"></script>

    <script>

    var map = L.map('leaflet_rs_map').setView([39.9526, -75.1652], 13);
    L.esri.basemapLayer('Streets').addTo(map);

    function onEachFeature(feature, layer) {
      //alert("onEachFeature");
              // does this feature have a property named popupContent?
              if (feature.properties && feature.properties.name) {
                //  alert(feature.properties.name);
                  layer.bindPopup("<b>"+"NAME: "+"</b>"+ feature.properties.name + "<br />" + "<br />"+ "<a href='http://45.55.57.30/resourcespace/plugins/ref_urls/file.php?ref=1'><img src='http://45.55.57.30/resourcespace/plugins/ref_urls/file.php?ref=1&size=thm' width=60 height=50 ></a>", {maxWidth:60});
                  }
    };

    var markersLayer = new L.LayerGroup();

    var geojsonMarkerOptions = {
        radius: 4,
        fillColor: "#ff7800",
        color: "#000",
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
    };

    var jsonPts = <?php echo json_encode($to_geojson); ?>;
    var markerArray = [];

  /*  var jsonLyr = L.geoJson(jsonPts, {
        onEachFeature: onEachFeature
        , pointToLayer: function (feature, latlng) {
        var marker =  L.circleMarker(latlng, geojsonMarkerOptions);
        //return marker;
        markersLayer.addLayer(marker);
        } }).addTo(map);
*/

        L.geoJSON(jsonPts, {
                              onEachFeature: onEachFeature
                           , pointToLayer: function (feature, latlng) {
                              return L.circleMarker(latlng, geojsonMarkerOptions);
                           } }).addTo(map);



//    markersLayer.addTo(map);




/*
     var searchControl = L.esri.Geocoding.geosearch().addTo(map);

     var results = L.layerGroup().addTo(map);

     // listen for the results event and add every result to the map
     searchControl.on("results", function(data) {
        results.clearLayers();
        for (var i = data.results.length - 1; i >= 0; i--) {
            results.addLayer(L.marker(data.results[0].latlng));
        }
      });


    //creating the selector control
    //**********************************************************************

    //create Leaflet control for selector
    var selector = L.control({
      position: 'topright'
    });

    selector.onAdd = function(map) {
      //create div container
      var div = L.DomUtil.create('div', 'mySelector');
      //create select element within container (with id, so it can be populated later
      div.innerHTML = '<select id="marker_select"><option value="init">(select name)</option></select>';
      return div;
    };
    selector.addTo(map);

    alert("selector added");
    jsonLyr.eachLayer(function(layer) {
      //create option in selector element
      //with content set to city name
      //and value set to the layer's internal ID
      var optionElement = document.createElement("option");
      optionElement.innerHTML = layer.feature.geometry.type;
      optionElement.value = layer._leaflet_id;
      L.DomUtil.get("marker_select").appendChild(optionElement);
    });

    var marker_select = L.DomUtil.get("marker_select");

    //prevent clicks on the selector from propagating through to the map
    //(otherwise popups will close immediately after opening)
    L.DomEvent.addListener(marker_select, 'click', function(e) {
      L.DomEvent.stopPropagation(e);
    });

    L.DomEvent.addListener(marker_select, 'change', changeHandler);

    function changeHandler(e) {
      if (e.target.value == "init") {
        map.closePopup();
      } else {
        jsonLyr.getLayer(e.target.value).openPopup();
      }
    }

    function timePeriod(){
        markersLayer.clearLayers();
        var period = document.getElementById('periodSelect').value;
        //alert(period);
        jsonLyr.eachLayer(function(layer) {
        //
           if (layer.feature.geometry.type == period){
             alert(layer.feature.geometry.coordinates);
           };
        };
    )};

    map.addControl( new L.Control.Search({layer: jsonLyr}) );
    var searchControl = new L.Control.Search({
        layer: jsonLyr,
        propertyName: 'name',
        circleLocation:true

   });

   searchControl.on('search:locationfound', function(e) {

		//console.log('search:locationfound', );
		//map.removeLayer(this._markerSearch)
		e.layer.setStyle({fillColor: '#3f0', color: '#0f0'});
		if(e.layer._popup)
			e.layer.openPopup();
  	}).on('search:collapsed', function(e) {
  		featuresLayer.eachLayer(function(layer) {	//restore feature color
  			featuresLayer.resetStyle(layer);
  		})
  	});

    map.addControl(window.searchControl);
*/
    </script>
<!--
    <select id = "periodSelect">
		<option value="Point">Point</option>
		<option value="2">Early Iron Age</option>
		<option value="3">Late Iron Age</option>
		<option value="4">Classical</option>
		</select>

		<input type="submit" onclick="timePeriod();" value="Search by period"/>
-->
<?php
}
?>
