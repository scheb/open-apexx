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


# Comment Class
# =============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class comments {

	var $set=array();


	//Startup
	function __construct($module,$mid=false) {
		global $set,$apx;
		if ( !isset($module) || !isset($mid) ) return;

		if ( !(int)$mid && $mid!==false ) die('invalid MID!');
		//if ( !$apx->is_module($module) ) die('invalid module!');

		$this->module=$module;
		$this->mid=(int)$mid;
		$this->getsettings($module);
	}



	//Kommentar-Settings
	function getsettings($module) {
		global $set;

		foreach ( $set['comments'] AS $key => $info ) {
			if ( isset($set[$module]['com_'.$key]) ) $this->set[$key]=$set[$module]['com_'.$key];
			else $this->set[$key]=$set['comments'][$key];
		}

		return $this->set;
	}



	//Link holen
	function getpage() {
		list($file,$query)=explode('?',$_SERVER['REQUEST_URI'],2);

		//Query-String analysieren
		if ( $query ) {
			$args=explode('&',$query);
			$params=array();

			foreach ( $args AS $arg ) {
				list($var,$value)=explode('=',$arg);
				if ( $var=='comp' ) continue;
				$params[]=$arg;
			}

			$qstring=implode('&',$params);
		}

		return str_replace('&','&amp;',$file.iif($qstring,'?'.$qstring));
	}



	/*********************** Alle Kommentar-Platzhalter ***********************/
	function assign_comments($parse=false) {
		global $set,$db,$apx,$user;
		if ( $parse!==false && !is_array($parse) ) $parse=array();

		if ( $parse===false || in_array('COMMENT',$parse) ) $apx->tmpl->assign('COMMENT',$this->display());
		if ( $parse===false || in_array('COMMENT_COUNT',$parse) ) $apx->tmpl->assign('COMMENT_COUNT',$this->count());
		if ( $parse===false || in_array('COMMENT_LINK',$parse) ) $apx->tmpl->assign('COMMENT_LINK',$this->link());
		if ( $parse===false || in_array('COMMENT_POSTTO',$parse) ) $apx->tmpl->assign('COMMENT_POSTTO',$this->postto());
		if ( $this->set['captcha'] && !$user->info['userid'] && ( $parse===false || in_array('COMMENT_CAPCHA',$parse) || in_array('COMMENT_CAPTCHA',$parse) ) ) {
			require_once(BASEDIR.'lib/class.captcha.php');
			$captcha=new captcha;
			$captchacode=$captcha->generate();
			$apx->tmpl->assign('COMMENT_CAPCHA',$captchacode); //Abwärtskompatiblität
			$apx->tmpl->assign('COMMENT_CAPTCHA',$captchacode);
		}

		$apx->tmpl->assign('DISPLAY_COMMENTS',1);
		$apx->tmpl->assign('COMMENT_REGONLY',!$this->set['pub']);
		$apx->tmpl->overwrite('MID',$this->mid);
		$apx->tmpl->overwrite('MODULE',$this->module);
	}



	/*********************** Kommentare ausgeben ***********************/
	function display() {
		global $db,$apx,$user,$set;

		$apx->lang->drop('comments','comments');
		$page=$this->getpage(array('p'));

		//Seitenzahlen
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='".addslashes($this->module)."' AND mid='".$this->mid."' AND active='1' )");
		pages($page,$count,$this->set['epp'],'comp','COMMENT');

		//Sortierreihenfolge
		if ( $this->set['order']==1 ) $order="a.time ASC";
		else $order="a.time DESC";

		//Kommentare auslesen
		$data=$db->fetch("SELECT a.* FROM ".PRE."_comments AS a WHERE ( module='".addslashes($this->module)."' AND a.mid='".$this->mid."' AND a.active='1' ) ORDER BY ".$order." ".getlimit($this->set['epp'],'comp'));
		if ( !count($data) ) return;

		//Nummerierungs-Anfang
		if ( $this->set['epp'] ) {
			if ( $this->set['order']==0 ) $entrynumber=$count-($_REQUEST['comp']-1)*$this->set['epp'];
			else $entrynumber=1+($_REQUEST['comp']-1)*$this->set['epp'];
		}
		else {
			if ( $this->set['order']==0 ) $entrynumber=$count;
			else $entrynumber=1;
		}

		foreach ( $data AS $res ) {
			++$i;

			if ( $res['userid'] && !isset($userinfo[$res['userid']]) ) {
				$userinfo[$res['userid']]=$user->get_info($res['userid'],'username,email,pub_hidemail,homepage,avatar,avatar_title,signature,lastactive,pub_invisible,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10');
			}
			if ( $res['userid'] && $userinfo[$res['userid']] ) {
				$tabledata[$i]['USERID']=$res['userid'];
				$tabledata[$i]['NAME']=replace($userinfo[$res['userid']]['username']);
				$tabledata[$i]['EMAIL']=replace(iif(!$userinfo[$res['userid']]['pub_hidemail'],$userinfo[$res['userid']]['email']));
				$tabledata[$i]['EMAIL_ENCRYPTED']=replace(iif(!$userinfo[$res['userid']]['pub_hidemail'],cryptMail($userinfo[$res['userid']]['email'])));
				$tabledata[$i]['HOMEPAGE']=replace($userinfo[$res['userid']]['homepage']);
				$tabledata[$i]['AVATAR']=$user->mkavatar($userinfo[$res['userid']]);
				$tabledata[$i]['AVATAR_TITLE']=$user->mkavtitle($userinfo[$res['userid']]);
				$tabledata[$i]['SIGNATURE']=$user->mksig($userinfo[$res['userid']]);
				$tabledata[$i]['ONLINE'] = iif(!$userinfo[$res['userid']]['pub_invisible'] && ($userinfo[$res['userid']]['lastactive']+$set['user']['timeout']*60)>=time(),1,0);
				$tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];

				//Custom-Felder
				for ( $ii=1; $ii<=10; $ii++ ) {
					$tabledata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii-1)];
					$tabledata[$i]['CUSTOM'.$ii] = compatible_hsc($userinfo[$res['userid']]['custom'.$ii]);
				}
			}
			else {
				$tabledata[$i]['NAME']=replace($res['username']);
				$tabledata[$i]['EMAIL']=replace($res['email']);
				$tabledata[$i]['EMAIL_ENCRYPTED']=replace(cryptMail($res['email']));
				$tabledata[$i]['HOMEPAGE']=replace($res['homepage']);
			}

			//Text
			$text=$res['text'];
			if ( $this->set['badwords'] ) $text=badwords($text);
			$text=replace($text,1);
			if ( $this->set['breakline'] ) $text=wordwrapHTML($text,$this->set['breakline'],"\n");
			if ( $this->set['allowsmilies'] ) $text=dbsmilies($text);
			if ( $this->set['allowcode'] ) $text=dbcodes($text);

			//Titel
			$title=$res['title'];
			if ( $this->set['breakline'] ) $title=wordwrap($title,$this->set['breakline'],"\n",1);
			if ( $this->set['badwords'] ) $title=badwords($title);
			$title=replace($title);

			$tabledata[$i]['TEXT']=$text;
			$tabledata[$i]['TITLE']=$title;
			$tabledata[$i]['TIME']=$res['time'];
			$tabledata[$i]['NUMBER']=$entrynumber;

			//Admin-Links
			if ( $_COOKIE[$set['main']['cookie_pre'].'_admin_userid'] && $_COOKIE[$set['main']['cookie_pre'].'_admin_password'] ) {
				$tabledata[$i]['EDITLINK']=HTTPDIR.'admin/action.php?action=comments.edit&amp;module='.$this->module.'&amp;mid='.$this->mid.'&amp;id='.$res['id'].'&amp;outer=1';
				$tabledata[$i]['DELETELINK']=HTTPDIR.'admin/action.php?action=comments.del&amp;module='.$this->module.'&amp;mid='.$this->mid.'&amp;id='.$res['id'].'&amp;outer=1';
			}

			//Melden
			$link_report = "javascript:popupwin('misc.php?action=comments_report&amp;id=".$res['id']."&amp;url='+escape(window.location.href),500,300);";
			$tabledata[$i]['REPORTLINK']=$link_report;

			//Kommentarnummer
			if ( $this->set['order']==0 ) --$entrynumber;
			else ++$entrynumber;
		}

		return $tabledata;
	}



	/*********************** Kommentare zählen ***********************/
	function count() {
		global $db,$apx,$user;
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='".addslashes($this->module)."' AND mid='".$this->mid."' AND active='1' )");
		return $count;
	}



	/*********************** Kommentar-Link ***********************/
	function link($page=false, $varname='comments') {
		global $db,$apx,$user;

		if ( $this->set['popup'] ) {
			return "javascript:popupwin('".HTTPDIR."misc.php?action=".$this->module."_".$varname."&amp;id=".$this->mid."&amp;sec=".$apx->section_id()."',".$this->set['popup_width'].",".$this->set['popup_height'].",1);";
		}
		else {
			if ( !$page ) $page=$this->getpage();
			if ( strpos($page,'?')!==false ) return $page.'&amp;'.$varname.'=1';
			else return $page.'?'.$varname.'=1';
		}

		/*if ( !$page ) $page=$this->getpage();

		if ( strpos($page,'?')!==false ) $link=$page.'&amp;comments=1';
		else $link=$page.'?comments=1';

		//Popup
		if ( $this->set['popup'] ) {
			$link="javascript:popupwin('".$link."',".$this->set['popup_width'].",".$this->set['popup_height'].");";
		}

		return $link;*/
	}



	/*********************** Letzter Kommentar von... ***********************/
	function get_lastcomment() {
		global $db,$apx,$user;
		if ( isset($this->lastinfo[$this->mid]) ) return;
		$res=$db->first("SELECT userid,username,time FROM ".PRE."_comments WHERE ( module='".addslashes($this->module)."' AND mid='".$this->mid."' AND active='1' ) ORDER BY time DESC LIMIT 1");
		$this->lastinfo[$this->mid]=$res;
	}

	function last_userid($page=false) {
		global $db,$apx,$user;
		$this->get_lastcomment();
		return $this->lastinfo[$this->mid]['userid'];
	}

	function last_name($page=false) {
		global $db,$apx,$user;
		$this->get_lastcomment();
		return replace($this->lastinfo[$this->mid]['username']);
	}

	function last_time($page=false) {
		global $db,$apx,$user;
		$this->get_lastcomment();
		return $this->lastinfo[$this->mid]['time'];
	}



	/*********************** Formular senden an... ***********************/
	function postto() {
		return $this->getpage();
	}



	/*********************** Kommentare hinzufügen ***********************/

	//Ist die IP des Posters gesperrt?
	function ip_is_blocked() {
		global $set;

		if ( !is_array($set['comments']['blockip']) ) $set['comments']['blockip']=array();
		foreach ( $set['comments']['blockip'] AS $res ) {
			if ( ( $res['endip']===false && $res['startip']==ip2float(get_remoteaddr()) ) || ( ip2float(get_remoteaddr())>=$res['startip'] && ip2float(get_remoteaddr())<=$res['endip'] ) ) {
				return true;
			}
		}

		return false;
	}



	//Enthält der Text verbotene Zeichenketten?
	function text_is_blocked() {
		global $set;
		$text=$_POST['text'];

		if ( !is_array($set['comments']['blockstring']) ) $set['comments']['blockstring']=array();
		foreach ( $set['comments']['blockstring'] AS $string ) {
			$string=trim($string);
			$string=preg_quote($string);
			$string=str_replace('\\*','(.*)',$string);
			if ( preg_match('#'.$string.'#miU',$text) ) return true;
		}

		return false;
	}



	//Kommentar hinzufügen
	function addcom() {
		global $db,$apx,$user;
		$_POST['mid']=(int)$_POST['mid'];
		if ( !$_POST['mid'] ) die('missing mID!');
		//if ( !$apx->is_module($_POST['module']) ) die('invalid MODULE!');

		$apx->lang->drop('add','comments');

		list($spam)=$db->first("SELECT time FROM ".PRE."_comments WHERE ( module='".addslashes($_POST['module'])."' AND ip='".get_remoteaddr()."' AND mid='".$_POST['mid']."' ) ORDER BY time DESC");

		//Captcha prüfen
		if ( $this->set['captcha'] && !$user->info['userid'] ) {
			require(BASEDIR.'lib/class.captcha.php');
			$captcha=new captcha;
			$captchafailed=$captcha->check();
		}

		if ( $user->info['userid'] ) {
			if ( $captchafailed  ) message($apx->lang->get('MSG_COM_WRONGCODE'),'javascript:history.back()');
			elseif ( $this->ip_is_blocked() ) message($apx->lang->get('MSG_COM_BLOCKIP'),'back');
			elseif ( !$_POST['text'] || ( $this->set['req_title'] && !$_POST['title'] ) ) message('back');
			elseif ( $this->text_is_blocked() ) message($apx->lang->get('MSG_COM_BLOCKTEXT'),'back');
			elseif ( $this->set['maxlen'] && strlen($_POST['text'])>$this->set['maxlen'] ) message($apx->lang->get('MSG_COM_TOOLONG'),'back');
			elseif ( ($spam+$this->set['spamprot']*60)>time() ) message($apx->lang->get('MSG_COM_BLOCKSPAM',array('SEC'=>($spam+$this->set['spamprot']*60)-time())),'back');
			else {

				if ( $this->set['mod'] && !$user->is_team_member() ) $_POST['active']=0;
				else $_POST['active']=1;

				$_POST['userid']=$user->info['userid'];
				$_POST['username']=$user->info['username'];
				$_POST['time']=time();
				$_POST['ip']=get_remoteaddr();

				$db->dinsert(PRE.'_comments','module,mid,userid,username,title,text,time,notify,ip,active');
				$comid = $db->insert_id();

				//eMail-Benachrichtigung (Admin)
				if ( $this->set['mailonnew'] ) {
					$text = strip_tags(dbcodes($_POST['text']));
					$input=array(
						'URL' => HTTP,
						'GOTO' => HTTP_HOST.$_SERVER['REQUEST_URI'],
						'TEXT' => $text
					);
					sendmail($this->set['mailonnew'],'SENDCOM',$input);
				}

				//eMail-Benachrichtigung (User)
				if ( $_POST['active'] ) {
					$data = $db->fetch("
						SELECT DISTINCT IF(c.userid, u.email, c.email) AS email
						FROM ".PRE."_comments AS c
						LEFT JOIN ".PRE."_user AS u USING(userid)
						WHERE c.module='".addslashes($_POST['module'])."' AND c.mid='".addslashes($_POST['mid'])."' AND c.notify=1 AND c.id!='".$comid."' AND c.userid!=".$user->info['userid']."
					");
					if ( count($data) ) {
						foreach ( $data AS $res ) {
							$input=array(
								'URL' => HTTP,
								'GOTO' => HTTP_HOST.$_SERVER['REQUEST_URI']
							);
							sendmail($res['email'],'NOTIFYCOM',$input);
						}
					}

					//Notify zurücksetzen
					$db->query("UPDATE ".PRE."_comments SET notify=0 WHERE module='".addslashes($_POST['module'])."' AND mid='".addslashes($_POST['mid'])."' AND id!='".$comid."'");
				}

				//Captcha löschen
				if ( $this->set['captcha'] && !$user->info['userid'] ) {
					$captcha->remove();
				}

				message($apx->lang->get('MSG_COM_OK'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
			}
		}
		elseif ( $this->set['pub'] ) {
			if ( !checkmail($_POST['email']) ) {
				if ( $this->set['req_email'] ) $emailnotvalid=true;
				else $_POST['email']='';
			}

			if ( $captchafailed  ) message($apx->lang->get('MSG_COM_WRONGCODE'),'javascript:history.back()');
			elseif ( $this->ip_is_blocked() ) message($apx->lang->get('MSG_COM_BLOCKIP'),'back');
			elseif ( !$_POST['username'] || !$_POST['text']
				|| ( $this->set['req_email'] && !$_POST['email'] )
				|| ( $this->set['req_homepage'] && !$_POST['homepage'] )
				|| ( $this->set['req_title'] && !$_POST['title'] )
			) message('back');
			elseif ( $_POST['notify'] && !$_POST['email'] ) message($apx->lang->get('MSG_COM_MAILNEEDED'),'back');
			elseif ( $this->text_is_blocked() ) message($apx->lang->get('MSG_COM_BLOCKTEXT'),'back');
			elseif ( $this->set['entrymaxlen'] && strlen($_POST['text'])>$this->set['entrymaxlen'] ) message($apx->lang->get('MSG_COM_TOOLONG'),'back');
			elseif ( $emailnotvalid ) message($apx->lang->get('MSG_COM_EMAILNOTVALID'),'back');
			elseif ( ($spam+$this->set['spamprot']*60)>time() ) message($apx->lang->get('MSG_COM_BLOCKSPAM',array('SEC'=>($spam+$this->set['spamprot']*60)-time())),'back');
			else {
				if ( substr($_POST['homepage'],0,4)=='www.' ) $_POST['homepage']='http://'.$_POST['homepage'];

				if ( $this->set['mod'] ) $_POST['active']=0;
				else $_POST['active']=1;

				$_POST['time']=time();
				$_POST['ip']=get_remoteaddr();

				$db->dinsert(PRE.'_comments','module,mid,userid,username,email,homepage,title,text,time,notify,ip,active');

				//eMail-Benachrichtigung (Admin)
				if ( $this->set['mailonnew'] ) {
					$text = strip_tags(dbcodes($_POST['text']));
					$input=array(
						'URL' => HTTP,
						'GOTO' => HTTP_HOST.$_SERVER['REQUEST_URI'],
						'TEXT' => $text
					);
					sendmail($this->set['mailonnew'],'SENDCOM',$input);
				}

				//eMail-Benachrichtigung (User)
				if ( $_POST['active'] ) {
					$data = $db->fetch("
						SELECT DISTINCT IF(c.userid, u.email, c.email) AS email
						FROM ".PRE."_comments AS c
						LEFT JOIN ".PRE."_user AS u USING(userid)
						WHERE c.module='".addslashes($_POST['module'])."' AND c.mid='".addslashes($_POST['mid'])."' AND c.notify=1 AND c.id!='".$comid."'
					");
					if ( count($data) ) {
						foreach ( $data AS $res ) {
							$input=array(
								'URL' => HTTP,
								'GOTO' => HTTP_HOST.$_SERVER['REQUEST_URI']
							);
							sendmail($res['email'],'NOTIFYCOM',$input);
						}
					}

					//Notify zurücksetzen
					$db->query("UPDATE ".PRE."_comments SET notify=0 WHERE module='".addslashes($_POST['module'])."' AND mid='".addslashes($_POST['mid'])."' AND id!='".$comid."'");
				}

				//Captcha löschen
				if ( $this->set['captcha'] && !$user->info['userid'] ) {
					$captcha->remove();
				}

				message($apx->lang->get('MSG_COM_OK'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
			}
		}
	}


} //END CLASS

?>
