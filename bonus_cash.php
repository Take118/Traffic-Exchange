<?php

global $db;
$db = new mysqli();

session_start();
//$_SESSION("sess_data");
include("vars.php");
include("headfoot.php");
include("auth.php");

$db->connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);
$res = checkauth();
$usrid = $res->fetch_fields()["id"];
if ($_SESSION['sess_data']['ccwon'] != 'ccreallycc' || $_GET['next'] != md5($_SESSION['sess_data']['surf_encoder_vals'])) {
    header("Location: $self_url" . "surf.php?next=" . $_GET['next']);
    $db->close();
    exit;
}
unset($_SESSION['sess_data']['ccwon']);
$res = $db->query("select value from adminprops where field='contcx'");
if ($res->fetch_fields()["value"] != 0) {
    $contcx = $res->fetch_fields()["value"];
    $res = $db->query("select value from adminprops where field='contcy'");
    $contcy = $res->fetch_fields()["value"];
} else {
    header("Location: $self_url" . "surf.php?next=" . $_GET['next']);
    $db->close();
    exit;
}
if ($contcy != $_SESSION['sess_data']['contcy']) {
    $_SESSION['sess_data']['contcy'] = $contcy;
}
if ($contcx != $_SESSION['sess_data']['contcx']) {
    header("Location: $self_url" . "surf.php?next=" . $_GET['next']);
    $db->close();
    exit;
}
$get_stats = $db->query("SELECT * FROM monthly_stats WHERE usrid=$usrid && yearis=" . date("Y") . " && monthis=" . date("m"));
if ($get_stats->num_rows == 0) {
    $ins_upd = $db->query("INSERT INTO monthly_stats (usrid, sbcash_earned, tot_owed, monthis, yearis) VALUES ($usrid, " . $_SESSION['sess_data']['contcy'] . ", " . $_SESSION['sess_data']['contcy'] . ", " . date("m") . ", " . date("Y") . ")") or die($db->error);
} else {
    $ins_upd = $db->query("UPDATE monthly_stats SET sbcash_earned=sbcash_earned+" . $_SESSION['sess_data']['contcy'] . ", tot_owed=tot_owed+" . $_SESSION['sess_data']['contcy'] . " WHERE usrid=$usrid && yearis=" . date("Y") . " && monthis=" . date("m")) or die($db->error);
}
$res = $db->query("update user set roi_cash=roi_cash+" . $_SESSION['sess_data']['contcy'] . ", lifetime_cash=lifetime_cash+" . $_SESSION['sess_data']['contcy'] . ", sb_cash=sb_cash+" . $_SESSION['sess_data']['contcy'] . " where id=$usrid") or die($db->error);
$surpres = $db->query("update adminprops set value=value-" . $_SESSION['sess_data']['contcy'] . " where field='csurpl'");
secheader();

echo ("<h4>Bonus Cash Won!</h4>
<p>Congratulations! <b>\$" . $_SESSION['sess_data']['contcy'] . " Cash</b> was just added to your account!</p>\n<p><a href=$self_url" . "surf.php?next=" . $_GET['next'] . ">Continue back To Surf</a><br>
<a href=$self_url" . "members/>Go To Member Area</a></p>\n");
secfooter();
$db->close();
exit;
