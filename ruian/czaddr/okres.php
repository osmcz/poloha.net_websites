<?php
require("config.php");
$query="select *,round(importovano/celkem*100,2) as procent,round(zpracovavano/celkem*100,2) as zpracovavanoprocent from
    (select okres,obec,obec_kod,sum(celkem) as celkem,sum(importovano) as importovano,sum(zpracovavano) as zpracovavano, (sum(pocet_duchu)::real/sum(pocet_so)::real) * 100 as procent_duchu from import.stat_all
    where okres_kod=".$id."
    group by okres,okres_kod,obec,obec_kod) as foo order by okres,obec";
$result=pg_query($CONNECT,$query);
if (pg_num_rows($result) < 1) die;

echo("<br><font size=6><b>Okres ".pg_result($result,0,"okres")."</b></font><br><br>\n");
echo("<table cellpadding=2 border=0>\n");
echo("<tr><td></td><td><b>Obec</b></td><td><b>Adres</b></td><td><b>Nahráno</b></td><td><b>Procent</b></td><td><b>Edituje se</b></td><td><b>Procent</b></td><td></td></tr>\n");
for ($i=0;$i<pg_num_rows($result);$i++)
    {
    echo("<tr>\n");
    echo("<td>");
    $iks = pg_result($result,$i,"procent_duchu");
    if ($iks < 1.0) echo("<img src=\"ghost-green.png\" title=\"".round($iks,2)." % duchù uvnitø budovy\">");
    if ($iks >= 1.0 and $iks < 2.0) echo("<img src=\"ghost-blue.png\" title=\"".round($iks,2)." % duchù uvnitø budovy\">");
    if ($iks >= 2.0 and $iks < 3.0) echo("<img src=\"ghost-grey.png\" title=\"".round($iks,2)." % duchù uvnitø budovy\">");
    if ($iks >= 3.0 and $iks < 5.0) echo("<img src=\"ghost-brown.png\" title=\"".round($iks,2)." % duchù uvnitø budovy\">");
    if ($iks >= 5.0 and $iks < 20.0) echo("<img src=\"ghost-red.png\" title=\"".round($iks,2)." % duchù uvnitø budovy\">");
    if ($iks >= 20.0) echo("<img src=\"death.png\" title=\"".round($iks,2)." % duchù uvnitø budovy\">");
    echo("</td>\n");
    echo("<td><a href=\"obec.php?id=".pg_result($result,$i,"obec_kod")."\">".pg_result($result,$i,"obec")."</a></td>\n");
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
