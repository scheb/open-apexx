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



//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Online-Zahl berechnen
function user_getonline() {
	global $db, $set;
	static $count;
	if ( !isset($count) ) {
		if ( $set['user']['onlinelist'] ) {
			list($count)=$db->first("SELECT count(ip) FROM ".PRE."_user_online");
		}
		else {
			list($count)=$db->first("SELECT count(userid) FROM ".PRE."_user WHERE lastactive>=".(time()-$set['user']['timeout']*60)." AND pub_invisible=0");
		}
	}
	return $count;
}



//PM-Anzahl auslesen
function user_getpms() {
	global $db, $set;
	static $count;
	
	if ( !isset($count) ) {
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_pms WHERE ( touser='".$user->info['userid']."' AND del_to='0' AND isread='0' )");
	}
	
	return $count;
}



//Online-Anzeige
function user_online($template = 'online') {
	global $set,$apx,$db,$user;
	$tmpl=new tengine;
	$apx->lang->drop('func_online','user');
	$count = user_getonline();
	$tmpl->assign('COUNT',$count);
	$tmpl->parse('functions/'.$template,'user');
}



//PM-Anzeige
function user_newpms($template = 'newpms') {
	global $set,$apx,$db,$user;
	$tmpl=new tengine;
	if ( !$user->info['userid'] ) return;
	$apx->lang->drop('func_newpms','user');
	$count = user_getpms();
	$tmpl->assign('COUNT',$count);
	$tmpl->parse('functions/'.$template,'user');
}



//Gästebucheinträge-Anzeige
function user_newgbs($template = 'newgbentries') {
	global $set,$apx,$db,$user;
	$tmpl=new tengine;
	if ( !$user->info['userid'] ) return;
	
	$apx->lang->drop('func_newgbs','user');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_guestbook WHERE owner='".$user->info['userid']."' AND userid!='".$user->info['userid']."' AND time>'".$user->info['lastonline']."'");
	$tmpl->assign('COUNT',$count);
	$tmpl->parse('functions/'.$template,'user');
}



//Bookmarks anzeigen
function user_onlinerecord($template='onlinerecord') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$apx->lang->drop('func_online','user');
	
	$nowonline = user_getonline();
	
	if ( $nowonline>$set['user']['onlinerecord'] ) {
		$recordtime = time();
		$set['user']['onlinerecord'] = $nowonline;
		$set['user']['onlinerecord_time'] = $recordtime;
		$db->query("UPDATE ".PRE."_config SET value='".$nowonline."' WHERE module='user' AND varname='onlinerecord' LIMIT 1");
		$db->query("UPDATE ".PRE."_config SET value='".$recordtime."' WHERE module='user' AND varname='onlinerecord_time' LIMIT 1");
	}
	
	$tmpl->assign('COUNT',$set['user']['onlinerecord']);
	$tmpl->assign('TIME',$set['user']['onlinerecord_time']);
	$tmpl->parse('functions/'.$template,'user');
}



//Loginbox ausgeben
function user_loginbox($template='loginbox') {
	global $set,$apx,$db,$user;
	$tmpl=new tengine;
	$apx->lang->drop('func_loginbox','user');
	
	if ( !$user->info['userid'] ) $tmpl->assign('POSTTO',mklink('user.php','user.html'));
	$tmpl->parse('functions/'.$template,'user');
}



//User-Status
function user_status($template='userstatus') {
	global $set,$apx,$db,$user;
	$tmpl=new tengine;
	$apx->lang->drop('func_status','user');
	
	if ( count($set['main']['smilies']) ) {
		foreach ( $set['main']['smilies'] AS $res ) {
			++$i;
			$smiledata[$i]['CODE']=$res['code'];
			$smiledata[$i]['INSERTCODE']=addslashes($res['code']);
			$smiledata[$i]['IMAGE']=$res['file'];
			$smiledata[$i]['DESCRIPTION']=$res['description'];
		}
	}
	
	$tmpl->assign('POSTTO', 'user.php?action=setstatus');
	$tmpl->assign('SMILEY', $smiledata);
	$tmpl->assign('STATUS', compatible_hsc($user->info['status']));
	$tmpl->assign('STATUS_SMILEY', compatible_hsc($user->info['status_smiley']));
	
	$tmpl->parse('functions/'.$template,'user');
}



