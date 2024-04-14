<?php

global $db;
$db = new mysqli();

function regmail()
{
	global $db;
	global $date;
	global $email_headers, $title, $siteurl;

	$datenowis = date("Y-m-d");
	$res = $db->query("select id, email, credits from user where lastmail!='$datenowis'");
	if ($res->num_rows != 0) {

		$wk = $db->query("select value from admin where field='email'");
		$wk->data_seek(0);
		$admail = $wk->fetch_fields()['value'];

		for ($i = 0; $i < $res->num_rows; $i++) {

			$res->data_seek($i);

			$id = $res->fetch_fields()["id"];
			$email = $res->fetch_fields()["email"];
			$credits = $res->fetch_fields()["credits"];

			$subj = "$title Weekly Stats";
			$message = "$title account #$id weekly statistics as of $date\n\nAccount credits: $credits\n\n";

			$sres = $db->query("select id, name, credits, totalhits, hitslastmail from site where usrid=$id");
			for ($si = 0; $si < $sres->num_rows; $si++) {

				$sres->data_seek($si);
				$sid = $sres->fetch_fields()["id"];
				$sname = $sres->fetch_fields()["name"];
				$scredits = $sres->fetch_fields()["credits"];
				$thits = $sres->fetch_fields()["totalhits"];
				$lhits = $sres->fetch_fields()["hitslastmail"];

				$message = $message . "Site: $sname\n\tCredits: $scredits\n\tHits this week: $lhits\n\tTotal hits: $thits\n\n";
				$sres2 = $db->query("update site set hitslastmail=0 where id=$sid");
			}
			$res2 = $db->query("update user set lastmail='$datenowis' where id=$id");
			$message = $message . "Regards\n\n$title Admin\nhttp://$siteurl/";
			mail($email, $subj, $message, $email_headers);
		}
	}
}
