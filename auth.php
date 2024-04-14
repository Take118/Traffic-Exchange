<?php

global $db;

if ($_SERVER['REMOTE_ADDR']=='127.0.0.1') {$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];}
if (isset($_REQUEST['_SESSION'])) {
	header("Location: $self_url/?e=sess-invasion");
	@session_destroy();
	@$db->close();
	exit;
}
function checkauth() {
	global $db;

	global $self_url;
	//exit(print_r($_SESSION));
	if (!isset($_SESSION['sess_name']) || !isset($_SESSION['sess_passwd'])) {
		header("Location: $self_url?session-expired");
		session_destroy();
		$db->close();
		exit;
	} else {
		$res = $db->query("select * from user where email='" . $_SESSION['sess_name'] . "'");
		if ($res->num_rows != 0) {
			$res->data_seek(0);
			$mysaved_pas = $res->fetch_fields()["passwd"];
			$my_ac = $res->fetch_fields()["ac"];
			if (md5($mysaved_pas) != $_SESSION['sess_passwd'] || $my_ac != 0) {
				header("Location: $self_url?login-error");
				session_destroy();
				$db->close();
				exit;
			}
		} else {
			header("Location: $self_url?user-not-found");
			session_destroy();
			$db->close();
			exit;
		}
		return($res);
	}
}

function checkbarauth() {
	global $db;

	global $self_url;
	if (!isset($_SESSION['sess_name']) || !isset($_SESSION['sess_passwd'])) {
		header("Location: $self_url"."bar_break.php?error=session-expired");
		session_destroy();
		$db->close();
		exit;
	} else {
		$res = $db->query("select * from user where email='" . $_SESSION['sess_name'] . "'");
		if ($res->num_rows != 0) {
			$res->data_seek(0);
			$mysaved_pas = $res->fetch_fields()["passwd"];
			$my_ac = $res->fetch_fields()["ac"];
			if (md5($mysaved_pas) != $_SESSION['sess_passwd'] || $my_ac != 0) {
				header("Location: $self_url"."bar_break.php?error=session-expired");
				session_destroy();
				$db->close();
				exit;
			}
		} else {
			header("Location: $self_url"."bar_break.php?error=session-expired");
			session_destroy();
			$db->close();
			exit;
		}
		return($res);
	}
}
?>