//Online-Liste
function user_onlinelist($template='onlinelist') {
	global $set,$apx,$db,$user;
	$apx->lang->drop('func_onlinelist','user');
	
	$data=$db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE ( lastactive>=".(time()-$set['user']['timeout']*60)." AND pub_invisible=0 ) ORDER BY username ASC");
	user_print($data, 'functions/'.$template);
}



//Neue User
function user_new($count=5,$template='newuser') {
	global $set,$apx,$db,$user;
	$count=(int)$count;
	if ( $count<1 ) $count=1;
	$apx->lang->drop('func_newuser','user');
	
	$data=$db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE reg_key='' ORDER BY reg_time DESC LIMIT ".$count);
	user_print($data, 'functions/'.$template, 'NEW');
}



//Neue User
function user_random($count=5,$template='randomuser') {
	global $set,$apx,$db,$user;
	$count=(int)$count;
	if ( $count<1 ) $count=1;
	$apx->lang->drop('func_newuser','user');
	
	$data=$db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE reg_key='' ORDER BY RAND() LIMIT ".$count);
	user_print($data, 'functions/'.$template);
}



//Geburtstage auflisten
function user_birthdays($template='birthdays') {
	global $set,$apx,$db,$user;
	$apx->lang->drop('func_birthdays','user');
	
	$data=$db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE ( birthday='".date('d-m',time()-TIMEDIFF)."' OR birthday LIKE '".date('d-m-',time()-TIMEDIFF)."%' ) ORDER BY username ASC");
	user_print($data, 'functions/'.$template);
}



//Geburtstage morgen auflisten
function user_birthdays_tomorrow($template='birthdays_tomorrow') {
	global $set,$apx,$db,$user;
	$apx->lang->drop('func_birthdays_tomorrow','user');
	
	$data=$db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE ( birthday LIKE '".date('d-m-',time()+24*3600-TIMEDIFF)."%' ) ORDER BY username ASC");
	user_print($data, 'functions/'.$template);
}



//Geburtstage nächsten Tage auflisten
function user_birthdays_nextdays($days = 5, $template='birthdays_nextdays') {
	global $set,$apx,$db,$user;
	$apx->lang->drop('func_birthdays_nextdays','user');
	
	$today = date('j', time()-TIMEDIFF);
	$daylist = array();
	for ( $i=0; $i<$days; $i++ ) {
		$timestamp = mktime(0, 0, 0, date('n', time()-TIMEDIFF), $today+1+$i, date('Y', time()-TIMEDIFF))+TIMEDIFF;
		$daylist[] = "birthday LIKE '".date('d-m', $timestamp-TIMEDIFF)."%'";
	}
	
	$data=$db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE ( ".implode(' OR ', $daylist)." ) ORDER BY username ASC");
	user_print($data, 'functions/'.$template);
}



//Buddies auflisten
function user_buddylist($template='buddylist') {
	global $set,$apx,$db,$user;
	$apx->lang->drop('func_buddylist','user');
	
	$buddies = $user->get_buddies();
	if ( count($buddies) ) {
		$data = $db->fetch("SELECT userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE userid IN (".implode(',',$buddies).") ORDER BY username ASC");
	}
	user_print($data, 'functions/'.$template, 'BUDDY', true);
}



//Ausgabe Liste von Usern
function user_print($data, $template, $varname='USER', $buddylist = false, $templatemodule = 'user') {
	global $set,$apx,$db,$user;
	
	$tmpl=new tengine;
	$parse = $tmpl->used_vars($template, $templatemodule);
	
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
			if ( in_array($varname.'.ISBUDDY', $parse) ) {
				$tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);
			}
			
			//Custom-Felder
			for ( $ii=1; $ii<=10; $ii++ ) {
				$tabledata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii-1)];
				$tabledata[$i]['CUSTOM'.$ii] = compatible_hsc($res['custom'.$ii]);
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
				
				if ( in_array($varname.'.LINK_BUDDY', $parse) && !$user->is_buddy($res['userid']) ) {
					$tabledata[$i]['LINK_BUDDY']=mklink(
						'user.php?action=addbuddy&amp;id='.$res['userid'],
						'user,addbuddy,'.$res['userid'].'.html'
					);
				}
			}
			
			//Nur Buddy-Liste
			if ( $buddylist ) {
				$tabledata[$i]['LINK_DELBUDDY']=mklink(
					'user.php?action=delbuddy&amp;id='.$res['userid'],
					'user,delbuddy,'.$res['userid'].'.html'
				);
			}
		}
	}
	
	$tmpl->assign($varname,$tabledata);
	$tmpl->parse($template,$templatemodule);
}



