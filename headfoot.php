<?php

global $db;
@$db = new mysqli;

function get_main_content()
{
	global $db;

	$rst = @$db->query("select content from html where type='1page'");
	$rst->data_seek(0);
	$mncontent = $rst->fetch_array();
	echo ($mncontent);
}
function uheader()
{
	global $m_header, $ref;
	include($m_header);
}
function ufooter()
{
	global $m_footer, $ref;
	include($m_footer);
}
function secheader()
{
	global $mem_header, $usrid;
	include($mem_header);
}
function members_main_menu()
{
	global $title, $fontface, $server_clock, $usrid, $self_url;
	$s_vals = file_reader("/memlinks.html");
	$s_vals = str_replace('[show_server_time]', $server_clock, $s_vals);
	$s_vals = str_replace('[session_name]', session_name(), $s_vals);
	$s_vals = str_replace('[session_id]', session_id(), $s_vals);
	$s_vals = str_replace('[self_url]', $self_url, $s_vals);
	echo ($s_vals);
}
function secfooter()
{
	global $mem_footer, $usrid;
	include($mem_footer);
}
function file_reader($fileurl)
{
	$fileurl = $_SERVER['DOCUMENT_ROOT'] . $fileurl;
	$file = fopen($fileurl, 'r') or die("File Doesn't Exist");
	$contents = fread($file, filesize($fileurl));
	fclose($file);
	return $contents;
}
function checkPTCdata($dateis)
{
	global $db;

	@$db->query("UPDATE ptc_orders SET date_done='$dateis' WHERE clicks_remain=0 && date_done=null");
	return;
}
function text($limit = 1, $fstart = "", $fend = "")
{
	global $db;

	$res = $db->query("select text from tads order by rand() limit $limit");
	if ($res->num_rows > 0) {
		while ($r = $res->fetch_array()) {
			$text = $r['text'];
			echo $fstart . $text . $fend;
		}
	}
	@$res->free_result();
}
function get_referral($vid)
{
	global $db;

	$query = "select ref_id from member_refs where mem_id=$vid";
	if ($res = $db->query($query)) {
		if ($res1 = $res->fetch_array()) {
			return $res1[0];
		}
	}
	@$res->free_result();
}
function credit_ref_bonuses($par_id)
{
	global $db;

	for ($i = 0; $i < count($par_id); $i++) {
		$get_ref_data = $db->query("SELECT acctype FROM user WHERE id=$par_id[$i] && status='Active'");
		if ($get_ref_data->num_rows != 0) {
			$get_ref_data->data_seek(0);
			$refacc = $get_ref_data->fetch_field();
			$rs = $db->query("SELECT rbonuses FROM acctype WHERE id=$refacc");
			$rs->data_seek(0);
			$get_bonuses = $rs->fetch_field();
			$bonuses = explode(",", $get_bonuses);
			$givebonus = $bonuses[$i];
			if (!is_numeric($givebonus)) {
				$givebonus = 0;
			}
			@$db->query("UPDATE user SET credits=credits+$givebonus, rbon_credits=rbon_credits+$givebonus, lifetime_credits=lifetime_credits+$givebonus WHERE id=$par_id[$i]");
			@$db->query("update adminprops set value=value-$givebonus where field='surplu'");
		}
		@$get_ref_data->free_result();
	}
}
function get_ref_levels($mid, $z)
{
	global $db;
	global $tier;

	$squery = "select count(*),mem_id from member_refs where ref_id in ($mid) group by mem_id";
	if ($res = $db->query($squery)) {
		$tier[$z] = $res->num_rows;
		$res = $res->fetch_array();
		$mquery = "select mem_id from member_refs where ref_id in ($mid)";
		if ($resultx = $db->query($mquery)) {
			$z = 1;
			while ($rsvz = $resultx->fetch_array()) {
				$rr_id[$z] = $rsvz[0];
				$z++;
			}
		}
		return $rr_id;
	}
	//@$res->free_result();
}
function credit_r_bonuses($par_id, $type, $ammt)
{
	global $db;

	$zzz = 0;
	for ($i = 0; $i < count($par_id); $i++) {
		$zzz++;
		$get_ref_data = $db->query("SELECT acctype FROM user WHERE id=$par_id[$i] && status='Active'");
		if ($get_ref_data->num_rows != 0) {
			$get_ref_data->data_seek(0);
			$refacc = $get_ref_data->fetch_field();
			if ($type == 'credits') {
				$get_ref_data = $db->query("SELECT levels FROM acctype WHERE id=$refacc");
				$get_ref_data->data_seek(0);
				$get_bonuses = $get_ref_data->fetch_field();
			} else {
				$get_ref_data = $db->query("SELECT ptc_levels FROM acctype WHERE id=$refacc");
				$get_ref_data->data_seek(0);
				$get_bonuses = $get_ref_data->fetch_field();
			}
			$bonuses = explode(",", $get_bonuses);
			$givebonus = $bonuses[$i] / 100;
			$givebonus = round($givebonus, 2);
			$givebonus = $givebonus * $ammt;
			if ($zzz == 1) {
				$return_val = $givebonus;
			}
			if (!is_numeric($givebonus)) {
				$givebonus = 0;
			}
			if ($type == 'credits') {
				@$db->query("UPDATE user SET credits=credits+$givebonus, crdsfrmallrefs=crdsfrmallrefs+$givebonus, lifetime_credits=lifetime_credits+$givebonus WHERE id=$par_id[$i]");
				@$db->query("update adminprops set value=value-$givebonus where field='surplu'");
			} else {
				@$db->query("UPDATE user SET cshfrmallrefs=cshfrmallrefs+$givebonus, roi_cash=roi_cash+$givebonus, lifetime_cash=lifetime_cash+$givebonus WHERE id=$par_id[$i]");
				@$db->query("update adminprops set value=value-$givebonus where field='csurpl'");
				$get_refstats = $db->query("SELECT * FROM monthly_stats WHERE usrid=$par_id[$i] && yearis=" . date("Y") . " && monthis=" . date("m"));
				if ($get_refstats->num_rows == 0) {
					@$db->query("INSERT INTO monthly_stats (usrid, refptc_cash, tot_owed, monthis, yearis) VALUES ($par_id[$i], $givebonus, $givebonus, " . date("m") . ", " . date("Y") . ")") or die($db->error);
				} else {
					@$db->query("UPDATE monthly_stats SET refptc_cash=refptc_cash+$givebonus, tot_owed=tot_owed+$givebonus WHERE usrid=$par_id[$i] && yearis=" . date("Y") . " && monthis=" . date("m")) or die($db->error);
				}
			}
		}
	}
	return $return_val;
}
function ref_shunt($memb_id)
{
	global $db;

	$par_id = get_referral($memb_id);
	$query = "SELECT mem_id FROM member_refs WHERE ref_id=$memb_id";
	$chv_id = array();
	$i = 0;
	if ($res = $db->query($query)) {
		while ($id = $res->fetch_array()) {
			$chv_id[$i] = $id[0];
			$i++;
		}
		$queryv = "UPDATE member_refs SET ref_id=$par_id WHERE mem_id=";
		for ($i = 0; $i < count($chv_id); $i++) {
			$db->query($queryv . $chv_id[$i]);
		}
	}
	return 1;
}
function totalmembers()
{
	global $db;

	$resz = $db->query("SELECT id FROM user");
	return $resz->num_rows;
}
function totalupgrademembers()
{
	global $db;

	$resz = $db->query("SELECT id FROM user WHERE acctype='2'");
	return $resz->num_rows;
}
function totalsiteinrotation()
{
	global $db;

	$resz = $db->query("SELECT * FROM site");
	return $resz->num_rows;
}
function totalsiteshowntoday()
{
	global $db;

	$resum = $db->query("SELECT SUM(pg_views) FROM 7stat WHERE date='" . date('Y-m-d') . "'");
	$resum->data_seek(0);
	$sum = $resum->fetch_field();
	$sum = empty($sum) ? 0 : $sum;
	return $sum;
}
function totalmembersufringnow()
{
	global $db;

	$res = $db->query("SELECT count(*) FROM 7stat WHERE date='" . date('Y-m-d') . "' AND time > '" . date('H:i:s', (time() - 30)) . "'");
	$res->data_seek(0);
	return $res->fetch_field();
}
function totalpayout()
{
	global $db;

	$resum = $db->query("SELECT SUM(amount) FROM cashout_history WHERE amount>0");
	$resum->data_seek(0);
	$sum = $resum->fetch_field();
	$sum = empty($sum) ? 0 : $sum;
	$resum1 = $db->query("SELECT SUM(amount) FROM investment_history WHERE amount>0 AND is_from='Upline Earnings'");
	$resum1->data_seek(0);
	$sum1 = $resum1->fetch_field();
	$sum1 = empty($sum1) ? 0 : $sum1;
	$sum2 = '$ ' . number_format(($sum1 + $sum), 2, '.', ',');
	return $sum2;
}
