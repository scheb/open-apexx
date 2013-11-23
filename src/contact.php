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

$apx->module('contact');
$apx->lang->drop('contact');

headline($apx->lang->get('HEADLINE'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE'));

////////////////////////////////////////////////////////////////////////////////////////////////////////


//eMail senden
if ( $_POST['send'] ) {
	$_POST['sendto']=(int)$_POST['sendto'];
	
	//Captcha prüfen
	if ( $set['contact']['captcha'] && !$user->info['userid'] ) {
		require(BASEDIR.'lib/class.captcha.php');
		$captcha=new captcha;
		$captchafailed=$captcha->check();
	}
	
	//Zusätzliche Felder prüfen ob ausgefüllt
	$addnl_failed=false;
	foreach ( $_POST AS $key => $value ) {
		if ( in_array($key,array('name','email','copytome','sendto','subject','text','send','capcha','captcha','capcha_hash','captcha_hash')) ) continue;
		if ( substr($key,-9)!='_required' ) continue;
		if ( !$value ) $addnl_failed=true;
	}
	
	if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');
	elseif ( !$_POST['name'] || !$_POST['email'] || !$_POST['sendto'] || !$_POST['subject'] || !$_POST['text'] || $addnl_failed ) message('back');
	elseif ( !checkmail($_POST['email']) ) message($apx->lang->get('MSG_NOEMAIL'),'back');
	else {
		list($sendtomail)=$db->first("SELECT email FROM ".PRE."_contact WHERE id='".$_POST['sendto']."' LIMIT 1");
		if ( !$sendtomail ) die('no valid contact!');
		
		//Captcha löschen
		if ( $set['contact']['captcha'] && !$user->info['userid'] ) {
			$captcha->remove();
		}
		
		//Zusätzliche Felder
		foreach ( $_POST AS $key => $value ) {
			if ( in_array($key,array('name','email','copytome','sendto','subject','text','send','capcha','captcha','capcha_hash','captcha_hash')) ) continue;
			
			//Bei required-Feldern Namen kürzen
			if ( substr($key,-9)=='_required' ) {
				$key=substr($key,0,strlen($key)-9);
			}
			
			$addnl.=$key.': '.$value."\r\n";
		}
		
		//Text erstellen
		$text = str_replace("\r", '', $_POST['text']);
		$text = str_replace("\n", "\r\n", $text);
		$text = $text.iif($addnl,"\r\n\r\n".$addnl);
		
		//Mediamanger initialisieren
		$attachments=array();
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		$temphash=md5(microtime());
		
		//Dateien hochladen
		for ( $i=1; $i<=5; $i++ ) {
			$fileinfo=$_FILES['attach'.$i];
			if ( !$fileinfo['tmp_name'] ) continue;
			if ( !is_uploaded_file($fileinfo['tmp_name']) ) continue;
			
			$tempname='contact_'.$temphash.'_'.$fileinfo['name'].'.tmp';
			$mm->uploadfile($fileinfo,'temp',$tempname);
			$attachments[]=array(
				'filename' => $fileinfo['name'],
				'source' => $tempname,
				'type' => $fileinfo['type']
			);
		}
		
		//Normale eMail senden
		if ( !count($attachments) ) {
			mail($sendtomail,$_POST['subject'],$text,'From: '.$_POST['name'].'<'.$_POST['email'].'>');
		}
		
		//eMail mit Anhang senden
		else {
			$boundary = md5(uniqid(time()));
			
			$header = "MIME-Version: 1.0\n";
			$header .= "From: ".$_POST['name']."<".$_POST['email'].">\n";
			$header .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"\n";
			
			$body = "--".$boundary."\n";
			$body .= "Content-Type: text/plain\n";
			$body .= "Content-Transfer-Encoding: 7bit\n\n";
			$body .= $text."\n\n";
			
			//Dateianhänge codieren
			$filedata='';
			foreach ( $attachments AS $source ) {
				$sourcepath=BASEDIR.getpath('uploads').'temp/'.$source['source'];
				$filedata = fread(fopen($sourcepath,'r'),filesize($sourcepath));
				
				$body .= "--".$boundary."\n";
				$body .= "Content-Type: ".$source['type']."; name=\"".$source['filename']."\"\n";
				$body .= "Content-Transfer-Encoding: base64\n";
				$body .= "Content-Disposition: attachment; filename=\"".$source['filename']."\"\n\n";
				$body .= chunk_split(base64_encode($filedata));
				$body .= "\n";
			}
			
			$body .= "--$boundary--\n";
			
			//eMail abschicken
			mail($sendtomail,$_POST['subject'],$body,$header);
			
			//Anhänge vom Server löschen
			foreach ( $attachments AS $tempfile ) {
				$mm->deletefile('temp/'.$tempfile['source']);
			}
		}
		
		//Kopie an mich
		if ( $_POST['copytome'] ) {
			$copytext = '';
			if ( $apx->lang->get('COPY_INTRO') ) {
				$copytext .= $apx->lang->get('COPY_INTRO')."\r\n\r\n-----\r\n\r\n";
			}
			$copytext .= $text;
			if ( $set['main']['mailbotname'] ) $from='From:'.$set['main']['mailbotname'].'<'.$set['main']['mailbot'].'>';
			else $from='From:'.$set['main']['mailbot'];
			if ( count($attachments) ) {
				$copytext .= "\r\n\r\n-----\r\n\r\n".$apx->lang->get('ATTACHMENTS').':';
				foreach ( $attachments AS $source ) {
					$copytext .= "\r\n- ".$source['filename'];
				}
			}
			mail($_POST['email'],$_POST['subject'],$copytext,$from);
		}
		
		$goto = mklink('index.php','index.html');
		message($apx->lang->get('MSG_OK'),$goto);
	}
	
	//SCRIPT BEENDEN
	require('lib/_end.php');
}


$data=$db->fetch("SELECT id,title FROM ".PRE."_contact ORDER BY title ASC");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		$condata[$i]['ID']=$res['id'];
		$condata[$i]['TITLE']=$res['title'];
		$condata[$i]['SELECTED']=iif($_REQUEST['sendto']==$res['id'],1,0);
	}
}

$postto=mklink(
	'contact.php',
	'contact.html'
);


//Captcha erstellen
if ( $set['contact']['captcha'] && !$user->info['userid'] ) {
	require(BASEDIR.'lib/class.captcha.php');
	$captcha=new captcha;
	$captchacode=$captcha->generate();
}			

$apx->tmpl->assign('POSTTO',$postto);
$apx->tmpl->assign('CAPCHA',$captchacode); //Abwärtskompatiblität
$apx->tmpl->assign('CAPTCHA',$captchacode);
$apx->tmpl->assign('CONTACT',$condata);

//Formular anzeigen
if ( $_REQUEST['form'] && preg_match('#^[a-zA-Z0-9_-]+$#',$_REQUEST['form']) && file_exists(BASEDIR.getpath('tmpl_modules_public',array('MODULE'=>'contact','THEME'=>$apx->tmpl->theme)).$_REQUEST['form'].'.html') ) {
	$apx->tmpl->parse($_REQUEST['form']);
}
else {
	$apx->tmpl->parse('contact');
}



////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>