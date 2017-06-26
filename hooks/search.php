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
</script>