//Profillink erzeugen
function user_profile($id=0,$username=false) {
	global $set,$apx,$db,$user;
	static $cache;
	
	$id=(int)$id;
	if ( !$id ) return;
	
	//Benutzername auslesen, wenn SEO-URLs aktiviert
	if ( $set['main']['staticsites'] ) {
		if ( isset($cache[$id]) ) $username=$cache[$id];
		if ( $username===false ) {
			list($username)=$user->get_info($id,'username');
		}
		
		$cache[$id]=$username; //Speichern
	}
	
	echo mklink(
		'user.php?action=profile&amp;id='.$id,
		'user,profile,'.$id.urlformat($username).'.html'
	);
}



//Bookmarklink erzeugen
function user_bookmarklink() {
	global $set,$apx,$db,$user;
	$escurl = urlencode(HTTP_HOST.$_SERVER['REQUEST_URI']);
	$link = mklink(
		'user.php?action=addbookmark&amp;url='.$escurl,
		'user,addbookmark.html?url='.$escurl
	);
	echo $link;
}



//Bookmarks anzeigen
function user_bookmarks($template='bookmarks') {
	global $set,$apx,$db,$user;
	$tmpl=new tengine;
	$apx->lang->drop('func_bookmarks','user');
	
	$tabledata = array();
	$data = $db->fetch("SELECT * FROM ".PRE."_user_bookmarks WHERE userid='".$user->info['userid']."' ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Bookmark löschen
			$dellink = mklink(
				'user.php?action=delbookmark&amp;id='.$res['id'],
				'user,delbookmark,'.$res['id'].'.html'
			);
			
			$tabledata[$i]['ID'] = $res['id'];
			$tabledata[$i]['TITLE'] = $res['title'];
			$tabledata[$i]['LINK'] = $res['url'];
			$tabledata[$i]['TIME'] = $res['addtime'];
			$tabledata[$i]['LINK_DELBOOKMARK'] = $dellink;
		}
	}
	
	$tmpl->assign('BOOKMARK',$tabledata);
	$tmpl->parse('functions/'.$template,'user');
}



//Neuste Galerien
function user_gallery_last($count=5,$start=0,$friendsonly=false,$userid=0,$template='gallery_last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	
	//Nach Freunden filtern
	$friendfilter='';
	if ( $friendsonly ) {
		$friends = $user->get_buddies();
		$friends[] = -1;
		$friendfilter = " WHERE userid IN (".implode(',',$friends).") ";
	}
	
	//Nach Benutzer filtern
	$userfilter = '';
	if ( $userid ) {
		$userfilter = " AND owner='".$userid."'";
	}
	
	$data=$db->fetch("SELECT * FROM ".PRE."_user_gallery WHERE password='' ".$userfilter.$friendfilter." ORDER BY addtime DESC LIMIT ".iif($start,$start.',').$count);
	user_gallery_print($data,'functions/'.$template);
}



//Aktualisierte Galerien
function user_gallery_updated($count=5,$start=0,$friendsonly=false,$userid=0,$template='gallery_updated') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$userid=(int)$userid;
	
	//Nach Freunden filtern
	$friendfilter='';
	if ( $friendsonly ) {
		$friends = $user->get_buddies();
		$friends[] = -1;
		$friendfilter = " AND owner IN (".implode(',',$friends).") ";
	}
	
	//Nach Benutzer filtern
	$userfilter = '';
	if ( $userid ) {
		$userfilter = " AND owner='".$userid."'";
	}
	
	$data=$db->fetch("SELECT * FROM ".PRE."_user_gallery WHERE password='' ".$userfilter.$friendfilter." ORDER BY lastupdate DESC LIMIT ".iif($start,$start.',').$count);
	user_gallery_print($data,'functions/'.$template);
}



