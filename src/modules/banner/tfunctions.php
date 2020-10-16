<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Bannerrotation
function banner_rotate($group=1) {
	global $set,$db,$apx;
	$group=(int)$group;

	$data=$db->fetch("SELECT id,code,capping,ratio FROM ".PRE."_banner AS a WHERE ( '".time()."' BETWEEN starttime AND endtime AND a.group='".$group."' AND ( a.limit='0' OR a.views<a.limit ) ) ORDER BY id");
	if ( !count($data) ) return '';
	
	$cached = array();
	foreach ( $data AS $res ) {
		if ( $res['capping'] && intval($_COOKIE[$set['main']['cookie_pre'].'_capping_'.$res['id']])>=$res['capping'] ) {
			continue;
		}
		$cached[$res['id']]=$res;
		for ( $i=1; $i<=$res['ratio']; $i++ ) $ratio[]=$res['id'];
	}
	if ( !$cached ) return;
	
	srand((float)microtime()*10000000);
	$key=array_rand($ratio);
	
	$viewid=$ratio[$key];
	$db->query("UPDATE ".PRE."_banner SET views=views+1 WHERE id='".$viewid."' LIMIT 1");
	
	if ( $cached[$viewid]['capping'] ) {
		if ( headers_sent() ) {
			echo '<img src="'.HTTPDIR.'misc.php?action=banner_cap&amp;viewid='.$viewid.'" style="width:1px;height:1px;position:absolute;top:0;left:0;">';
		}
		else {
			setcookie($set['main']['cookie_pre'].'_capping_'.$viewid, intval($_COOKIE[$set['main']['cookie_pre'].'_capping_'.$viewid])+1);
		}
	}
	
	echo $cached[$viewid]['code'];
}

?>