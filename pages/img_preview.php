<?php $ref =  htmlspecialchars($_GET["ref"]);
global $baseurl;
echo "<img id=\"preview_img\" style= \"width: 100%;\" src=\"" . $baseurl . "/pages/download.php?ref=".$ref ."\" alt=\"This image is not currently available.\">";
?>
