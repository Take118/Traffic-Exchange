<?php

global $db;

session_start();
include("vars.php");
include("headfoot.php");

var_dump($self_url);

$db = mysqli_connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
if (isset($_GET['ref']) && is_numeric($_GET['ref']) && !isset($_SESSION['refdata'])) {
	$rck = $db->query("SELECT id, acctype FROM user WHERE id=$_GET[ref] && status='Active'");
	if ($rck->num_rows != 0) {
		$rck->data_seek(0);
		$acctype = $rck->fetch_array()["acctype"];
		@$rck->free_result();

		$rck = $db->query("SELECT rpgebonus FROM acctype WHERE id=$acctype");
		$rck->data_seek(0);
		$creditme = $rck->fetch_array()["rpgebonus"];

		$_SESSION['ref'] = $_GET['ref'];
		$ipis = $_SERVER['REMOTE_ADDR'];
		$validip = 'no';
		@$rck->free_result();

		if (is_string($ipis) && preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\./i' . '([0-9]{1,3})\.([0-9]{1,3})$', $ipis, $sect)) {
			if ($sect[1] <= 255 && $sect[2] <= 255 && $sect[3] <= 255 && $sect[4] <= 255) {
				$validip = 'yes';
				$recip = "$sect[1].$sect[2].$sect[3]";
			} else {
				$validip = 'no';
			}
		} else {
			$validip = 'no';
		}
		if ($validip == 'yes' && $recip != "" && $creditme > 0) {
			$_SESSION['ref'] = $_GET['ref'];
			$ressu = $db->query("SELECT id FROM referstats WHERE usrid=$_GET[ref] && refip='$recip'") or die($db->error);
			if ($ressu->num_rows == 0) {
				$todayis = date("Y-m-d");
				$timeis = date("H:i:s");
				$htt_ref = $_SERVER['HTTP_REFERRER'];
				if ($htt_ref == "") {
					//
					$htt_ref = "Direct Request/Referring Info Blocked";
				}
				@$db->query("INSERT INTO referstats (usrid, orgip, refip, cdate, ctime, httpref, browser) VALUES ($_GET[ref], '$ipis', '$recip', '$todayis', '$timeis', '$htt_ref', '" . $_SERVER['HTTP_USER_AGENT'] . "')") or die($db->error);
				if ($creditme > 0) {
					@$db->query("UPDATE user SET credits=credits+$creditme, rpage_credits=rpage_credits+$creditme, lifetime_credits=lifetime_credits+$creditme WHERE id=$_GET[ref]") or die($db->error);
					$iearned_n = "Your sponsor just earned <b>$creditme</b> credits for showing you this page. Become a member and you can too!";
				}
			}
			@$ressu->free_result();
			header("location:index.php");
			exit();
		}
	} else {
		$_GET['ref'] = 0;
		@$rck->free_result($rck);
	}
}
uheader();
include("main_page.php");
ufooter();
$db->close();
exit;
