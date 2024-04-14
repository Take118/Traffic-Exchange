<?php

global $db;
$db = new mysqli();

session_start();
$_SESSION("sess_name");
$_SESSION("sess_passwd");
$_SESSION("sess_data");
include("vars.php");
include("headfoot.php");
$db->connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);
uheader();
?>

<h4>Member Surfing Statistics</h4>
<p>Below you will see the last 7 days of member credits earned, as well as, members websites shown. These statistics are automatically updated each time this page loads.</p>

<?php
$res = $db->query("select distinct(date) from 7stat order by date desc limit 7");
if ($res->num_rows > 0) {
    while ($r = $res->fetch_row()) {
        $arr1u[] = $r[0];
    }
}

$arr2 = array();
foreach ($arr1u as $k => $v) {
    $res = $db->query("select sum(num) from 7stat where date='$v'");
    $arr2[$v] = $res->fetch_field();
}
reset($arr2);
foreach ($arr2 as $k => $v) {
    $maxnum = $maxnum + $v;
}
$maxnum = round($maxnum);
echo ("<p><div style=\"padding-left: 10px\"><hr><br></div></p>");
echo ("<p align=left><b>Credits Earned Last 7 Days:</b>
<table width=100% style=\"padding-left: 10px;\">");
reset($arr2);
foreach ($arr2 as $k => $v) {
    $v = round($v);
    $px = 500 * ((($v * 100) / $maxnum) / 100);
    $px = round($px);
    echo ("<tr><td width=10%><b>$k:</b></td><td align=left>$v Credits Earned</td></tr>");
}
echo ("<tr align=center><td colspan=2><font color=#0000ff><b>7 Day Earned Total:</font><br>$maxnum Credits Earned</b></td></tr></table></p>");
echo ("<p><div style=\"padding-left: 10px\"><hr><br></div></p>");
$res = $db->query("select distinct(date) from 7statsite order by date desc limit 7");
$sarr1 = array();
if ($res->num_rows > 0) {
    while ($r = $res->fetch_row()) {
        $sarr1u[] = $r[0];
    }
}
$sarr2 = array();
reset($sarr1u);
foreach ($arr1u as $k => $v) {
    $res = $db->query("select sum(num) from 7statsite where date='$v'");
    $sarr2[$v] = $res->fetch_field();
}
$maxnum = 0;
reset($sarr2);
foreach ($arr2 as $k => $v) {
    $maxnum = $maxnum + $v;
}
$maxnum = round($maxnum);
echo ("<p align=left><b>Websites Shown Last 7 Days:</b>
<table width=100% style=\"padding-left: 10px;\">");
reset($sarr2);
foreach ($arr2 as $k => $v) {
    $v = round($v);
    $px = 500 * ((($v * 100) / $maxnum) / 100);
    $px = round($px);
    echo ("<tr><td width=10%><b>$k:</b></td><td align=left>$v Websites Shown</td></tr>");
}
echo ("<tr align=center><td colspan=2><font color=#0000ff><b>7 Day Shown Total:</font><br>$maxnum Websites Shown</b></td></tr></table></p>");
?>

<?php
ufooter();
$db->close();
exit;
?>