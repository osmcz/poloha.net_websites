<?php
require_once("/var/www/simplesamlphp/lib/_autoload.php");
$auth = new SimpleSAML_Auth_Simple("osm");
$DBHOST = "localhost";
$DBDATABASE = "pedro";
$DBUSER = "hlasenibudov";
$DBPASSWORD = "censored";
$CONNECT = pg_connect("host=$DBHOST dbname=$DBDATABASE password=$DBPASSWORD user=$DBUSER")
 or die("Databaze je down.");
$set = pg_query($CONNECT,"set client_encoding to UNICODE;");
?>
