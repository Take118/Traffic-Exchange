<?php

global $db;
$db = new mysqli();

session_start();
//$_SESSION("sess_name");
//$_SESSION("sess_passwd");
//$_SESSION("sess_data");
include("vars.php");
include("headfoot.php");
$db->connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);
$res = $db->query("select * from faq order by id asc");
uheader();
echo("<h4>Frequently Asked Questions (FAQs)</h4>");
for ($i = 0; $i < $res->num_rows; $i++) {
	$res->data_seek($i);
	$quest = $res->fetch_fields()["quest"];
	$answ = $res->fetch_fields()["answ"];
	echo("<p><b>$quest</b><br>$answ</p>");
}
ufooter();
$db->close();
exit;
?>
