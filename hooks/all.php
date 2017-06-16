<script>
var target;
document.addEventListener('click', function(e) {
    var target = e.target.href;
    if (target == "http://45.55.57.30/resourcespace/pages/home.php"){
        window.location.href = "http://45.55.57.30/resourcespace/pages/home.php";
    }
});

/***************** OTHER OPTIONS FOR RS BACK NAV WORKAROUND *******************/
/*
function CentralSpaceLoad () {
    //alert("central space load");
    if (target == "http://45.55.57.30/resourcespace/pages/home.php"){
        window.location.href = "http://45.55.57.30/resourcespace/pages/home.php";
        return false;
    }
}

top.window.onpopstate = function(event) {
	if (!event.state) {console.log('no event state'); return true;} // no state
	page = window.history.state;
	mytitle = page.substr(0, page.indexOf('&&&'));
	if (mytitle.substr(-1,1) != "'" && mytitle.length! = 0) {
		page = page.substr(mytitle.length+3);
		document.title = mytitle;
		// calculate the name of the page the user is navigating to.
		pagename = basename(document.URL);
		pagename = pagename.substr(0, pagename.lastIndexOf('.'));
		if (pagename == "home") {
			window.location.href = "http://45.55.57.30/resourcespace/pages/home.php";
		}
		ModalClose();
		jQuery('#CentralSpace').html(page);
	}
}

function LoadActions(pagename,id,type,ref) {
    window.location.href = "http://45.55.57.30/resourcespace/pages/home.php";
}
*/
</script>
