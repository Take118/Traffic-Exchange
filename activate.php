<?php
session_start();
include("vars.php");
include("headfoot.php");

global $db;
$db = new mysqli();

$db->connect($db_host, $db_user, $db_pwd);
$db->select_db($db_name);
if (!isset($_GET['ac']) || !is_numeric($_GET['ac']) || !isset($_GET['i']) || !is_numeric($_GET['i'])) {
    echo ("<head><title>$title</title><meta http-equiv=\"Refresh\" content=\"1;URL=$self_url\"></head><body><span style=\"font-size:250%\">$title</span><br><span style=\"font-size:150%\">Failed to resolve activation code!</span></body></html>");
    $db->close();
    exit;
}
$res = $db->query("select email, ref from user where id=" . $_GET['i'] . "  && status='Un-verified' && ac=" . $_GET['ac']);
if ($res->num_rows != 1) {
    echo ("<head><title>$title</title><meta http-equiv=\"Refresh\" content=\"1;URL=$self_url\"></head><body><span style=\"font-size:250%\">$title</span><br><span style=\"font-size:150%\">Failed to resolve activation code!</span></body></html>");
    $db->close();
    exit;
}
$res->data_seek(0);
$email = $res->fetch_fields()["email"];
$myref = $res->fetch_fields()["ref"];
if ($activation_pages == 0) {
    $my_stat = "Active";
    $accs = $db->query("SELECT * FROM acctype");
    for ($i = 0; $i < $accs->num_rows; $i++) {
        $accs->data_seek($i);
        $accids = $accs->fetch_fields()["id"];
        $r_bons = $accs->fetch_fields()["rbonuses"];
        $r_bons2[$accids] = explode(",", $r_bons);
        $acc_r_bon[$accids] = count($r_bons2[$accids]);
    }
    if ($acc_r_bon[2] > $acc_r_bon[1]) {
        $greatest = 2;
    } elseif ($acc_r_bon[1] > $acc_r_bon[2]) {
        $greatest = 1;
    } else {
        $greatest = 2;
    }
    if ($myref >= 1 && ($acc_r_bon[1] > 0 || $acc_r_bon[2] > 0)) {
        $cc = 0;
        $ref_id[$cc] = $myref;
        for ($v = 0; $v < ($acc_r_bon[$greatest] - 1); $v++) {
            $myref = get_referral($myref);
            if (!$myref || $myref == 0)
                break;
            ++$cc;
            $ref_id[$cc] = $myref;
        }
        credit_ref_bonuses($ref_id);
    }
} else {
    $my_stat = "Verified";
}
$res = $db->query("update user set status='$my_stat', ac=0 where id=" . $_GET['i']) or die("Please contact $title Admin there was an error, listed below please inlcude with your contact request...<br><br>Error was:<br>" . $db->error);
uheader();
echo ("<h4>Account Activation</h4><p><br><b>Your account was activated!</b><br>");
echo ("<form action=\"$self_url" . "members/mem_auth.php\" method=post name=login><input type=hidden name=form value=sent>
<b>You may now login to your account...</b><br><br>");
echo ("E-mail:<br><input type=text name=email size=20 maxlength=100 value=$email><br>Password:<br><input type=password name=passwd size=20 maxlength=20><br><input type=submit value=\" Login \" style=\"font-size: 11px; padding: 2px;\"></form>\n");
ufooter();
$db->close();
exit;
