<?php
require_once("config.php");

$logged=false;
if (!$auth->isAuthenticated())
    echo "Uživatel nepřihlášen - <a href=\"".$auth->getLoginURL()."\">přihlásit</a>";
if ($auth->isAuthenticated()) {
    $attr=$auth->getAttributes();
    $user_id='';
    $user_nick='';
    $osm_user='';
    if (isset($attr["id"])) $user_id=$attr["id"][0];
    if (isset($attr["nick"])) $user_nick=$attr["nick"][0];
    if (isset($attr["osm_user"])) $logged=true;
}
if ($logged) {
    echo("Přihlášen jako ".$user_nick." - ");
    echo "<a href=\"".$auth->getLogoutURL()."\">odhlásit</a>";
}

echo("<hr>\n");

$l='';
$queryran=false;
if (isset($_REQUEST['latlng'])) {
    $l= $_REQUEST['latlng'];
    $left=strpos($l,'(');
    $right=strpos($l,')');
    $mid=strpos($l,',');
    $lat=substr($l,$left+1,$mid-$left-1);
    $lon=substr($l,$mid+1,$right-$mid-1);
    $lat = (float) $lat;
    $lon = (float) $lon;
    $RESULT=pg_query($CONNECT,"
	select so.kod,nb.datum,nb.user_nick,nb.datum,nb.duvod,zv.nazev,ruian.adresa_budovy(so.kod),
	    duvod.popis,nb.poznamka,nb.hlasit_cuzk
	from ruian.rn_stavebni_objekt so
	left join osmtables.neplatne_budovy nb on so.kod=nb.kod
	left join osmtables.neplatne_budovy_duvod duvod on nb.duvod=duvod.id
	left join osmtables.zpusob_vyuziti_objektu zv on so.zpusob_vyuziti_kod=zv.kod
	where st_intersects(st_transform(st_setsrid(st_makepoint(".$lon.",".$lat."),4326),900913),so.hranice)
	and not so.deleted
	limit 1
    ");
    $queryran=true;
}
if (isset($_REQUEST['kod'])) {
    $kod= (int) $_REQUEST['kod'];
    $RESULT=pg_query($CONNECT,"
	select so.kod,nb.datum,nb.user_nick,nb.datum,nb.duvod,zv.nazev,ruian.adresa_budovy(so.kod),
	    duvod.popis,nb.poznamka,nb.hlasit_cuzk
	from ruian.rn_stavebni_objekt so
	left join osmtables.neplatne_budovy nb on so.kod=nb.kod
	left join osmtables.neplatne_budovy_duvod duvod on nb.duvod=duvod.id
	left join osmtables.zpusob_vyuziti_objektu zv on so.zpusob_vyuziti_kod=zv.kod
	where so.kod=".$kod." and not so.deleted
	limit 1
    ");
    if (pg_num_rows($RESULT) != 1) {
	$RESULT=pg_query($CONNECT,"
	select nb.kod,nb.datum,nb.user_nick,nb.datum,nb.duvod,'Objekt z RÚIAN smazán'::text as nazev,NULL::text as adresa_budovy,
	    duvod.popis,nb.poznamka,nb.hlasit_cuzk
	from osmtables.neplatne_budovy nb
	left join osmtables.neplatne_budovy_duvod duvod on nb.duvod=duvod.id
	where nb.kod=".$kod."
	limit 1
    ");
    }
    $queryran=true;
}
if ($queryran) {
    if (pg_num_rows($RESULT) != 1) {
	echo "Budova nenalezena.";
	die;
    }
    $adresa=pg_result($RESULT,0,"adresa_budovy");
    $i_kod=pg_result($RESULT,0,"kod");
    $i_user_nick=pg_result($RESULT,0,"user_nick");
    $datum=pg_result($RESULT,0,"datum");
    $duvod=pg_result($RESULT,0,"duvod");
    $popis=pg_result($RESULT,0,"popis");
    $poznamka=pg_result($RESULT,0,"poznamka");
    $nazev=pg_result($RESULT,0,"nazev");
    $hlasit_cuzk=pg_result($RESULT,0,"hlasit_cuzk");
    echo "SO: ".$i_kod." (".$nazev.")<br>".$adresa."<hr>\n";
    if ($i_user_nick != "") echo "Zadal(a): ".$i_user_nick." dne ".$datum."<br>Důvod: ".$popis."<br>\n";
    if ($poznamka != "") echo "Poznámka: ".$poznamka."<br>\n";
    if ($hlasit_cuzk != "") {
	if ($hlasit_cuzk == "t") {
	    echo "Hlásit ČÚZK: ANO<br>\n";
	}
	else
	{
	    echo "Hlásit ČÚZK: NE<br>\n";
	}
    }
    if ($i_user_nick != "") echo("<hr>\n");
    // formular
    $RESULT=pg_query($CONNECT,"select id,popis from osmtables.neplatne_budovy_duvod order by id");
    echo "<br>\n";
    echo "<form action=\"building-post.php\" method=\"post\">\n";
    echo "Proč je budova chybná?<br>\n";
    echo "<br>\n";
    echo "<select name=\"duvod\" size=\"";
    echo pg_num_rows($RESULT);
    echo "\"";
    if (!$logged) echo " disabled";
    echo ">\n";
    for ($i=0;$i<pg_num_rows($RESULT);$i++) {
	echo "<option value=\"".pg_result($RESULT,$i,"id")."\"";
	if ($duvod==pg_result($RESULT,$i,"id")) echo " selected";
	echo ">".pg_result($RESULT,$i,"popis")."</option>\n";
    }
    echo "</select>\n";
    echo "<br>\n";
    echo "<br>\n";
    echo "Hlásit ČÚZK: \n";
    echo "<input type=\"checkbox\" name=\"hlasit_cuzk\"";
    if (!$logged) echo " disabled";
    if ($hlasit_cuzk=="t") echo " checked";
    echo ">\n";
    echo "<br>\n";
    echo "<br>\n";
    echo "Poznámka:<br>\n";
    echo "<br>\n";
    echo "<textarea name=\"poznamka\" cols=\"80\" rows=\"4\" wrap=\"soft\"";
    if (!$logged) echo " disabled";
    echo ">\n";
    echo $poznamka;
    echo "</textarea>\n";
    echo "<br>\n";
    echo "<br>\n";
    echo "<input type=\"submit\" value=\"Odeslat\"";
    if (!$logged) echo " disabled";
    echo ">";
    echo "<input type=\"hidden\" name=\"kod\" value=\"".$i_kod."\">\n";
    echo "</form>\n";
    if ($duvod != "" and $logged) {
	echo "<form action=\"building-post.php\" method=\"post\">\n";
	echo "<button type=\"submit\" name=\"delete\"";
	echo "><font color=\"red\"><b>Smazat záznam (nutno zaškrtnout políčko vedle)</b></font></button>\n";
        echo "<input type=\"checkbox\" name=\"deleteit\"";
        echo ">\n";
	echo "<input type=\"hidden\" name=\"kod\" value=\"".$i_kod."\">\n";
	echo "</form>\n";
    }

}
?>
