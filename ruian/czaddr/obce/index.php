<?php
require("config.php");
$query="select * from import.stat_obce";
$result=pg_query($CONNECT,$query);
$pole=pg_fetch_all($result);
// print_r($pole);
echo json_encode($pole);
?>
