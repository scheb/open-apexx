<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/


define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('poll').'functions.php');

$apx->module('poll');
$apx->lang->drop('poll');

$recent=poll_recent();
$_REQUEST['id']=(int)$_REQUEST['id'];
if ( $_REQUEST['recent'] ) $_REQUEST['id']=$recent;


//////////////////////////////////////////////////////////////////////////////////////////////////////// ARCHIV ANZEIGEN

if ( !$_REQUEST['id'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('archive');
	
	headline($apx->lang->get('HEADLINE_ARCHIVE'),mklink('poll.php','poll.html'));
	titlebar($apx->lang->get('HEADLINE_ARCHIVE'));
	
	$data=$db->fetch("SELECT id,question,addtime,starttime,days,a1_c+a2_c+a3_c+a4_c+a5_c+a6_c+a7_c+a8_c+a9_c+a10_c+a11_c+a12_c+a13_c+a14_c+a15_c+a16_c+a17_c+a18_c+a19_c+a20_c AS total FROM ".PRE."_poll WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(!$set['poll']['archall']," AND ( id!='".$recent."' OR endtime<='".time()."' ) ")." ".section_filter()." ) ORDER BY starttime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Tags
			if ( in_array('ARCHIVE.TAG',$parse) || in_array('ARCHIVE.TAG_IDS',$parse) || in_array('ARCHIVE.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = poll_tags($res['id']);
			}
			
			$tabledata[$i]['QUESTION']=$res['question'];
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['STARTTIME']=$res['starttime'];
			$tabledata[$i]['ENDTIME']=($res['starttime']+$res['days']*24*3600);
			$tabledata[$i]['VOTES']=$res['total'];
			
			//Tags
			$tabledata[$i]['TAG']=$tagdata;
			$tabledata[$i]['TAG_IDS']=$tagids;
			$tabledata[$i]['KEYWORDS']=$keywords;
			
			$tabledata[$i]['LINK']=mklink(
				'poll.php?id='.$res['id'],
				'poll,'.$res['id'].urlformat($res['question']).'.html'
			);
		}
	}
	
	$apx->tmpl->assign('ARCHIVE',$tabledata);
	$apx->tmpl->parse('archive');
	
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// ABSTIMMEN

$query="SELECT *,a1_c+a2_c+a3_c+a4_c+a5_c+a6_c+a7_c+a8_c+a9_c+a10_c+a11_c+a12_c+a13_c+a14_c+a15_c+a16_c+a17_c+a18_c+a19_c+a20_c AS total FROM ".PRE."_poll WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ) ".iif ( !$user->is_team_member()," AND ( '".time()."' BETWEEN starttime AND endtime ) ")." ".section_filter()." ORDER BY starttime DESC LIMIT 1";
$pollinfo=$db->first($query);
if ( !$pollinfo['id'] ) filenotfound();
if ( $user->info['userid'] ) list($ipblock)=$db->first("SELECT ip FROM ".PRE."_poll_iplog WHERE ( id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' AND time>".(time()-24*3600)." ) LIMIT 1");
else list($ipblock)=$db->first("SELECT ip FROM ".PRE."_poll_iplog WHERE ( id='".$_REQUEST['id']."' AND ip='".ip2integer(get_remoteaddr())."' AND time>".(time()-24*3600)." ) LIMIT 1");

if ( $_POST['send']
	&& ( $recent==$_REQUEST['id'] || $set['poll']['archvote'] )
	&& $_POST['vote']
	&& $_COOKIE[$set['main']['cookie_pre'].'_voted'][$_REQUEST['id']]!='1'
	&& !$ipblock
) {
	$_POST['vote'] = (int)$_POST['vote'];
	//Mehrere
	if ( $pollinfo['multiple'] ) {
		$cset=array();
		
		foreach ( $_POST['vote'] AS $aid => $true ) {
			$aid=(int)$aid;
			if ( !$aid || $aid<1 || $aid>20 || $true!='1' ) continue;
			$cset[]='a'.$aid.'_c=a'.$aid.'_c+1';
		}
		
		if ( count($cset) ) $db->query("UPDATE ".PRE."_poll SET ".implode(',',$cset)." WHERE ( id='".$_REQUEST['id']."' AND ( '".time()."' BETWEEN starttime AND endtime ) ) LIMIT 1");
	}
	
	//Einzelne
	else {
		$db->query("UPDATE ".PRE."_poll SET a".$_POST['vote']."_c=a".$_POST['vote']."_c+1 WHERE ( id='".$_REQUEST['id']."' AND ( '".time()."' BETWEEN starttime AND endtime ) ) LIMIT 1");
	}
	
	//Block User
	$db->query("INSERT INTO ".PRE."_poll_iplog VALUES ('".$_REQUEST['id']."','".$user->info['userid']."','".ip2integer(get_remoteaddr())."','".time()."')");
	@setcookie($set['main']['cookie_pre'].'_voted['.$_REQUEST['id'].']','1',time()+100*24*3600,'/');
	
	message($apx->lang->get('MSG_VOTE'),mklink('poll.php?id='.$_REQUEST['id'],'poll,'.$_REQUEST['id'].'.html'));
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// IMMER AUFRUFEN

//Headline + Titlebar
if ( $pollinfo['id']==$recent ) {
	headline($apx->lang->get('HEADLINE_RECENT'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	titlebar($apx->lang->get('HEADLINE_RECENT').': '.strip_tags($pollinfo['question']));
}
else {
	headline($apx->lang->get('HEADLINE_ARCHIVE'),mklink('poll.php','poll.html'));
	titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.strip_tags($pollinfo['question']));
}
	

//KOMMENTARE
if ( $_REQUEST['comments'] && $_REQUEST['id'] ) {
	poll_showcomments($_REQUEST['id']);
	require('lib/_end.php');
}



//Verwendete Variablen auslesen
$parse=$apx->tmpl->used_vars('poll');

//////////////////////////////////////////////////////////////////////////////////////////////////////// ERGEBNIS

if ( $_REQUEST['result']
	|| ( $_REQUEST['id']!=$recent && !$set['poll']['archvote'] )
	|| $_POST['vote']
	|| $_COOKIE[$set['main']['cookie_pre'].'_voted'][$pollinfo['id']]=='1'
	|| $ipblock
	|| ($pollinfo['starttime']+$pollinfo['days']*24*3600)<=time()
) {
	
	//Ergebnis
	$result=poll_format_result($pollinfo);
	foreach ( $result AS $element ) {
		++$ri;
		
		$percent=round($element[1]/iif($pollinfo['total'],$pollinfo['total'],1)*100,$set['poll']['percentdigits']);
		
		if ( $set['poll']['barmaxwidth'] ) $width=round($percent*$set['poll']['barmaxwidth']/100);
		else $width=round($percent).'%';
		
		$resdata[$ri]['ANSWER']=$element[0];
		$resdata[$ri]['VOTES']=$element[1];
		$resdata[$ri]['COLOR']=$element[2];
		$resdata[$ri]['PERCENT']=$percent.'%';
		$resdata[$ri]['WIDTH']=$width;
	}
	
	if ( ($pollinfo['starttime']+$pollinfo['days']*24*3600)<=time() ) $set_end=1;
	if ( $_COOKIE[$set['main']['cookie_pre'].'_voted'][$pollinfo['id']]=='1' || $ipblock ) $set_voted=1;
	
	$apx->tmpl->assign('TOTALVOTES',$pollinfo['total']);
	$apx->tmpl->assign('RESULT',$resdata);
	$apx->tmpl->assign('SET_END',$set_end);
	$apx->tmpl->assign('SET_VOTED',$set_voted);
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// OPTIONEN AUFLISTEN
else {
	
	//Optionen
	for ( $i=1; $i<=20; $i++ ) {
		if ( !$pollinfo['a'.$i] ) continue;
		
		if ( $pollinfo['multiple'] ) $box='<input type="checkbox" name="vote['.$i.']" value="1" />'; 
		else $box='<input type="radio" name="vote" value="'.$i.'" />';
		
		$optdata[$i]['ANSWER']=$pollinfo['a'.$i];
		$resdata[$i]['COLOR']=$pollinfo['color'.$i];
		$optdata[$i]['BOX']=$box;
	}
	
	$postto=mklink(
		'poll.php?id='.$pollinfo['id'],
		'poll,'.$pollinfo['id'].'.html'
	);
	
	$apx->tmpl->assign('POSTTO',$postto);
	$apx->tmpl->assign('OPTION',$optdata);
	
	//ERGEBNIS ZEIGEN
	if ( $_REQUEST['id']==$recent ) {
		$apx->tmpl->assign('LINK_RESULT',mklink(
			'poll.php?recent=1&amp;result=1',
			'poll,recent.html?result=1'
		));
	}
	else {
		$apx->tmpl->assign('LINK_RESULT',mklink(
			'poll.php?id='.$_REQUEST['id'].'&amp;result=1',
			'poll,'.$_REQUEST['id'].urlformat($pollinfo['question']).'.html?result=1'
		));
	}
}

//Kommentare
if ( $apx->is_module('comments') && $set['poll']['coms'] && $pollinfo['allowcoms'] ) {
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('poll',$pollinfo['id']);
	$coms->assign_comments();
	if ( $_REQUEST['id']!=$recent && !$set['poll']['archcoms'] ) $apx->tmpl->assign('COMMENT_NOFORM',1);
}


//Tags
if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
	list($tagdata, $tagids) = poll_tags($res['id']);
}


//AUSGABE
$apx->tmpl->assign('ID',$pollinfo['id']);
$apx->tmpl->assign('QUESTION',$pollinfo['question']);
$apx->tmpl->assign_static('META_DESCRIPTION',replace($pollinfo['meta_description']));
$apx->tmpl->assign('STARTTIME',$pollinfo['starttime']);
$apx->tmpl->assign('ENDTIME',$pollinfo['starttime']+$pollinfo['days']*24*3600);

//Tags
$apx->tmpl->assign('TAG_IDS', $tagids);
$apx->tmpl->assign('TAG', $tagdata);
$apx->tmpl->assign('KEYWORDS', $keywords);

$apx->tmpl->parse('poll');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
