<?php
require("config.php");
$query="select *,round(importovano/celkem*100,2) as procent,round(zpracovavano/celkem*100,2) as zpracovavanoprocent from
    (select okres,okres_kod,obec,obec_kod,cast_obce,sum(celkem) as celkem,sum(importovano) as importovano,sum(zpracovavano) as zpracovavano,sum(procent_duchu) as procent_duchu from import.stat_all
    where obec_kod=".$id."
    group by okres,okres_kod,obec,obec_kod,cast_obce) as foo order by okres,obec,cast_obce";
$result=pg_query($CONNECT,$query);
if (pg_num_rows($result) < 1) die;

echo("<br><font size=6><b><a href=\"okres.php?id=".pg_result($result,0,"okres_kod")."\">Obec ".pg_result($result,0,"obec")."</a></b></font><br><br>\n");
echo("<table cellpadding=2 border=0>\n");
echo("<tr><td></td><td><b>»·st obce</b></td><td><b>Adres</b></td><td><b>Nahr·no</b></td><td><b>Procent</b></td><td><b>Edituje se</b></td><td><b>Procent</b></td><td></td></tr>\n");
for ($i=0;$i<pg_num_rows($result);$i++)
    {
    echo("<tr>\n");
    echo("<td>");
    $iks = pg_result($result,$i,"procent_duchu");
    if ($iks < 1) echo("<img src=\"ghost-green.png\" title=\"".round($iks,2)." % duch˘ uvnit¯ budovy\">");
    if ($iks >= 1 and $iks < 2) echo("<img src=\"ghost-blue.png\" title=\"".round($iks,2)." % duch˘ uvnit¯ budovy\">");
    if ($iks >= 2 and $iks < 3) echo("<img src=\"ghost-grey.png\" title=\"".round($iks,2)." % duch˘ uvnit¯ budovy\">");
    if ($iks >= 3 and $iks < 6) echo("<img src=\"ghost-brown.png\" title=\"".round($iks,2)." % duch˘ uvnit¯ budovy\">");
    if ($iks >= 6 and $iks < 10) echo("<img src=\"ghost-red.png\" title=\"".round($iks,2)." % duch˘ uvnit¯ budovy\">");
    if ($iks >= 10 ) echo("<img src=\"death.png\" title=\"".round($iks,2)." % duch˘ uvnit¯ budovy\">");
    echo("</td>\n");
    echo("<td>".pg_result($result,$i,"cast_obce")."</td>\n");
    echo("<td>".pg_result($result,$i,"celkem")."</td>\n");
    echo("<td>".pg_result($result,$i,"importovano")."</td>\n");
    echo("<td>".pg_result($result,$i,"procent")."</td>\n");
    echo("<td>".pg_result($result,$i,"zpracovavano")."</td>\n");
    echo("<td>".pg_result($result,$i,"zpracovavanoprocent")."</td>\n");
    echo("<td><img src=\"image.php?p=".pg_result($result,$i,"procent")."&q=".pg_result($result,$i,"zpracovavanoprocent")."\"></td>\n");
    echo("</tr>\n");
    }
echo("</table>\n");

echo("</div>\n");
echo("</html>\n");
?>
