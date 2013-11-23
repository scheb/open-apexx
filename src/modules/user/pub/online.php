<?php

$apx->lang->drop('onlinelist');
headline($apx->lang->get('HEADLINE_ONLINELIST'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_ONLINELIST'));

//Verwendete Variablen
$parse = $apx->tmpl->used_vars('online');

list($count['users'])=$db->first("SELECT count(*) FROM ".PRE."_user WHERE lastactive>=".(time()-$set['user']['timeout']*60));
list($count['inv'])=$db->first("SELECT count(*) FROM ".PRE."_user WHERE lastactive>=".(time()-$set['user']['timeout']*60)." AND pub_invisible=1");
if ( $set['user']['onlinelist'] ) {
	list($count['guests'])=$db->first("SELECT count(*) FROM ".PRE."_user_online WHERE userid=0");
}
else {
	$count['guests'] = 0;
}
$count['total']=$count['users']+$count['guests'];

$data=$db->fetch("SELECT b.userid,b.username,b.email,b.pub_hidemail,b.groupid,b.realname,b.gender,b.city,b.plz,b.country,b.city,b.lastactive,b.pub_invisible,b.avatar,b.avatar_title FROM ".PRE."_user AS b WHERE ( b.lastactive>=".(time()-$set['user']['timeout']*60)." AND b.pub_invisible='0' ) ORDER BY b.username ASC");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		
		$age = 0;
		if ( $res['birthday'] ) {
			$bd=explode('-',$res['birthday']);
			$birthday=intval($bd[0]).'. '.getcalmonth($bd[1]).iif($bd[2],' '.$bd[2]);
			if ( $bd[2] ) {
				$age = date('Y')-$bd[2];
				if ( intval(sprintf('%02d%02d', $bd[1], $bd[0]))>intval(date('md')) ) {
					$age -= 1;
				}
			}
		}
		
		$tabledata[$i]['ID'] = $res['userid'];
		$tabledata[$i]['USERID'] = $res['userid'];
		$tabledata[$i]['NAME'] = replace($res['username']);
		$tabledata[$i]['USERNAME'] = replace($res['username']);
		$tabledata[$i]['GROUPID'] = $res['groupid'];
		$tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'],$res['email']));
		$tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'],cryptMail($res['email'])));
		$tabledata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive']+$set['user']['timeout']*60)>=time(),1,0);
		$tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];
		$tabledata[$i]['REALNAME'] = replace($res['realname']);
		$tabledata[$i]['GENDER'] = $res['gender'];
		$tabledata[$i]['CITY'] = replace($res['city']);
		$tabledata[$i]['PLZ'] = replace($res['plz']);
		$tabledata[$i]['COUNTRY'] = $res['country'];
		$tabledata[$i]['REGTIME']=$res['reg_time'];
		$tabledata[$i]['REGDAYS']=floor((time()-$res['reg_time'])/(24*3600));
		$tabledata[$i]['LASTACTIVE'] = $res['lastactive'];
		$tabledata[$i]['AVATAR'] = $user->mkavatar($res);
		$tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);
		$tabledata[$i]['BIRTHDAY'] = $birthday;
		$tabledata[$i]['AGE'] = $age;
		if ( in_array('USER.ISBUDDY', $parse) ) {
			$tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);
		}
		
		//Interaktions-Links
		if ( $user->info['userid'] ) {
			$tabledata[$i]['LINK_SENDPM']=mklink(
				'user.php?action=newpm&amp;touser='.$res['userid'],
				'user,newpm,'.$res['userid'].'.html'
			);
			
			$tabledata[$i]['LINK_SENDEMAIL']=mklink(
				'user.php?action=newmail&amp;touser='.$res['userid'],
				'user,newmail,'.$res['userid'].'.html'
			);
			
			if ( ( in_array('USER.LINK_BUDDY', $parse) || in_array('USER.LINK_ADDBUDDY', $parse) ) && $userid!=$user->info['userid'] && !$user->is_buddy($res['userid']) ) {
				$tabledata[$i]['LINK_BUDDY']=mklink(
					'user.php?action=addbuddy&amp;id='.$res['userid'],
					'user,addbuddy,'.$res['userid'].'.html'
				);
				$tabledata[$i]['LINK_ADDBUDDY'] = $tabledata[$i]['LINK_BUDDY'];
			}
		}
	}
}

$apx->tmpl->assign('COUNT_TOTAL',$count['total']);
$apx->tmpl->assign('COUNT_USERS',$count['users']);
$apx->tmpl->assign('COUNT_GUESTS',$count['guests']);
$apx->tmpl->assign('COUNT_INV',$count['inv']);
$apx->tmpl->assign('USER',$tabledata);

$apx->tmpl->parse('online');

?>