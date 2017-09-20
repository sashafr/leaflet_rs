<?php
global $baseurl;

function HookLeaflet_rsAllHeadertop() {
    ?>
    <div class="return_link">
        <a href="http://monumentlab.muralarts.org/">>> Return to Monument Lab</a>
    </div>
    <?php
}

function HookLeaflet_rsAllFootertop() {
    include "/var/www/resourcespace/include/config.php";
    ?>
    <div id="footerdiv">
        <div class="footertext"><p>Research system created with support from   <a href="https://github.com/sashafr/leaflet_rs"><img src="<?php echo $baseurl ?>/plugins/leaflet_rs/assets/ds_team_stamp_tiny.png" /></a>&nbsp;&nbsp;&&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://pricelab.sas.upenn.edu/"><img src="<?php echo $baseurl ?>/plugins/leaflet_rs/assets/price_lab_logo_tiny.png" /></a></div>
    </div>
    <?php
}

function HookLeafletrs_AllReplaceheadernav1anon() {
    ?>
    <div class="sadandempty">
    test
    </div>
    <?php
}

function HookLeaflet_rsAllAddtologintoolbarmiddle() {
    include "/var/www/resourcespace/include/config.php";
    ?>
    <li id = "list">
          <button id = "submissionsButton" onclick="showDropdown()" class="dropbtn">Submissions</button>
    </li>
    <div id="submissions_dropdown" class="dropdown-content" href = "#"></div>

    <script>
    var baseUrl = "<?php include '/var/www/resourcespace/include/config.php'; echo $baseurl; ?>";

    //create dropdown menu

    var dropdownElement = document.getElementById("submissions_dropdown");
    //dropdownElement.innerHTML = '<a class="dropdown_option" href="' + baseUrl + '/pages/search.php?search=field94%3AUnprocessed&resetrestypes=true">Unprocessed<br/></a>';
    dropdownElement.innerHTML += '<a class="dropdown_option" href="' + baseUrl + '/pages/search.php?search=field94%3AMapped&resetrestypes=true">Mapped<br/></a>';
    dropdownElement.innerHTML += '<a class="dropdown_option" href="' + baseUrl + '/pages/search.php?search=field94%3ATranscribed&resetrestypes=true">Transcribed</a>';

    //hide dropdown initially
    document.getElementById('submissions_dropdown').style.display = "none";

    function showDropdown() {
        //if the button is clicked and the dropdown is hidden, show it
        if (document.getElementById('submissions_dropdown').style.display == "none"){
            document.getElementById('submissions_dropdown').style.display = "block";
            return;
        }
        //if the button is clicked and the dropdown is visible, hide it
        if (document.getElementById('submissions_dropdown').style.display == "block"){
            document.getElementById('submissions_dropdown').style.display = "none";
            return;
        }
    }

    //close dropdown on click outside of dropdown
    window.onclick = function(event) {
        if (event.target.id!= 'submissions_dropdown' && event.target.id != 'submissionsButton') {
            document.getElementById('submissions_dropdown').style.display = "none";
        }
    }
    </script>
<?php
} //end hook addlogintoolbarmiddle
?>

<script>
//Reload the home page when the user clinks on anything that links to the homepage
document.addEventListener('click', function(e) {
    var target = e.target.href;
    if (typeof target !== 'undefined'){
        if (target.includes("pages/home.php")){
            window.location.href = "<?php echo $baseurl?>/pages/home.php";
        }
    }
});

//link checkboxes
var linkedCheckboxes = { "nodes_349": ["nodes_340", "nodes_346"], "nodes_347": ["nodes_351"]};
function makeClickCallback(key) {
  function callback(e){
    linkedCheckboxes[key].map(function(x){
      document.getElementById(x).checked = e.target.checked;
    });
  }
  return callback;
}

if (document.getElementById("question_16")){
  for (var key in linkedCheckboxes) {
    document.getElementById(key).addEventListener('click', makeClickCallback(key));
  }
}

//get value $user, which will be a string if the user is logged in and
//null if the user is not logged in
var loggedIn = "<?php $user=getvalescaped("user",""); echo $user;  ?>";

//if the user is not logged in, load the stylesheet that hides the resource tools
if(!loggedIn) {
    // re-directing proposals to direct_view page when a proposal's title is clicked
    document.addEventListener('click', function(e) {
        var target = e.target.href;
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
    });
}


</script>
