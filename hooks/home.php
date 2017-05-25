<?php
function HookLeaflet_rsHomeAdditionalheaderjs() {
    ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin=""/>

    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>

    <?php
}
function HookLeaflet_rsHomeHomebeforepanels() {
    ?>

    <div id="leaflet_rs_map"> </div>

    <?php
}
function HookLeaflet_rsHomeFooterbottom() {
    ?>
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js" integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg==" crossorigin=""></script>
    <script src="https://unpkg.com/esri-leaflet@2.0.8"></script>
    <script>
        var map = L.map('leaflet_rs_map').setView([39.9526, -75.1652], 13);
        L.esri.basemapLayer('Streets').addTo(map);
        L.esri.featureLayer({
            url: 'https://services.arcgis.com/rOo16HdIMeOBI4Mb/arcgis/rest/services/Heritage_Trees_Portland/FeatureServer/0'
        }).addTo(map);
    </script>
<?php
}
?>
