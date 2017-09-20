<?php global $baseurl; ?>
<script>
//Dynamically load stylesheet
function loadjscssfile(filename, filetype) {
    if (filetype=="css") {
        var fileref=document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref!="undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}
//get value $user, which will be a string if the user is logged in and
//null if the user is not logged in
var loggedIn = "<?php $user=getvalescaped("user",""); echo $user;  ?>";

//if the user is not logged in, load the stylesheet that hides the resource tools
if(!loggedIn) {
    loadjscssfile("../plugins/leaflet_rs/css/extra.css", "css");
}


// re-directing search results to direct_view page when search result's title is clicked
document.addEventListener('click', function(e) {
    var target = e.target.href;
    if(!loggedIn) {
        if (typeof target !== 'undefined') {
            if (target.includes("pages/view.php")){
                var parametersArray = target.split('&');
                function parseURLParameter(parameter) {
                for (var i = 0; i < parametersArray.length; i++) {
                    var currentParameter = parametersArray[i].split('=');
                    if (currentParameter[0] == parameter) {
                        return currentParameter[1];
                    }
                }
            }
            var ref = parseURLParameter("ref");
            window.location.href = "<?php echo $baseurl?>/plugins/leaflet_rs/pages/direct_view.php?ref=" + ref;
        }
    }
}
});

</script>