//AUSGABE: Galerie
function user_gallery_print($data,$template) {
	global $set,$db,$apx,$user;	
	$tmpl=new tengine;
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'user');
	
	if ( count($data) ) {
		
		//Benutzer-Infos auslesen
		$userdata = array();
		if ( in_template(array('GALLERY.USERNAME','GALLERY.REALNAME','GALLERY.AVATAR','GALLERY.AVATER_TITLE'),$parse) ) {
			$userids = get_ids($data,'owner');
			$userdata = $user->get_info_multi($userids,'username,realname,avatar,avatar_title');
		}
		
		//Galerien auflisten
		$tabledata = array();
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'user.php?action=gallery&amp;id='.$res['owner'].'&amp;galid='.$res['id'],
				'user,gallery,'.$res['owner'].','.$res['id'].',0.html'
			);
			
			//Enthaltene Bilder
			if ( in_array('GALLERY.COUNT',$parse) ) {
				list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_pictures WHERE galid='".$res['id']."'");
			}
			
			//Vorschau-Bild
			$preview = '';
			if ( in_array('GALLERY.PREVIEW',$parse) && ( !$res['password'] || $user->info['userid']==$res['owner'] || $res['password']==$_COOKIE['usergallery_pwd_'.$res['id']] ) ) {
				list($preview) = $db->first("SELECT thumbnail FROM ".PRE."_user_pictures WHERE galid='".$res['id']."' ORDER BY RAND() LIMIT 1");
			}
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
			}
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=$res['title'];
			$tabledata[$i]['DESCRIPTION']=$res['description'];
			$tabledata[$i]['TIME']=$res['addtime'];
			$tabledata[$i]['UPDATETIME']=$res['lastupdate'];
			$tabledata[$i]['LINK']=$link;
			$tabledata[$i]['COUNT']=$count;
			$tabledata[$i]['PREVIEW'] = iif($preview,HTTPDIR.getpath('uploads').$preview);
			
			//Userinfo
			$userinfo = $userdata[$res['owner']];
			$tabledata[$i]['USERID'] = $res['owner'];
			$tabledata[$i]['USERNAME'] = replace($userinfo['username']);
			$tabledata[$i]['REALNAME'] = replace($userinfo['realname']);
			$tabledata[$i]['AVATAR'] = $user->mkavatar($userinfo);
			$tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($userinfo);
			
			//Kommentare
			if ( $apx->is_module('comments') && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('usergallery',$res['id']);
				else $coms->mid=$res['id'];
				
				$link = mklink(
					'user.php?action=gallery&amp;id='.$_REQUEST['id'].'&amp;galid='.$res['id'],
					'user,gallery,'.$_REQUEST['id'].','.$res['id'].',0.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('GALLERY.COMMENT_LAST_USERID','GALLERY.COMMENT_LAST_NAME','GALLERY.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
	}
	
	$tmpl->assign('GALLERY',$tabledata);
	$tmpl->parse($template,'user');
}



//Neuste Bilder
function user_gallery_lastpics($count=5,$start=0,$galid=false,$friendsonly=false,$userid=0,$template='gallery_lastpics') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$galid=(int)$galid;
	$userid=(int)$userid;
	
	//Nach Freunden filtern
	$friendfilter='';
	if ( $friendsonly ) {
		$friends = $user->get_buddies();
		$friends[] = -1;
		$friendfilter = " AND g.owner IN (".implode(',',$friends).") ";
	}
	
	//Nach Benutzer filtern
	$userfilter = '';
	if ( $userid ) {
		$userfilter = " AND g.owner='".$userid."'";
	}
	
	$data=$db->fetch("SELECT g.owner,g.title,g.description,g.password,g.addtime AS galaddtime,g.lastupdate,p.* FROM ".PRE."_user_pictures AS p LEFT JOIN ".PRE."_user_gallery AS g ON p.galid=g.id WHERE g.password='' ".$userfilter.$friendfilter." ".iif($galid," AND p.galid='".$galid."'")." ORDER BY p.addtime DESC LIMIT ".iif($start,$start.',').$count);
	user_gallery_printpic($data,'functions/'.$template);
}



