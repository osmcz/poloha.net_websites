<?php
  require_once("config.php");
  header('Content-Type: text/xml; charset=iso-8859-2');
echo("<?xml version=\"1.0\" encoding=\"iso-8859-2\"?>");
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Tipy na mapování budov</title>
    <link>http://poloha.net/tipy-na-mapovani-budov</link>
    <webMaster>pv@poloha.net (Petr Vejsada)</webMaster>
    <atom:link href="http://ruian.poloha.net/mapovat_budovy/" rel="self" type="application/rss+xml" />
<?php
    $RESULT = pg_query("select * from import.mapovat_budovy_view where todo > 9");
    while($rec = pg_fetch_array($RESULT)) { ?>
    <item>
      <title><?php echo $rec["nazev"]; ?></title>
      <?php
      if ($rec["relation_id"] != "")
      echo("<link>http://mapapi.poloha.net/relation/".$rec["relation_id"]."</link>\n");
      ?>
      <guid><?php echo $rec["kod"]; ?>/</guid>
      <description><?php echo("KÚ " . $rec["kod"] . " - " . $rec["nazev"] . ", okres " . $rec["okres"] . ", chybí " . $rec["todo"]) ?></description>
    </item>
<?php
    }
?>
    </channel>
</rss>
