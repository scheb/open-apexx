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



//Diese Klasse dient zur Initialisierung des Benutzersystems!
class user {

var $info=array();

	//////////////////////////////////////////////////////////////////////////////////////////// STARTUP
	function init() {
		global $set,$db,$apx;
		
		if ( $_COOKIE[$set['main']['cookie_pre'].'_userid'] && $_COOKIE[$set['main']['cookie_pre'].'_password'] ) {
			$this->info=$db->first("SELECT * FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING (groupid) WHERE ( userid='".intval($_COOKIE[$set['main']['cookie_pre'].'_userid'])."' AND password='".addslashes($_COOKIE[$set['main']['cookie_pre'].'_password'])."' ) LIMIT 1",1);
			
			if ( ( !$this->info['userid'] || !$this->info['active'] || $this->info['reg_key'] ) && $apx->module()!='user' && $_REQUEST['action']!='logout' ) {
				$link=str_replace('&amp;', '&', mklink(
					'user.php?action=logout',
					'user,logout.html'
				));
				header("HTTP/1.1 301 Moved Permanently");
				header('location:'.$link);
				exit;
			}
			
			$this->update_lastonline();
		}
		else {
			$this->info=$db->first("SELECT * FROM ".PRE."_user_groups WHERE groupid='3' LIMIT 1",1);
		}
		
		$apx->lang->langid($this->info['pub_lang']);
		if ( $set['user']['onlinelist'] ) {
			$this->update_onlinelist();
		}
	}
	
	
	
	//Buddie-Liste holen
	function get_buddies() {
		global $db;
		if ( !$this->info['userid'] ) return array();
		if ( isset($this->info['friends']) ) return $this->info['friends'];
		$data = $db->fetch("SELECT friendid FROM ".PRE."_user_friends WHERE userid='".$this->info['userid']."'");
		$this->info['friends'] = get_ids($data,'friendid');
		return $this->info['friends'];
	}
	
	
	
	//Zuletzt online aktualisieren
	function update_lastonline() {
		global $db,$set;
		
		if ( ($this->info['lastactive']+$set['user']['timeout']*60)<time() ) {
			$db->query("UPDATE ".PRE."_user SET lastonline=lastactive,lastactive='".time()."' WHERE userid='".$this->info['userid']."' LIMIT 1");
			$this->info['lastonline']=$this->info['lastactive'];
			$this->info['lastactive']=time();
		}
		else {
			$db->query("UPDATE ".PRE."_user SET lastactive='".time()."' WHERE userid='".$this->info['userid']."' LIMIT 1");
			$this->info['lastactive']=time();
		}
	}
	
	
	
	//Onlineliste
	function update_onlinelist() {
		global $db,$set;
		$db->query("DELETE FROM ".PRE."_user_online WHERE ( time<'".(time()-$set['user']['timeout']*60)."' OR ip='".ip2integer(get_remoteaddr())."' ".iif($this->info['userid']," OR userid='".$this->info['userid']."' ").")");
		$db->query("INSERT IGNORE INTO ".PRE."_user_online VALUES ('".$this->info['userid']."','".ip2integer(get_remoteaddr())."','".time()."','".$this->info['pub_invisible']."','".addslashes($_SERVER['REQUEST_URI'])."')");
	}
	
	
	
	//Hat der User Admin-Rechte?
	function is_team_member($userid=false) {
		global $db;
		if ( $userid===false ) {
			if ( $this->info['gtype']=='admin' || $this->info['gtype']=='indiv' ) return true;
			else return false;
		}
		
		$userid=(int)$userid;
		if ( !$userid ) return false;
		
		$res=$db->first("SELECT a.userid,b.gtype FROM ".PRE."_user LEFT JOIN ".PRE."_user_groups USING(groupid) WHERE userid='".$userid."' LIMIT 1");
		if ( !$res['userid'] ) return false;
		if ( $res['gtype']=='admin' || $res['gtype']=='indiv' ) return true;
		return false;
	}
	
	
	
	//Ist der Nutzer Admin?
	function is_admin($userid=false) {
		if ( $userid===false ) {
			return $this->info['gtype']=='admin';
		}
		elseif ( !$userid ) {
			return false;
		}
		else {
			$res=$db->first("SELECT b.gtype FROM ".PRE."_user LEFT JOIN ".PRE."_user_groups USING(groupid) WHERE userid='".$userid."' LIMIT 1");
			return $this->info['gtype']=='admin';
		}
	}
	
	
	
