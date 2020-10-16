<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


////////////////////////////////////////////////////////////////////////////////// -> KLASSE FÜR BENUTZERSYSTEM

class user {

var $rights;
var $sprights;

var $info=array();
var $timeout=10;

//Benutzersystem starten
function user() {
	global $apx;
	
	//URL-Rewriter deaktivieren
	@ini_set('url_rewriter.tags','');
	@ini_set('session.use_cookies','');
	
	$this->start_session();
	$this->get_userinfo();
	
	//Rechte Holen + Lastonline
	if ( $this->info['userid'] ) {
		$this->get_rights();
		$this->update_lastonline();
	}
	
	$this->give_default_rights();
	//$this->check_language();
}



//Benutzer-Informationen auslesen
function get_userinfo() {
	global $db,$apx;
	$sesuser = $apx->session->get('apxses_userid');
	$sespwd = $apx->session->get('apxses_password');
	
	if ( !$sesuser || !$sespwd ) return;
	
	$info=$db->first("SELECT * FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING (groupid) WHERE ( userid='".intval($sesuser)."' AND password='".addslashes($sespwd)."' ) LIMIT 1",1);
	$this->info=$info;
	
	if ( $_REQUEST['action']!='user.autologout' && ( !$this->info['userid'] || !$this->is_team_member() || !$this->info['active'] ) ) {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:'.HTTPDIR.'admin/action.php?action=user.autologout&sectoken='.$apx->session->get('sectoken'));
		exit;
	}
	
	//Sprachpaket-ID setzen
	$apx->lang->langid($this->info['admin_lang']);
}



//Zuletzt online aktualisieren
function update_lastonline() {
	global $db,$set;
	
	//Neue Sitzung
	if ( ($this->info['lastactive']+$this->timeout*60)<time() ) {
		$db->query("UPDATE ".PRE."_user SET lastonline=lastactive,lastactive='".time()."' WHERE userid='".$this->info['userid']."' LIMIT 1");
		$this->info['lastonline']=$this->info['lastactive'];
		$this->info['lastactive']=time();
	}
	
	//Bestehende Sitzung fortsetzen
	else {
		$db->query("UPDATE ".PRE."_user SET lastactive='".time()."' WHERE userid='".$this->info['userid']."' LIMIT 1");
		$this->info['lastactive']=time();
	}
}



//Sprachpaket überpfüfen
/*function check_language() {
	global $apx;
	if ( $this->info['admin_lang'] && array_key_exists($this->info['admin_lang'],$apx->languages) ) return;
	
	$this->info['admin_lang']=$apx->language_default;
}*/



//Session wird gestartet
function start_session() {
	global $apx, $set;
	
	$sesuser = $apx->session->get('apxses_userid');
	$sespwd = $apx->session->get('apxses_password');
	
	//Cookie-Login
	if ( ( !$sesuser || !$sespwd ) && ( $_COOKIE[$set['main']['cookie_pre'].'_admin_userid'] && $_COOKIE[$set['main']['cookie_pre'].'_admin_password'] ) ) {
		$apx->session->set('apxses_userid', $_COOKIE[$set['main']['cookie_pre'].'_admin_userid']);
		$apx->session->set('apxses_password', $_COOKIE[$set['main']['cookie_pre'].'_admin_password']);
	}
}



//Rechte holen
function get_rights() {
	global $db;
	if ( MODE!='admin' ) return;
	
	//Admin -> alle Rechte
	if ( $this->info['gtype']=='admin' ) {
		$this->rights['global']=$this->sprights['global']='global';
		return;
	}
	
	$this->rights=unserialize($this->info['rights']);
	$this->sprights=unserialize($this->info['sprights']);
}



//Standard-Rechte setzen
function give_default_rights() {
	global $apx;
	if ( MODE!='admin' ) return;
	
	foreach ( $apx->actions AS $module => $info ) {
		foreach ( $info AS $action => $ainfo ) {
			if ( !$ainfo[3]==1 ) continue;
			$this->rights[]=$module.'.'.$action;
		}
	}
}


////////////////////////////////////////////////////////////////////////////////// -> RECHTE-CHECKS

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



//Hat der User das Recht diese Aktion auszuführen?
function has_right($action) {
	if ( MODE!='admin' ) return;
	
	if ( $this->rights['global']=='global' ) return true;
	if ( in_array($action,$this->rights) ) return true;
	
	return false;
}



//Hat der User Sonderrechte für diese Aktion?
function has_spright($action) {
	if ( MODE!='admin' ) return;

	if ( $this->sprights['global']=='global' ) return true;
	if ( is_array($this->sprights) && in_array($action,$this->sprights) ) return true;
	
	return false;
}


} //END CLASS

$apx->user = new user;



////////////////////////////////////////////////////////////////////////////////// -> FUNKTIONEN


//Liste der Teammitglieder ausgeben
function user_team($selected=0) {
	global $set,$apx,$db;
	$selected=(int)$selected;
	
	$data=$db->fetch("SELECT a.userid,a.username FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE ( ".iif($selected,"userid='".$selected."' OR")." ( active='1' AND b.gtype IN ('admin','indiv') ) ) ORDER BY username ASC");
	if ( !count($data) ) return;
	
	foreach ( $data AS $res ) {
		echo '<option value="'.$res['userid'].'"'.iif($res['userid']==$selected,' selected="selected"').'>'.replace($res['username']).'</option>';
	}
}


?>