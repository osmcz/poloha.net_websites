<?php
require_once("config.php");
if (!$auth->isAuthenticated()) die;
if (!isset($_REQUEST["kod"])) die;
$attr=$auth->getAttributes();
$user_id='';
$user_nick='';
$osm_user='';
if (isset($attr["id"])) $user_id = (int) $attr["id"][0];
if (isset($attr["nick"])) $user_nick = pg_escape_string($attr["nick"][0]);
if (!isset($attr["osm_user"])) die;
if (!isset($_REQUEST["kod"])) die;
$kod = (int) $_REQUEST["kod"];
$duvod=0;
if (isset($_REQUEST["duvod"])) $duvod = (int) $_REQUEST["duvod"];
$hlasit_cuzk="false";
if (isset($_REQUEST["hlasit_cuzk"])) $hlasit_cuzk = "true";
$poznamka = "";
if (isset($_REQUEST["poznamka"])) $poznamka = pg_escape_string($_REQUEST["poznamka"]);

//echo "Poznamka: ".$poznamka."<br>Duvod: ".$duvod."<br>Hlasit: ".$hlasit_cuzk."<br>";
//var_dump($_REQUEST);die;

// delete
if (isset($_REQUEST["delete"]) and !isset($_REQUEST["deleteit"])) die("<font size=\"5\"><b>Nezaškrtli jste potvrzovací checkbox.</b></font>");
if (isset($_REQUEST["delete"]) and isset($_REQUEST["deleteit"])) {
    if (!$RESULT=pg_query($CONNECT,"select osmtables.delete_building(".$kod.",".$user_id.",'".$user_nick."')"))
	echo "<font size=\"5\"><b>Něco je divně.</b></font><br>\n";
    if (pg_result($RESULT,0,"delete_building") != 1) echo "<font size=\"5\"><b>Něco je divně.</b></font><br>\n";
    echo "<font size=\"5\"><b>Budova smazána.</b></font><br>\n";
}
else { // update or insert
    if ($duvod==0) die("<font size=\"5\"><b>Důvod musí být vybrán.</b></font>");
    if (!$RESULT=pg_query($CONNECT,"select kod from osmtables.neplatne_budovy where kod = ".$kod))
	echo "<font size=\"5\"><b>Něco je divně.</b></font><br>\n";
    if (pg_num_rows($RESULT) == 1) { // update
	if (!$RESULT=pg_query($CONNECT,"update osmtables.neplatne_budovy set
	duvod=".$duvod.
	",hlasit_cuzk=".$hlasit_cuzk.
	",poznamka='".$poznamka.
	"',user_id=".$user_id.
	",user_nick='".$user_nick."',datum=now()
	where kod=".$kod))
	    echo "<font size=\"5\"><b>Něco je divně.</b></font><br>\n";
	if (pg_affected_rows($RESULT) != 1) echo "<font size=\"5\"><b>Něco je divně.</b></font><br>\n";
	echo "<font size=\"5\"><b>Budova aktualizována.</b></font>";
    }
    else { // insert
	if (!$RESULT=pg_query($CONNECT,"insert into osmtables.neplatne_budovy
	(kod,
	duvod,
	hlasit_cuzk,
	datum,
	user_id,
	user_nick,
	poznamka,
	geom)
	values ("
	.$kod.","
	.$duvod.","
	.$hlasit_cuzk."
	,now(),"
	.$user_id.",'"
	.$user_nick."','"
	.$poznamka."',(select hranice from ruian.rn_stavebni_objekt where kod=".$kod."))"))
	    echo "<font size=\"5\"><b>Něco je divně.</b></font><br>\n";
	echo "<font size=\"5\"><b>Budova přidána.</b></font>";
    }
}

?>
