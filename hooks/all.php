<?php
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
    dropdownElement.innerHTML = '<a class="dropdown_option" href="' + baseUrl + '/pages/search.php?search=field94%3AUnprocessed&resetrestypes=true">Unprocessed<br/></a>';
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
var target;
document.addEventListener('click', function(e) {
    var target = e.target.href;
    if (target == "http://45.55.57.30/resourcespace/pages/home.php"){
        window.location.href = "http://45.55.57.30/resourcespace/pages/home.php";
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



</script>