//AUSGABE Toplisten
function user_gallery_printpic($data,$template) {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'user');
	
	if ( count($data) ) {
		
		//Benutzer-Infos auslesen
		$userdata = array();
		if ( in_template(array('PICTURE.USERNAME','PICTURE.REALNAME','PICTURE.AVATAR','PICTURE.AVATER_TITLE'),$parse) ) {
			$userids = get_ids($data,'owner');
			$userdata = $user->get_info_multi($userids,'username,realname,avatar,avatar_title');
		}
		
		//Bilder auflisten
		$tabledata = array();
		foreach ( $data AS $res ) {
			++$i;
			
			//GALERIE
			$gallink = mklink(
				'user.php?action=gallery&amp;id='.$res['owner'].'&amp;galid='.$res['galid'],
				'user,gallery,'.$res['owner'].','.$res['galid'].',1.html'
			);
			
			$tabledata[$i]['GALLERY_ID']=$res['galid'];
			$tabledata[$i]['GALLERY_TITLE']=$res['title'];
			$tabledata[$i]['GALLERY_DESCRIPTION']=$res['description'];
			$tabledata[$i]['GALLERY_TIME']=$res['galaddtime'];
			$tabledata[$i]['GALLERY_LINK']=$gallink;
			$tabledata[$i]['GALLERY_UPDATETIME']=$res['lastupdate'];
			
			//Enthaltene Bilder
			if ( in_array('PICTURE.GALLERY_COUNT',$parse) ) {
				list($galcount)=$db->first("SELECT count(id) FROM ".PRE."_user_pictures WHERE galid='".$res['galid']."'");
				$tabledata[$i]['GALLERY_COUNT']=$galcount;
			}
			
			//BILD
			$size=getimagesize(BASEDIR.getpath('uploads').$res['picture']);
			$tabledata[$i]['LINK'] = "javascript:popuppic('misc.php?action=picture&amp;pic=".$res['picture']."','".$size[0]."','".$size[1]."');";
			$tabledata[$i]['CAPTION']=$res['caption'];
			$tabledata[$i]['IMAGE']=getpath('uploads').$res['thumbnail'];
			$tabledata[$i]['FULLSIZE']=getpath('uploads').$res['picture'];
			$tabledata[$i]['TIME']=$res['addtime'];
			
			//Userinfo
			$userinfo = $userdata[$res['owner']];
			$tabledata[$i]['USERID'] = $res['owner'];
			$tabledata[$i]['USERNAME'] = replace($userinfo['username']);
			$tabledata[$i]['REALNAME'] = replace($userinfo['realname']);
			$tabledata[$i]['AVATAR'] = $user->mkavatar($userinfo);
			$tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($userinfo);
		}
	}
	
	$tmpl->assign('PICTURE',$tabledata);
	$tmpl->parse($template,'user');
}



//POTM
function user_gallery_potm($galid=0,$friendsonly=false,$userid=0,$template='gallery_potm') {
	global $set,$db,$apx,$user;
	$galid=(int)$galid;
	$userid=(int)$userid;
	
	//Nach Freunden filtern
	$friendfilter='';
	if ( $friendsonly ) {
		$friends = $user->get_buddies();
		$friends[] = -1;
		$friendfilter = " AND g.owner IN (".implode(',',$friends).") ";
	}
	
	//Nach Benutzer filtern
	$userfilter = '';
	if ( $userid ) {
		$userfilter = " AND g.owner='".$userid."'";
	}
	
	//Zufallsauswahl
	$res=$db->first("SELECT g.owner,g.title,g.description,g.password,g.addtime AS galaddtime,g.lastupdate,p.* FROM ".PRE."_user_pictures AS p LEFT JOIN ".PRE."_user_gallery AS g ON p.galid=g.id WHERE g.password='' ".$userfilter.$friendfilter." ".iif($galid," AND p.galid='".$galid."'")." ORDER BY RAND() LIMIT 1");
	user_gallery_printsingle($res,'functions/'.$template);
}



