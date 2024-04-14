<?php

global $db;
$db= new mysqli();

include("vars.php");
include("headfoot.php");
$db->connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);
if ($_POST['form'] == 'sent' && $_POST['email'] != "") {
	$email = trim($_POST['email']);
	$res = $db->query("select * from user where email='$email'");
	if ($res->num_rows == 1) {
		$admail = ($db->query("select value from admin where field='email'"))->fetch_field();
		$res->data_seek(0);
		$uid = $res->fetch_fields()["id"];
		$passwd = $res->fetch_fields()["passwd"];
		$act = $res->fetch_fields()["ac"];
		if ($act != 0) {
			$actcode = "Your account is not yet activated, please click the link below to activate your account!\n\n$self_url" . "activate.php?ac=$act&i=$uid\n\n<a href=\"$self_url" . "activate.php?ac=$act&i=$uid\">AOL Users</a>";
		}
		mail($email, "$title password reminder", "Your $title login is: $email\n\nYour $title password is: $passwd\n\n$actcode" . "Regards\n\n$title Admin\nhttp://$siteurl/", $email_headers);
		uheader();
		echo("<h4>Password/Activation Retrieval</h4><p>Your details were sent to your registered email address:<br><b>$email</b></p>");
		ufooter();
		$db->close();
		exit;
	} else {
		uheader();
		echo("<h4>Password/Activation Retrieval</h4><p>Your email address was not found!</p>");
		ufooter();
		$db->close();
		exit;
	}
} else {
	uheader();
	echo("<h4>Password/Activation Retrieval</h4>\n");
	echo("<p>Enter your e-mail address in the field below and press '<b>Send Password</b>'.</font></p>");
	echo("<p><form action=$self_url" . "lost.php method=post><input type=hidden name=form value=sent><input type=text name=email class=webforms> <input type=submit value=\" Send Password \" style=\"font-size: 11px; padding: 2px;\"></form></p>");
	ufooter();
	$db->close();
	exit;
}
?>
