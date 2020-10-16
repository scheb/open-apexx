<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Mail absenden
function misc_mailform() {
	global $set,$db,$apx,$user;
	if ( $_SERVER['REQUEST_METHOD']!='POST' ) return;
	if ( !isset($set['formmailer']['sendto'][$_POST['sendto']]) ) die('sendto is not a valid mail-ID!');
	$apx->lang->drop('sendform','formmailer');
	
	//Zusätzliche Felder prüfen ob ausgefüllt
	$addnl_failed=false;
	foreach ( $_POST AS $key => $value ) {
		if ( in_array($key,array('subject','sendto','copytome')) ) continue;
		if ( substr($key,-9)!='_required' ) continue;
		if ( !$value ) $addnl_failed=true;
	}
	
	//Absenden
	if ( $addnl_failed ) message('back');
	else {
		
		//Text erstellen
		$text='';
		foreach ( $_POST AS $key => $value ) {
			if ( in_array($key,array('subject','sendto','copytome')) ) continue;
			
			//Bei required-Feldern Namen kürzen
			if ( substr($key,-9)=='_required' ) {
				$key=substr($key,0,strlen($key)-9);
			}
			
			$text.=$key.': '.$value."\n";
		}
		
		//Betreff
		if ( $_POST['subject'] ) $subject=$_POST['subject'];
		else $subject='apexx Formmailer';
		
		//Mediamanger initialisieren
		$attachments=array();
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		$temphash=md5(microtime());
		
		//Dateien hochladen
		if ( is_array($_FILES) && count($_FILES) ) {
			foreach ( $_FILES AS $fileinfo ) {
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
		}
		
		//Normale eMail senden
		if ( !count($attachments) ) {
			if ( $set['main']['mailbotname'] ) $from='From:'.$set['main']['mailbotname'].'<'.$set['main']['mailbot'].'>';
			else $from='From:'.$set['main']['mailbot'];
			$sendtomail=$set['formmailer']['sendto'][$_POST['sendto']];
			mail($sendtomail,$subject,$text,$from);
		}
		
		//eMail mit Anhang senden
		else {
			$boundary = md5(uniqid(time()));
			
			if ( $set['main']['mailbotname'] ) $from='From:'.$set['main']['mailbotname'].'<'.$set['main']['mailbot'].'>';
			else $from='From:'.$set['main']['mailbot'];
			
			$header = "MIME-Version: 1.0\n";
			$header .= $from."\n";
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
			$sendtomail=$set['formmailer']['sendto'][$_POST['sendto']];
			mail($sendtomail,$_POST['subject'],$body,$header);
			
			//Anhänge vom Server löschen
			foreach ( $attachments AS $tempfile ) {
				$mm->deletefile('temp/'.$tempfile['source']);
			}
		}
		
		message($apx->lang->get('MSG_OK'),mklink('index.php','index.html'));
	}
}

?>