//AUSGABE POTW & POTM
function user_gallery_printsingle($res,$template) {
	global $set,$db,$apx,$user;
	if ( !$res['id'] ) return;
	$tmpl=new tengine;
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'user');
	
	//GALERIE
	$gallink = mklink(
		'user.php?action=gallery&amp;id='.$res['owner'].'&amp;galid='.$res['galid'],
		'user,gallery,'.$res['galid'].',1.html'
	);
	
	$tmpl->assign('GALLERY_ID',$res['galid']);
	$tmpl->assign('GALLERY_TITLE',$res['title']);
	$tmpl->assign('GALLERY_DESCRIPTION',$res['description']);
	$tmpl->assign('GALLERY_TIME',$res['galaddtime']);
	$tmpl->assign('GALLERY_LINK',$gallink);
	$tmpl->assign('GALLERY_LASTUPDATE',$res['lastupdate']);
	
	//Enthaltene Bilder
	if ( in_array('GALLERY_COUNT',$parse) ) {
		list($galcount)=$db->first("SELECT count(id) FROM ".PRE."_user_pictures WHERE galid='".$res['galid']."'");
		$tmpl->assign('GALLERY_COUNT',$galcount);
	}
	
	//BILD
	$size=getimagesize(BASEDIR.getpath('uploads').$res['picture']);
	$tmpl->assign('LINK',"javascript:popuppic('misc.php?action=picture&amp;pic=".$res['picture']."','".$size[0]."','".$size[1]."');");
	$tmpl->assign('CAPTION',$res['caption']);
	$tmpl->assign('IMAGE',getpath('uploads').$res['thumbnail']);
	$tmpl->assign('FULLSIZE',getpath('uploads').$res['picture']);
	$tmpl->assign('TIME',$res['addtime']);
	
	//Benutzer-Infos auslesen
	$tmpl->assign('USERID',$res['owner']);
	if ( in_template(array('USERNAME','REALNAME','AVATAR','AVATER_TITLE'),$parse) ) {
		$userinfo = $user->get_info($res['owner'],'username,realname,avatar,avatar_title');
		$tmpl->assign('USERNAME',replace($userinfo['username']));
		$tmpl->assign('REALNAME',replace($userinfo['realname']));
		$tmpl->assign('AVATAR',$user->mkavatar($userinfo));
		$tmpl->assign('AVATAR_TITLE',$user->mkavtitle($userinfo));
	}
	
	$tmpl->parse($template,'user');
}



