<?php
require_once("config.php");

$l=false;
if (!$auth->isAuthenticated())
    echo "<a href=\"".$auth->getLoginURL()."\">Login</a>";
if ($auth->isAuthenticated()) {
    $attr=$auth->getAttributes();
    $user_id='';
    $user_nick='';
    $osm_user='';
    if (isset($attr["id"])) $user_id=$attr["id"][0];
    if (isset($attr["nick"])) $user_nick=$attr["nick"][0];
    if (isset($attr["osm_user"])) $l=true;
//    print_r($attr);
}
if ($l) {
    echo("Vitej, ".$user_nick."<br>");
    echo "<a href=\"".$auth->getLogoutURL()."\">Logout</a>";
}
?>