	//Wird der User von einem anderen ignoriert?
	function ignore($userid,&$reasonvar) {
		global $db,$set;
		$userid = (int)$userid;
		list($check,$reason) = $db->first("SELECT userid,reason FROM ".PRE."_user_ignore WHERE userid='".$userid."' AND ignored='".$this->info['userid']."' LIMIT 1");
		$reasonvar = $reason;
		if ( $check ) return true;
		else return false;
	}
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////////// AUSGABE GENERIEREN
	
	
	//Signatur
	function mksig($info,$nospacer=false) {
		global $set;
		$text=$info['signature'];
		if ( !$text ) return '';
		
		if ( $set['user']['sig_badwords'] ) $text=badwords($text);
		$text=replace($text,1);
		if ( $set['user']['sig_allowsmilies'] ) $text=dbsmilies($text);
		if ( $set['user']['sig_allowcode'] ) $text=dbcodes($text,1);	
		if ( !$nospacer ) $text=$set['user']['sigspace'].$text;
		
		return $text;
	}
	
	
	
	//Profil-Link erzeugen
	function mkprofile($userid,$username='') {
		global $apx;
		$userid=(int)$userid;
		if ( !$userid ) return '#';
		
		$link=mklink(
			'user.php?action=profile&amp;id='.$userid,
			'user,profile,'.$userid.urlformat($username).'.html'
		);
		
		return $link;
	}
	
	
	
	//Avatar
	function mkavatar($info) {
		if ( !$info['avatar'] ) return '';
		$path=HTTPDIR.getpath('uploads').'user/'.$info['avatar'];
		return $path;
	}
	
	
	
	//Avatar-Titel
	function mkavtitle($info) {
		global $set;
		$title=$info['avatar_title'];
		if ( !$title ) return '';
		
		if ( $set['user']['avatar_badwords'] ) $title=badwords($title);
		return compatible_hsc($title);
	}
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////////// BENUTZERVERWALTUNG
	
	//User Info
	function get_info($userid=false,$fields='*') {
		global $db;
		if ( $userid===false ) return $this->info;
		$userid=(int)$userid;
		
		$res=$db->first("SELECT ".$fields." FROM ".PRE."_user WHERE userid='".$userid."' LIMIT 1");
		$res['buddies']=$this->get_buddies($res['buddies']);
		
		return $res;
	}
	
	
	
	//User Multi Info
	function get_info_multi($userids,$fields='*') {
		global $db;
		if ( !is_array($userids) ) return array();
		$userids = array_map('intval',$userids);
		
		$data=$db->fetch_index("SELECT userid,".$fields." FROM ".PRE."_user WHERE userid IN (".implode(',',$userids).")",'userid');
		foreach ( $data AS $key => $res ) {
			if ( !isset($res['buddies']) ) break;
			$data[$key]['buddies'] = $this->get_buddies($res['buddies']);
		}
		
		return $data;
	}
	
	
	
	//Username checken
	function block_username($username) {
		global $set,$apx;
		if ( !count($set['user']['blockusername']) ) return false;
		
		foreach ( $set['user']['blockusername'] AS $string ) {
			$strpos=strpos(strtolower($username),strtolower($string));
			if ( $strpos===false ) continue;
			return substr($username,$strpos,strlen($string));
		}
		
		return false;
	}
	
	
	
	//Prüfen ob Benutzer ein Buddy ist
	function is_buddy($id) {
		$friends = $this->get_buddies();
		return in_array($id,$friends);
	}
	
	
	
	//Prüfen ob Benutzer Buddy eines anderen Benutzers ist
	function is_buddy_of($id) {
		global $db;
		$id = (int)$id;
		if ( !$id ) return false;
		list($check) = $db->first("SELECT userid FROM ".PRE."_user_friends WHERE userid='".$id."' AND friendid='".$this->info['userid']."' LIMIT 1");
		return $check ? true : false;
	}


} //END CLASS

//Klasse sofort initialisieren für Sprachpaket und Userinfos
$user=new user;
$user->init();

?>