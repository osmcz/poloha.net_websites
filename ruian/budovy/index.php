<?php
ini_set('default_charset','iso8859-2');
global $CONNECT,$RESULT,$DBDATABASE,$DBUSER,$DBPASSWORD;

$DBHOST = "localhost";
$DBDATABASE = "pedro";
$DBUSER = "guest";
$DBPASSWORD = "guest";
$CONNECT = pg_connect("host=$DBHOST dbname=$DBDATABASE password=$DBPASSWORD user=$DBUSER")
 or die("Databaze je down.");
$set = pg_query($CONNECT,"set client_encoding to latin2;");

$sort=0;
if (isset($_REQUEST['sort'])) $sort=$_REQUEST['sort'];
if ( !is_numeric($sort) ) die;
$t="nazev";
if ($sort==1) $t="todo desc";
if ($sort==2) $t="procent";
$query = "select sum(todo) as todo from import.mapovat_budovy";
$result=pg_query($CONNECT,$query);
if (pg_num_rows($result) != 1) die;
$todo=pg_result($result,0,"todo");
echo("\n");
echo("<html>\n");
echo("<div align=center>\n");
echo("<font size=7><b>Budovy</b></font><hr>\n");
echo("<font size=6><b>V OSM je ".number_format($todo,0,".",".")." chybìjících èi posunutých budov.</b></font><br>\n");
$result=pg_query($CONNECT,"select buildings_todo::date as a from import.datatimestamp");
echo("<b>(stav k ".pg_result($result,0,"a").")</b><br>\n");
echo("<hr>\n");


$query="select buildings,mapped,todo,round(procent::numeric,2) as procent,kod,nazev,relation_id from import.mapovat_budovy order by ".$t."";
$result=pg_query($CONNECT,$query);
if (pg_num_rows($result) < 1) die;

echo("<br><font size=6><b>Katastrální území</b></font><br><br>\n");
echo("<table cellpadding=2 cellspacing=6 border=0>\n");
echo("<tr>");
echo("<td><b><a href=\"/budovy\">KÚ</a></b></td>");
echo("<td><b>Kód KÚ<b></td>");
echo("<td><b>Budov v RÚIAN</b></td>");
echo("<td><b>Zmapováno</b></td>");
echo("<td><b><a href=\"?sort=2\">Procent</a></b></td>");
echo("<td><b><a href=\"?sort=1\">Chybí</a></b></td>");
echo("</tr>\n");
for ($i=0;$i<pg_num_rows($result);$i++)
    {
    echo("<tr>\n");
    echo("<td><a href=\"http://mapapi.poloha.net/relation/".pg_result($result,$i,"relation_id")."\">".pg_result($result,$i,"nazev")."</a></td>\n");
    echo("<td>".pg_result($result,$i,"kod")."</td>\n");
    echo("<td>".pg_result($result,$i,"buildings")."</td>\n");
    echo("<td>".pg_result($result,$i,"mapped")."</td>\n");
    echo("<td>".pg_result($result,$i,"procent")."</td>\n");
    echo("<td>".pg_result($result,$i,"todo")."</td>\n");
    echo("</tr>\n");
    }
echo("</table>\n");

echo("</div>\n");
echo("</html>\n");
?>
