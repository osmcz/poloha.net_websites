<script>
function openBuilding(x) {
    window.open('/building.php?latlng='+x, 'Building', 'width=780, height=580, resizable=yes, scrollbars=yes, location=no')};
</script>
<?php

global $CONNECT,$RESULT,$DBDATABASE,$DBUSER,$DBPASSWORD;

require_once("/var/www/simplesamlphp/lib/_autoload.php");
$auth = new SimpleSAML_Auth_Simple("osm");

$DBHOST = "localhost";
$DBDATABASE = "pedro";
$DBUSER = "guest";
$DBPASSWORD = "guest";
$CONNECT = pg_connect("host=$DBHOST dbname=$DBDATABASE password=$DBPASSWORD user=$DBUSER")
 or die("Databaze je down.");
$set = pg_query($CONNECT,"set client_encoding to UNICODE;");

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


$f_query=0; // zda zobrazit vysledek nebo jen formular
$f_uid=0; // uid
$f_zmeny=0; // jen v RUIAN zmenene
$f_okres=0; // okres





$sort=0;
if (isset($_REQUEST['sort'])) $sort=$_REQUEST['sort'];
if ( !is_numeric($sort) ) die;
$t="nazev";
if ($sort==1) $t="todo desc";
if ($sort==2) $t="procent";
$query = "select * from osmtables.neplatne_budovy_view";
//$result=pg_query($CONNECT,$query);
echo("\n");
echo("<html>\n");
echo("<div align=center>\n");
echo("<font size=7><b>Neplatné budovy</b></font><hr>\n");
if ($logged) {
    $res=pg_query($CONNECT,"select user_nick || '_(' || user_id || ')' as title,user_nick,user_id,case when user_id=".$user_id." then 1 else 0 end as myself from osmtables.neplatne_budovy group by user_id,user_nick,myself order by myself desc,user_nick;");
}
else
{
    $res=pg_query($CONNECT,"select user_nick || '_(' || user_id || ')' as title,user_nick,user_id,0 as myself from osmtables.neplatne_budovy group by user_id,user_nick,myself order by user_nick;");
}



echo("<table cellpadding=2 cellspacing=6 border=0>\n");
echo("<tr>");
echo "<form action=. method=\"post\">\n";
echo("<td><input type=\"checkbox\" name=\"Jen změněné?\"></td>");
echo("<td><b>kód SO</b></td>");
echo("<td><b>vložil(a)</b><br />");
echo "<select name=\"Nick\" size=\"1\">\n";
echo "<option value=\"0\">Vše</option>\n";
for ($i=0;$i<pg_num_rows($res);$i++) {
    echo "<option value=\"".pg_result($res,$i,"user_id")."\"";
    if (pg_result($res,$i,"myself") == 1 ) echo " selected";
    echo ">";
    if (pg_result($res,$i,"myself") == 1 ) echo "<b>";
    echo pg_result($res,$i,"title");
    if (pg_result($res,$i,"myself") == 1 ) echo "</b>";
    echo "</option>\n";
}
echo "</select>\n";

echo ("</td>");
echo("<td><b>dne</b></td>");
echo("<td><b>adresa (RÚIAN)</b></td>");
echo("<td><b>poloha (mapapi)</b></td>");
echo("<td><b>důvod (edit)</b></td>");
echo("<td>ČÚZK</td>");
echo("</tr>\n");
die;
for ($i=0;$i<pg_num_rows($result);$i++)
    {
    echo("<tr>\n");
    echo("<td>");
    if (pg_result($result,$i,"aktualizovano") == "t" and pg_result($result,$i,"ma_geom") == "t") echo "<img src=\"task-attention.png\" title=\"Po zadání sem byla budova v RÚIAN aktualizována\">";
    if (pg_result($result,$i,"aktualizovano") == "t" and pg_result($result,$i,"ma_geom") != "t") echo "<img src=\"face-sad.png\" title=\"Budova byla 'opravena' tak, že byla odstraněna její geometrie\">";
    if (pg_result($result,$i,"existuje") == "f") echo "<img src=\"task-reject.png\" title=\"Budova z RÚIAN zmizela\">";
    echo "</td>\n";
    echo("<td><a href=\"http://vdp.cuzk.cz/vdp/ruian/stavebniobjekty/".pg_result($result,$i,"kod")."\">".pg_result($result,$i,"kod")."</a></td>\n");
    echo("<td>".pg_result($result,$i,"user_nick")."</td>\n");
    echo("<td>".pg_result($result,$i,"datum")."</td>\n");
    if (pg_result($result,$i,"existuje") == "t") {
	echo("<td><a href=\"http://ruian.poloha.net/19/".str_replace(" ","/",pg_result($result,$i,"lokace"))."/B\">".pg_result($result,$i,"adresa")."</a></td>\n");
    }
    else
    {
	echo("<td></td>\n");
    }
    echo("<td><a href=\"http://mapapi.poloha.net/search?query=".pg_result($result,$i,"lokace")."\">".pg_result($result,$i,"lokace")."</a></td>\n");
    echo("<td><a href=\"/building.php?kod=".pg_result($result,$i,"kod")."\" target=\"Building\" onclick=\"openBuilding()\">".pg_result($result,$i,"popis")."</a></td>\n");
    echo("<td>");
    if (pg_result($result,$i,"hlaseno") == "t") echo "<img src=\"task-complete.png\" title=\"Ohlášeno na ČÚZK\">";
    if (!(pg_result($result,$i,"hlaseno") == "t") and pg_result($result,$i,"hlasit_cuzk") == "t") echo "<img src=\"run-build.png\" title=\"Bude se hlásit na ČÚZK\">";
    if (!(pg_result($result,$i,"hlaseno") == "t") and !(pg_result($result,$i,"hlasit_cuzk") == "t")) echo "<img src=\"process-stop.png\" title=\"Nebude se hlásit na ČÚZK\">";
    echo "</td>\n";
    echo("</tr>\n");
    }
echo("</table>\n");

echo("</div>\n");
echo("</html>\n");
?>
