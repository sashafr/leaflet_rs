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

    <div id="query" class="leaflet-bar">
    <label>
      Bus Direction
      <select id="direction">
        <!-- make sure to encase string values in single quotes for valid sql -->
        <option value='1=1'>Any</option>
        <option value="direction='North'">North</option>
        <option value="direction='South'">South</option>
        <option value="direction='East'">East</option>
        <option value="direction='West'">West</option>
      </select>
    </label>
    </div>

    <?php
}
function HookLeaflet_rsHomeFooterbottom() {
    ?>
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <script>
        var map = L.map('leaflet_rs_map').setView([39.9526, -75.1652], 5);
        L.esri.basemapLayer('Streets').addTo(map);
      //  L.esri.featureLayer({
        //    url: 'https://services.arcgis.com/rOo16HdIMeOBI4Mb/arcgis/rest/services/Heritage_Trees_Portland/FeatureServer/0'
        //}).addTo(map);




        function onEachFeature(feature, layer) {
                  // does this feature have a property named popupContent?
                if (feature.properties && feature.properties.popupContent && feature.properties.toponym) {
                   layer.bindPopup("<b>"+"NAME: "+"</b>" + feature.properties.toponym + "<br />" +
                   feature.properties.popupContent);
                   //layer.bindPopup(feature.properties.popupContent);
                  }

              }

         L.geoJSON(myPoints, {
                 onEachFeature: onEachFeature
              , pointToLayer: function (feature, latlng) {
                 return L.circleMarker(latlng, geojsonMarkerOptions);
              } }).addTo(map);



              var myPoints = [
                {
                  "properties": {
                    "offset": "1",
                    "latitude": "22.28552",
                    "longitude": "114.15769",
                    "toponym": "HONG KONG",
                    "popupContent": "<a href='www.google.com'><img src=’http://45.55.57.30/resourcespace/filestore/3_234e8b3d502e25a/3pre_5ea31c1ff3fefb6.jpg?v=2017-05-17+15%3A23%3A50’ width=100 height=80 /></a>"
                  },
                  "type": "Feature",
                  "geometry": {
                    "type": "Point",
                    "coordinates": [
                      114.15769,
                      22.28552
                    ]
                  }
                },
                {
                  "properties": {
                    "offset": "3",
                    "latitude": "37.77493",
                    "longitude": "-122.41942",
                    "toponym": "SAN FRANCISCO"
                  },
                  "type": "Feature",
                  "geometry": {
                    "type": "Point",
                    "coordinates": [
                      -122.41942,
                      37.77493
                    ]
                  }
                },
                {
                  "properties": {
                    "offset": "4",
                    "latitude": "38.482",
                    "longitude": "-90.74152",
                    "toponym": "PACIFIC"
                  },
                  "type": "Feature",
                  "geometry": {
                    "type": "Point",
                    "coordinates": [
                      -90.74152,
                      38.482
                    ]
                  }
                }
              ];
              //L.geoJSON(myPoints).addTo(map);

              var geojsonMarkerOptions = {
                  radius: 8,
                  fillColor: "#ff7800",
                  color: "#000",
                  weight: 1,
                  opacity: 1,
                  fillOpacity: 0.8
              };


              L.geoJSON(myPoints, {
                         onEachFeature: onEachFeature
                      , pointToLayer: function (feature, latlng) {
                         return L.circleMarker(latlng, geojsonMarkerOptions);
                      } }).addTo(map);


      //  L.geoJSON(myPoints, {
        //    pointToLayer: function (feature, latlng) {
          //     return L.circleMarker(latlng, geojsonMarkerOptions);
          //  }
        //}).addTo(map);












    </script>
<?php
}
?>
