<?php
if(isset($_REQUEST['z'])) $z=$_REQUEST['z'];
if(isset($_REQUEST['x'])) $x=$_REQUEST['x'];
if(isset($_REQUEST['y'])) $y=$_REQUEST['y'];
if(isset($_REQUEST['layer'])) $layer=$_REQUEST['layer'];
$z = (int) $z;
$x = (int) $x;
$y = (int) $y;
if ($z < 8 || $z > 30) die;
$DBHOST = "/tmp/";
$DBDATABASE = "pedro";
$DBUSER = "guest";
$DBPASSWORD = "guest";
$DBPORT="6432";
$CONNECT = pg_pconnect("host=$DBHOST dbname=$DBDATABASE password=$DBPASSWORD user=$DBUSER port=$DBPORT")
 or die("Databaze je down.");
$set = pg_query($CONNECT,"set client_encoding to UNICODE;");
$result = pg_query($CONNECT,"select jsontiles.jsontile(".$z.",".$x.",".$y.")");
if (pg_num_rows($result) != 1) die;
header('Content-Type: application/json; charset=utf-8');
echo pg_result($result,0,"jsontile");
?>
