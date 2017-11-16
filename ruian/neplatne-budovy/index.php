<script>
function openBuilding(x) {
    window.open('/building.php?latlng='+x, 'Building', 'width=780, height=1200, resizable=yes, scrollbars=yes, location=no')};
</script>
<?php
require_once("../config.php");

echo("\n");
echo("<html>\n");
echo("<div align=center>\n");
echo("<font size=7><b>Neplatné budovy</b></font><hr>\n");

$attr=$auth->getAttributes();

$f_user_id = 0;
if (isset($attr["id"])) $f_user_id = (int) $attr["id"][0];
if (isset($_REQUEST["f_user_id"])) $f_user_id = (int) $_REQUEST["f_user_id"];
$f_duvod = 0;
if (isset($_REQUEST["f_duvod"])) $f_duvod = (int) $_REQUEST["f_duvod"];
$f_okres_id = 0;
if (isset($_REQUEST["f_okres_id"])) $f_okres_id = (int) $_REQUEST["f_okres_id"];
$f_zmeneno = "false";
if (isset($_REQUEST["f_zmeneno"])) $f_zmeneno = "true";
$f_post = 0;
if (isset($_REQUEST["f_post"])) $f_post = (int) $_REQUEST["f_post"];
echo ("<form action=\".\" method=\"post\">\n");

echo ("<b>Nick:</b>\n");
$RESULT=pg_query($CONNECT,"select user_nick,user_id from osmtables.neplatne_budovy group by user_nick,user_id order by user_nick");
echo "<select name=\"f_user_id\" size=\"0\">\n";
echo ("<option value=\"0\">--------</option>\n");
for ($i=0;$i<pg_num_rows($RESULT);$i++)
{
    echo "<option value=\"".pg_result($RESULT,$i,"user_id")."\"";
	if ($f_user_id==pg_result($RESULT,$i,"user_id")) echo " selected";
    echo ">".pg_result($RESULT,$i,"user_nick")."</option>\n";
}
echo "</select>\n";

echo ("<b>Důvod:</b>\n");
$RESULT=pg_query($CONNECT,"select id,popis from osmtables.neplatne_budovy_duvod order by id");
echo "<select name=\"f_duvod\" size=\"0\">\n";
echo ("<option value=\"0\">--------</option>\n");
for ($i=0;$i<pg_num_rows($RESULT);$i++)
{
    echo "<option value=\"".pg_result($RESULT,$i,"id")."\"";
	if ($f_duvod==pg_result($RESULT,$i,"id")) echo " selected";
    echo ">".pg_result($RESULT,$i,"id")." - ".pg_result($RESULT,$i,"popis")."</option>\n";
}
echo "</select>\n";

echo ("<b>Okres:</b>\n");
$RESULT=pg_query($CONNECT,"select kod,nazev from ruian.rn_okres where not deleted order by nazev");
echo "<select name=\"f_okres_id\" size=\"0\">\n";
echo ("<option value=\"0\">--------</option>\n");
for ($i=0;$i<pg_num_rows($RESULT);$i++)
{
    echo "<option value=\"".pg_result($RESULT,$i,"kod")."\"";
	if ($f_okres_id==pg_result($RESULT,$i,"kod")) echo " selected";
    echo ">".pg_result($RESULT,$i,"nazev")."</option>\n";
}
echo "</select>\n";

echo ("<b>Jen změněné v RÚIAN:</b>\n");
echo "<input type=\"checkbox\" name=\"f_zmeneno\"";
if ($f_zmeneno=="true") echo " checked";
echo ">\n";

echo "<input type=\"hidden\" name=\"f_post\" value=\"1\">\n";

echo "<input type=\"submit\" value=\"OK\">\n";

echo "</form>\n";
if ($f_post == 0) die;

$sort=0;
if (isset($_REQUEST['sort'])) $sort=$_REQUEST['sort'];
if ( !is_numeric($sort) ) die;
$t="nazev";
if ($sort==1) $t="todo desc";
if ($sort==2) $t="procent";
$query = "select * from osmtables.neplatne_budovy_view where true";
if ($f_user_id != 0) $query = $query . " and user_id = " .$f_user_id;
if ($f_duvod != 0) $query = $query . " and duvod = ".$f_duvod;
if ($f_okres_id != 0) $query = $query . " and okres_kod = ".$f_okres_id;
if ($f_zmeneno == "true") $query = $query . " and aktualizovano";
$result=pg_query($CONNECT,$query);

//echo("<br><font size=6><b>Katastrální území</b></font><br><br>\n");
echo("<table cellpadding=2 cellspacing=6 border=0>\n");
echo("<tr>");
echo("<td></td>");
echo("<td><b>kód SO</b></td>");
echo("<td><b>vložil(a)<b></td>");
echo("<td><b>dne</b></td>");
echo("<td><b>adresa (RÚIAN)</b></td>");
echo("<td><b>poloha (mapapi)</b></td>");
echo("<td><b>důvod (edit)</b></td>");
echo("<td>ČÚZK</td>");
echo("</tr>\n");
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
    echo("<td>".pg_result($result,$i,"zadano")."</td>\n");
    if (pg_result($result,$i,"existuje") == "t") {
	echo("<td><a href=\"http://ruian.poloha.net/19/".str_replace(" ","/",pg_result($result,$i,"lokace"))."/B\">".pg_result($result,$i,"adresa")."</a></td>\n");
    }
    else
    {
	echo("<td></td>\n");
    }
    echo("<td><a href=\"http://mapapi.poloha.net/search?query=".pg_result($result,$i,"lokace")."\">".pg_result($result,$i,"lokace")."</a></td>\n");
    echo("<td><a href=\"../building.php?kod=".pg_result($result,$i,"kod")."\" target=\"Building\" onclick=\"openBuilding()\">".pg_result($result,$i,"popis")."</a></td>\n");
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
