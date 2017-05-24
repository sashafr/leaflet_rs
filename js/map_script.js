function createMap(data) {
    
    // ***MAP***

    // call L.map with id of element where we want map
    var map = L.map('leaflet_rs_map');

    // create base layer
    // var layer = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png');
    // load a different layer using Leaflet Providers - already included Leaflet Providers js to make this work
    // var layer = L.tileLayer.provider('CartoDB.DarkMatter');
    // to use a Mapbox layer you made
    var layer = L.tileLayer('https://api.mapbox.com/styles/v1/sashafr/ciph6s3hl000sbsnorwzacb4m/tiles/256/{z}/{x}/{y}?access_token=pk.eyJ1Ijoic2FzaGFmciIsImEiOiJjaXBoNnBoMGQwMHpydWduam8ycjE1b3FxIn0.j9xiOAVp-bKAXZusWgKKnA');

    // enable base layer on map
    map.addLayer(layer);

    // set view
    map.setView([0, 0], 3);
    
    // create the markers
    var markers = [];
    
    _.each(data.features, function(feature){
        var lat = feature.geometry.coordinates[1];
        var lon = feature.geometry.coordinates[0];
        
        var marker  = L.circleMarker([lat, lon], {
            className: 'toponym', 
            offset: Number(feature.properties.offset),
        });
        
        marker.bindPopup(feature.properties.toponym);
        
        markers.push(marker);
        map.addLayer(marker);
    });
    
    // ***SLIDER***
    
    var input =  $('#slider input');
    
    // using lodash to find last element
    var max = _.last(data.features).properties.offset;
    input.attr('max', max);  
    
    input.on('input', function() {
        var offset = Number(input.val());
        
        _.each(markers, function(marker) {
            if (marker.options.offset < offset) {
                map.addLayer(marker);
            } else {
                map.removeLayer(marker);
            }
        });
    });
    
    input.trigger('input');
    
    // *** MARKER CLUSTERS ***
    
    var clusters = L.markerClusterGroup();
    
    _.each(markers, function(marker) {
        clusters.addLayer(marker);
    });
    
    map.addLayer(clusters);
    
    // *** HEAT MAP ***
    var points = _.map(data.features, function(feature) {
        var lat = feature.geometry.coordinates[1];
        var lon = feature.geometry.coordinates[0];
        return [lat, lon, 1];
    });
    
    var heat = L.heatLayer(points, {
        minOpacity: 0.3
    });
    map.addLayer(heat);
}

// on page start
function createMapOnStart () {
    getJSON('../eight-days.geojson', function(data) {
        createMap(data);
    });
}