//Neuste User-Blogs
function user_blogs_last($count=5,$start=0,$friendsonly=false,$userid=0,$template='lastblogs') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$count=(int)$count;
	$start=(int)$start;
	$userid=(int)$userid;
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('functions/'.$template,'user');
	
	//Nach Freunde filtern
	$friendfilter='';
	if ( $friendsonly ) {
		$friends = $user->get_buddies();
		$friends[] = -1;
		$friendfilter = " AND userid IN (".implode(',',$friends).") ";
	}
	
	//Nach Benutzer filtern
	$userfilter = '';
	if ( $userid ) {
		$userfilter = " AND userid='".$userid."'";
	}
	
	$data = $db->fetch("SELECT * FROM ".PRE."_user_blog WHERE 1 ".$userfilter.$friendfilter." ORDER BY time DESC LIMIT ".iif($start,$start.',').$count);
	if ( count($data) ) {
		
		//Benutzer-Infos auslesen
		$userdata = array();
		if ( in_template(array('BLOG.USERNAME','BLOG.REALNAME','BLOG.AVATAR','BLOG.AVATER_TITLE'),$parse) ) {
			$userids = get_ids($data,'userid');
			$userdata = $user->get_info_multi($userids,'username,realname,avatar,avatar_title');
		}
		
		//Blogs auflisten
		$tabledata = array();
		foreach ( $data AS $res ) {
			++$i;
			
			$link = mklink(
				'user.php?action=blog&amp;id='.$res['userid'].'&amp;blogid='.$res['id'],
				'user,blog,'.$res['userid'].',id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Text
			$text = '';
			if ( in_array('BLOG.TEXT', $parse) ) {
				$text = $res['text'];
				$text = badwords($text);
				$text = replace($text,1);
				$text = dbsmilies($text);
				$text = dbcodes($text); 
			}
			
			$tabledata[$i]['ID'] = $res['id'];
			$tabledata[$i]['TITLE'] = replace($res['title']);
			$tabledata[$i]['TEXT'] = $res['text'];
			$tabledata[$i]['LINK'] = $link;
			$tabledata[$i]['TIME'] = $res['time'];
			
			//Userinfo
			$userinfo = $userdata[$res['userid']];
			$tabledata[$i]['USERID'] = $res['userid'];
			$tabledata[$i]['USERNAME'] = replace($userinfo['username']);
			$tabledata[$i]['REALNAME'] = replace($userinfo['realname']);
			$tabledata[$i]['AVATAR'] = $user->mkavatar($userinfo);
			$tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($userinfo);
			
			//Kommentare
			if ( $apx->is_module('comments') && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('userblog',$res['id']);
				else $coms->mid=$res['id'];
				
				$link = mklink(
					'user.php?action=blog&amp;id='.$res['userid'].'&amp;blogid='.$res['id'],
					'user,blog,'.$res['userid'].',id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('BLOG.COMMENT_LAST_USERID','BLOG.COMMENT_LAST_NAME','BLOG.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
		}
	}
	$tmpl->assign('BLOG',$tabledata);
	//Template ausgeben
	$tmpl->parse('functions/'.$template,'user');
}



//Neuste User-Blogs
function user_info($userid=0,$template='information') {
	global $set,$db,$apx,$user;
	$userid = (int)$userid;
	if ( !$userid ) return;
	$tmpl=new tengine;
	
	$apx->lang->drop('profile', 'user');
	
	//Verwendete Variablen auslesen
	$parse=$tmpl->used_vars('functions/'.$template,'user');
	
	$res=$db->first("SELECT * FROM ".PRE."_user WHERE userid='".$userid."' LIMIT 1");
	$userid = $res['userid'];
	if ( !$res['userid'] ) return;
	list($groupname)=$db->first("SELECT name FROM ".PRE."_user_groups WHERE groupid='".$res['groupid']."' LIMIT 1");
	
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
	
	$tmpl->assign('USERID',$res['userid']);
	$tmpl->assign('USERNAME',replace($res['username']));
	$tmpl->assign('GROUP',replace($groupname));
	$tmpl->assign('REGDATE',$res['reg_time']);
	$tmpl->assign('REGDAYS',floor((time()-$res['reg_time'])/(24*3600)));
	$tmpl->assign('LASTACTIVE',(int)$res['lastactive']);
	$tmpl->assign('IS_ONLINE',iif(!$res['pub_invisible'] && ($res['lastactive']+$set['user']['timeout']*60)>=time(),1,0));
	$tmpl->assign('EMAIL',replace($res['email']));
	$tmpl->assign('EMAIL_ENCRYPTED',replace(cryptMail($res['email'])));
	$tmpl->assign('HIDEMAIL',$res['pub_hidemail']);
	$tmpl->assign('HOMEPAGE',replace($res['homepage']));
	$tmpl->assign('ICQ',replace($res['icq']));
	$tmpl->assign('AIM',replace($res['aim']));
	$tmpl->assign('YIM',replace($res['yim']));
	$tmpl->assign('MSN',replace($res['msn']));
	$tmpl->assign('SKYPE',replace($res['skype']));
	$tmpl->assign('REALNAME',replace($res['realname']));
	$tmpl->assign('CITY',replace($res['city']));
	$tmpl->assign('PLZ',replace($res['plz']));
	$tmpl->assign('COUNTRY',replace($res['country']));
	$tmpl->assign('INTERESTS',replace($res['interests']));
	$tmpl->assign('WORK',replace($res['work']));
	$tmpl->assign('GENDER',(int)$res['gender']);
	$tmpl->assign('BIRTHDAY',$birthday);
	$tmpl->assign('AGE',$age);
	$tmpl->assign('SIGNATURE',$user->mksig($res,1));
	$tmpl->assign('AVATAR',$user->mkavatar($res));
	$tmpl->assign('AVATAR_TITLE',$user->mkavtitle($res));
	
	//Custom-Felder
	for ( $i=1; $i<=10; $i++ ) {
		$tmpl->assign('CUSTOM'.$i.'_NAME',replace($set['user']['cusfield_names'][($i-1)]));
		$tmpl->assign('CUSTOM'.$i,replace($res['custom'.$i]));
	}
	
	//Forum-Variablen
	if ( $apx->is_module('forum') ) {
		if ( $res['forum_lastactive']==0 ) $res['forum_lastactive']=$res['lastactive'];
		$tmpl->assign('FORUM_LASTACTIVE',(int)$res['forum_lastactive']);
		$tmpl->assign('FORUM_POSTS',(int)$res['forum_posts']);
		$tmpl->assign('FORUM_FINDPOSTS',HTTPDIR.$set['forum']['directory'].'/search.php?send=1&author='.urlencode($res['username']));
	}
	
	//Kommentare
	if ( $apx->is_module('comments') && in_array('COMMENTS', $parse) ) {
		require_once(BASEDIR.getmodulepath('comments').'functions.php');
		$tmpl->assign('COMMENTS',comments_count($res['userid']));
	}
	
	//Interaktionen
	$link_buddy=iif($user->info['userid'] && !$user->is_buddy($res['userid']),mklink(
		'user.php?action=addbuddy&amp;id='.$res['userid'],
		'user,addbuddy,'.$res['userid'].'.html'
	));
	$link_sendpm=iif($user->info['userid'],mklink(
		'user.php?action=newpm&amp;touser='.$res['userid'],
		'user,newpm,'.$res['userid'].'.html'
	));
	$link_sendmail=iif($user->info['userid'],mklink(
		'user.php?action=newmail&amp;touser='.$res['userid'],
		'user,newmail,'.$res['userid'].'.html'
	));
	$tmpl->assign('LINK_BUDDY',$link_buddy);
	$tmpl->assign('LINK_SENDPM',$link_sendpm);
	$tmpl->assign('LINK_SENDEMAIL',$link_sendmail);
	
	//Links zu den Profil-Funktionen
	require_once(dirname(__FILE__).'/functions.php');
	user_assign_profile_links($tmpl, $res);
	
	//Buddyliste
	$tabledata = array();
	if ( $res['pub_showbuddies'] && in_array('BUDDY', $parse) ) {
		$data = $db->fetch("SELECT friendid FROM ".PRE."_user_friends WHERE userid='".$res['userid']."'");
		$buddies = get_ids($data,'friendid');
		if ( count($buddies) ) {
			$data = $db->fetch("SELECT userid,username,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM ".PRE."_user WHERE userid IN (".implode(',',$buddies).") ORDER BY username ASC");
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
					
					//Custom-Felder
					for ( $ii=1; $ii<=10; $ii++ ) {
						$tabledata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii-1)];
						$tabledata[$i]['CUSTOM'.$ii] = compatible_hsc($res['custom'.$ii]);
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
					}
					
					//Nur Buddy-Liste
					if ( $buddylist ) {
						$tabledata[$i]['LINK_DELBUDDY']=mklink(
							'user.php?action=delbuddy&amp;id='.$res['userid'],
							'user,delbuddy,'.$res['userid'].'.html'
						);
					}
				}
			}
		}
	}
	$tmpl->assign('BUDDY',$tabledata);
	
	//Template ausgeben
	$tmpl->parse('functions/'.$template,'user');
}



