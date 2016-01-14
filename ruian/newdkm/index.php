<?php
  require_once("config.php");
  header('Content-Type: text/xml; charset=iso-8859-2');
echo("<?xml version=\"1.0\" encoding=\"iso-8859-2\"?>");
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Novì digitalizovaná katastrální území</title>
    <link>http://poloha.net/rss/newdkm</link>
    <webMaster>pv@poloha.net (Petr Vejsada)</webMaster>
    <atom:link href="http://ruian.poloha.net/newdkm/" rel="self" type="application/rss+xml" />
<?php
    $RESULT = pg_query("select * from ruian.dkm_rss_view order by datum desc");
    while($rec = pg_fetch_array($RESULT)) { ?>
    <item>
      <title><?php echo $rec["nazev"]; ?></title>
      <?php
      if ($rec["relation"] != "")
      echo("<link>http://mapapi.poloha.net/relation/".$rec["relation"]."</link>\n");
      ?>
      <guid><?php echo $rec["id"]; ?>/</guid>
      <description><?php echo("KÚ " . $rec["kod"] . " - " . $rec["nazev"] . ", obec " . $rec["obec"] . ", okres " . $rec["okres"] . " (" . $rec["plati_od"] . ")") ?></description>
    </item>
<?php
    }
?>
    </channel>
</rss>
