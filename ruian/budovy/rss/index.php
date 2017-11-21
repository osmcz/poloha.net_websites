<?php
global $CONNECT,$RESULT,$DBDATABASE,$DBUSER,$DBPASSWORD;

$DBHOST = "localhost";
$DBDATABASE = "pedro";
$DBUSER = "guest";
$DBPASSWORD = "guest";
$CONNECT = pg_connect("host=$DBHOST dbname=$DBDATABASE password=$DBPASSWORD user=$DBUSER")
 or die("Databaze je down.");
$set = pg_query($CONNECT,"set client_encoding to latin2;");

// require_once("config.php");

  header('Content-Type: text/xml; charset=iso-8859-2');
echo("<?xml version=\"1.0\" encoding=\"iso-8859-2\"?>");
$result=pg_query($CONNECT,"select buildings_todo::date as a from import.datatimestamp");
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Chybìjící budovy v OSM</title>
    <description>Stav k <?php echo (pg_result($result,0,"a"))?></description>
    <link>http://poloha.net/budovy/rss</link>
    <webMaster>pv@poloha.net (Petr Vejsada)</webMaster>
    <atom:link rel="self" href="http://ruian.poloha.net/budovy/rss" type="application/rss+xml" />
<?php
    $RESULT = pg_query("select * from import.mapovat_budovy where todo > 1 order by todo desc");
    while($rec = pg_fetch_array($RESULT)) { ?>
    <item>
      <title><?php echo $rec["nazev"]; ?></title>
      <?php
      if ($rec["relation_id"] != "")
      echo("<link>http://mapapi.poloha.net/relation/".$rec["relation_id"]."</link>\n");
      ?>
      <guid isPermaLink="false">http://mapapi.poloha.net/relation/<?php echo $rec["kod"]; ?></guid>
      <description><?php echo("KÚ " . $rec["kod"] . " - " . $rec["nazev"] . ", chybí " . $rec["todo"] . ", t.j. " . number_format(100-$rec["procent"],2) . "% z celkového poètu " . $rec["buildings"] . ".") ?></description>
    </item>
<?php
    }
?>
    </channel>
</rss>
