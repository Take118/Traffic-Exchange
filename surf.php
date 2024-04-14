<?php

global $db;
$db= new mysqli();

session_start();
include("vars.php");
include("auth.php");
$db->connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);
$res = checkauth();
$usrid = $res->fetch_fields()["id"];
$acctype = $res->fetch_fields()["acctype"];
@$res->free_result();
if ($acctype == 1) {$daily_max = $surf_max_free;} else {$daily_max = $surf_max_pro;}
if ($_SESSION['sess_data']['usrid'] != $usrid) {
	$_SESSION['sess_data']['cts'] = 0;
	$_SESSION['sess_data']['sts'] = 0;
	$_SESSION['sess_data']['pgv'] = 0;
	$_SESSION['sess_data']['usrid'] = $usrid;
}
$res = $db->query("SELECT num FROM 7stat WHERE usrid=$usrid && date='".date('Y-m-d')."'");
if ($res->num_rows != 0)
{
	$crds_today = $res->fetch_field();
}
else
{
	$crds_today = 0;
}
@$res->free_result();
if ($crds_today >= $daily_max)
{
	echo("Sorry you have earned your daily maximum of $daily_max credits!<br />Please return tomorrow!<br /><br /><a href=\"$self_url\">Login to your account here</a>");
	session_destroy();
	$db->close();
	exit;
}
@$crds_today->free_result();
$res = $db->query("select value from adminprops where field='negact'");
$_SESSION['sess_data']['negact'] = $res->fetch_field();
@$res->free_result();
$res = $db->query("select value from adminprops where field='reftim'");
$_SESSION['sess_data']['reftim'] = $res->fetch_field();
@$res->free_result();
$res = $db->query("select value from adminprops where field='contex'");
if ($res->fetch_field() != 0) {
	$_SESSION['sess_data']['contex'] = $res->fetch_field();
	@$res->free_result();
	$res = $db->query("select value from adminprops where field='contey'");
	$_SESSION['sess_data']['contey'] = $res->fetch_field();
}
@$res->free_result();
$res = $db->query("select value from adminprops where field='contcx'");
if ($res->fetch_field() != 0) {
	$_SESSION['sess_data']['contcx'] = $res->fetch_field();
	@$res->free_result();
	$res = $db->query("select value from adminprops where field='contcy'");
	$_SESSION['sess_data']['contcy'] = $res->fetch_field();
}
@$res->free_result();
$res = $db->query("select minmax from user where id=$usrid");
$rate = $res->fetch_field();
$_SESSION['sess_data']['mmax'] = $rate;
switch($rate) {
	case 1:
		$rate = 'ratemin';
		break;
	case 0:
		$rate = 'ratemax';
		break;
	default:
		$rate = 'ratemax';
		break;
}
@$res->free_result();
$res = $db->query("select $rate, ref from acctype, user where acctype.id=user.acctype && user.id=$usrid");
$_SESSION['sess_data']['rate'] = $res->fetch_fields()[$rate];
$_SESSION['sess_data']['ref'] = $res->fetch_fields()["ref"];
@$res->free_result();
$_SESSION['sess_data']['surfing'] = rand(9999, 9999999999);
$_SESSION['sess_data']['from'] = md5($_SESSION['sess_data']['surfing']);
if ($_GET['next'] == md5($_SESSION['sess_data']['surf_encoder_vals'])) {
	$s_bar_url = "surfbar.php?PHPSESSID=" . session_id() . "&vc_val=" . $_GET['next'];
} else {
	$s_bar_url = "surfbar.php?PHPSESSID=" . session_id() . "&vc_val=begin&coder=". md5($_SESSION['sess_data']['from']);
}
echo("<html>\n<head>\n<title>$title: Surf</title>\n<link rel=stylesheet type=text/css href=$self_url" . "style.css>\n</head>\n<frameset rows=90,* border=0><frame marginheight=0 marginwidth=0 scrolling=no noresize border=0 src=\"$s_bar_url\"><frame marginheight=0 marginwidth=0 scrolling=auto noresize border=0 src=./target.php></frameset>\n</html>");
$db->close();
exit;
?>
