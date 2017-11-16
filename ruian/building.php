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
	select so.kod,nb.zadano,nb.user_nick,nb.datum,nb.duvod,zv.nazev,ruian.adresa_budovy(so.kod),
	    duvod.popis,nb.poznamka,nb.hlasit_cuzk,nb.hlaseno,nb.zmenil_nick,
	    parcela.kmenove_cislo || case when parcela.poddeleni_cisla is not NULL then '/' || parcela.poddeleni_cisla else '' end as parcela,
	    parcela.id as parcela_id
	from ruian.rn_stavebni_objekt so
	left join osmtables.neplatne_budovy nb on so.kod=nb.kod
	left join osmtables.neplatne_budovy_duvod duvod on nb.duvod=duvod.id
	left join osmtables.zpusob_vyuziti_objektu zv on so.zpusob_vyuziti_kod=zv.kod
	left join ruian.rn_parcela parcela on so.identifikacni_parcela_id = parcela.id
	where st_intersects(st_transform(st_setsrid(st_makepoint(".$lon.",".$lat."),4326),900913),so.hranice)
	and not so.deleted
	limit 1
    ");
    $queryran=true;
}
if (isset($_REQUEST['kod'])) {
    $kod= (int) $_REQUEST['kod'];
    $RESULT=pg_query($CONNECT,"
	select so.kod,nb.zadano,nb.user_nick,nb.datum,nb.duvod,zv.nazev,ruian.adresa_budovy(so.kod),
	    duvod.popis,nb.poznamka,nb.hlasit_cuzk,nb.hlaseno,nb.zmenil_nick,
	    parcela.kmenove_cislo || case when parcela.poddeleni_cisla is not NULL then '/' || parcela.poddeleni_cisla else '' end as parcela,
	    parcela.id as parcela_id
	from ruian.rn_stavebni_objekt so
	left join osmtables.neplatne_budovy nb on so.kod=nb.kod
	left join osmtables.neplatne_budovy_duvod duvod on nb.duvod=duvod.id
	left join osmtables.zpusob_vyuziti_objektu zv on so.zpusob_vyuziti_kod=zv.kod
	left join ruian.rn_parcela parcela on so.identifikacni_parcela_id = parcela.id
	where so.kod=".$kod." and not so.deleted
	limit 1
    ");
    if (pg_num_rows($RESULT) != 1) {
	$RESULT=pg_query($CONNECT,"
	select nb.kod,nb.zadano,nb.user_nick,nb.datum,nb.duvod,'Objekt z RÚIAN smazán'::text as nazev,NULL::text as adresa_budovy,
	    duvod.popis,nb.poznamka,nb.hlasit_cuzk,nb.hlaseno,nb.zmenil_nick
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
    $datum=pg_result($RESULT,0,"zadano");
    $zmeneno=pg_result($RESULT,0,"datum");
    $zmenil=pg_result($RESULT,0,"zmenil_nick");
    $duvod=pg_result($RESULT,0,"duvod");
    $popis=pg_result($RESULT,0,"popis");
    $poznamka=pg_result($RESULT,0,"poznamka");
    $nazev=pg_result($RESULT,0,"nazev");
    $hlasit_cuzk=pg_result($RESULT,0,"hlasit_cuzk");
    $hlaseno=pg_result($RESULT,0,"hlaseno");
    $parcela=pg_result($RESULT,0,"parcela");
    $parcela_id=pg_result($RESULT,0,"parcela_id");
    echo "SO: ".$i_kod." (".$nazev.")";
    if ($parcela != '') echo " na parcele ".$parcela." (".$parcela_id.")";
    echo "<br>".$adresa."<hr>\n";
    if ($i_user_nick != "")
	{ echo "Zadal(a): ".$i_user_nick." dne ".$datum;
	if ($zmenil != "")
	    { echo ", naposled upravil(a) ".$zmenil." dne ".$zmeneno; }
	echo "<br>Důvod: ".$popis."<br>\n";}
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

    // mapa
    $resmap = pg_query($CONNECT,"select * from osmtables.neplatne_budovy_geom where kod = ".$i_kod);
    if (pg_result($resmap,0,"ma_geom") == "t") {
    require_once("map.js");
	echo ("<hr>\n");
    }
    // formular
    $RESULT=pg_query($CONNECT,"select id,popis from osmtables.neplatne_budovy_duvod order by id");
    echo "<br>\n";
    echo "<form action=\"building-post.php\" method=\"post\">\n";
    echo "Proč je budova chybná?<br>\n";
    echo "<br>\n";
    echo "<select name=\"duvod\" size=\"";
    echo pg_num_rows($RESULT);
    echo "\"";
    if (!$logged or $hlaseno=="t") echo " disabled";
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
    if (!$logged or $hlaseno=="t") echo " disabled";
    if ($hlasit_cuzk=="t") echo " checked";
    echo ">\n";
    echo "<br>\n";
    echo "<br>\n";
    echo "Poznámka:<br>\n";
    echo "<br>\n";
    echo "<textarea name=\"poznamka\" cols=\"60\" rows=\"4\" wrap=\"soft\"";
    if (!$logged or $hlaseno=="t") echo " disabled";
    echo ">\n";
    echo $poznamka;
    echo "</textarea>\n";
    echo "<br>\n";
    echo "<br>\n";
    echo "<input type=\"submit\" value=\"Odeslat\"";
    if (!$logged or $hlaseno=="t") echo " disabled";
    echo ">";
    echo "<input type=\"hidden\" name=\"kod\" value=\"".$i_kod."\">\n";
    echo "</form>\n";
    if ($duvod != "" and $logged and $hlaseno == "f") {
	echo "<form action=\"building-post.php\" method=\"post\">\n";
	echo "<button type=\"submit\" name=\"delete\"";
	echo "><font color=\"red\"><b>Smazat záznam (nutno zaškrtnout políčko vedle)</b></font></button>\n";
        echo "<input type=\"checkbox\" name=\"deleteit\"";
        echo ">\n";
	echo "<input type=\"hidden\" name=\"kod\" value=\"".$i_kod."\">\n";
	echo "</form>\n";
    }

}

$RESULT=pg_query($CONNECT,"select nl.*,d.popis from osmtables.neplatne_budovy_log nl left join osmtables.neplatne_budovy_duvod d on nl.new_duvod = d.id where nl.kod = ".$i_kod." order by nl.id");
if (pg_num_rows($RESULT) > 1)
    {
	echo ("<hr><b>Historie změn:</b><br>\n<table border=1><thead><tr><th>Akce</th><th>Datum</th><th>Nick</th><th>Důvod</th><th>Hlásit</th><th>Hlášeno</th><th>Poznámka</th></tr></thead>");
	for ($i=0;$i<pg_num_rows($RESULT);$i++)
	    {
		echo ("<tr><td>");
		switch (pg_result($RESULT,$i,"action"))
		    {
			case "I": echo ("Založení");break;
			case "U": echo ("Změna");break;
			case "D": echo ("Smazání");break;
		    }
		echo ("</td>\n");
		echo ("<td>".pg_result($RESULT,$i,"new_datum")."</td><td>".pg_result($RESULT,$i,"new_user_nick")."</td>\n");
		echo ("<td>");
		if (pg_result($RESULT,$i,"old_duvod") != pg_result($RESULT,$i,"new_duvod"))
		{
		    echo (pg_result($RESULT,$i,"popis"));
		}
		echo ("</td>\n");
		echo ("<td>");
		if (pg_result($RESULT,$i,"old_hlasit_cuzk") != pg_result($RESULT,$i,"new_hlasit_cuzk"))
		{
		    if (pg_result($RESULT,$i,"new_hlasit_cuzk") == "t")
			{ echo ("ANO");
			}
		    else
			{ echo ("NE");
			}
		}
		echo ("</td>\n");
		echo ("<td>");
		if (pg_result($RESULT,$i,"old_hlaseno") != pg_result($RESULT,$i,"new_hlaseno"))
		{
		    if (pg_result($RESULT,$i,"new_hlaseno") == "t")
			{ echo ("ANO");
			}
		    else
			{ echo ("NE");
			}
		}
		echo ("</td>\n");
		echo ("<td>");
		if (pg_result($RESULT,$i,"old_poznamka") != pg_result($RESULT,$i,"new_poznamka"))
		{
		    echo (pg_result($RESULT,$i,"new_poznamka"));
		}
		echo ("</td>\n");
		echo ("</tr>\n");
	    }
	echo ("</table><hr>\n");

    }

?>