//Statistik anzeigen
function user_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'user');
	
	$apx->lang->drop('func_stats', 'user');
	
	//User
	if ( in_array('COUNT_USERS', $parse) ) {
		list($count) = $db->first("
			SELECT count(userid) FROM ".PRE."_user
			WHERE active='1' ".iif($set['user']['listactiveonly'], " AND reg_key='' ")."
		");
		$tmpl->assign('COUNT_USERS', $count);
	}
	if ( in_array('COUNT_USERS_MALE', $parse) ) {
		list($count) = $db->first("
			SELECT count(userid) FROM ".PRE."_user
			WHERE active='1' AND gender=1 ".iif($set['user']['listactiveonly'], " AND reg_key='' ")."
		");
		$tmpl->assign('COUNT_USERS_MALE', $count);
	}
	if ( in_array('COUNT_USERS_FEMALE', $parse) ) {
		list($count) = $db->first("
			SELECT count(userid) FROM ".PRE."_user
			WHERE active='1' AND gender=2 ".iif($set['user']['listactiveonly'], " AND reg_key='' ")."
		");
		$tmpl->assign('COUNT_USERS_FEMALE', $count);
	}
	
	//Blogs
	if ( in_array('COUNT_BLOGS', $parse) ) {
		list($count) = $db->first("
			SELECT count(id) FROM ".PRE."_user_blog
		");
		$tmpl->assign('COUNT_BLOGS', $count);
	}
	
	//Galerien
	if ( in_array('COUNT_GALLERIES', $parse) ) {
		list($count) = $db->first("
			SELECT count(id) FROM ".PRE."_user_gallery
		");
		$tmpl->assign('COUNT_GALLERIES', $count);
	}
	if ( in_array('COUNT_PICTURES', $parse) ) {
		list($count) = $db->first("
			SELECT count(id) FROM ".PRE."_user_pictures
		");
		$tmpl->assign('COUNT_PICTURES', $count);
	}
	
	
	$tmpl->parse('functions/'.$template,'user');
}


?>