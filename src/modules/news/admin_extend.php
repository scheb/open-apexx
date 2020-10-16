<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class news_functions {

var $newspicid;
var $newspicpath;


//News-Quellen auslesen
function get_sources() {
	global $set,$db,$apx;
	
	$sdata=$db->fetch("SELECT id,title,link FROM ".PRE."_news_sources ORDER BY title ASC");
	if ( !count($sdata) ) return array();
	
	foreach ( $sdata AS $res ) {
		if ( strlen($res['link'])>60 ) {
			$shortlink=substr($res['link'],0,35).' ... '.substr($res['link'],-25);
		}
		else $shortlink=$res['link'];
		
		$sourcelist[$res['id']]=array(
			'ID' => $res['id'],
			'TITLE' => replace($res['title']),
			'LINK' => $res['link'],
			'SHORTLINK' => $shortlink
		);
	}
	
	return $sourcelist;
}



//Prüfen ob ein User in einer Kategorie posten darf
function category_is_open($catid) {
	global $set,$db,$apx;
	$catid=(int)$catid;
	
	list($groups)=$db->first("SELECT forgroup FROM ".PRE."_news_cat WHERE id='".$catid."' LIMIT 1");
	$ingroups=unserialize($groups);
	
	if ( $groups=='all' ) return true;
	if ( is_array($ingroups) && in_array($apx->user->info['groupid'],$ingroups) ) return true; 
	
	return false;
}


//Kategorie-Liste holen
function get_catlist($selected=null) {
	global $set,$db,$apx;
	
	if ( is_null($selected) ) {
		$selected = $_POST['catid'];
	}
	
	$catlist = '<option></option>';
	
	if ( $set['news']['subcats'] ) $data = $this->cat->getTree(array('title', 'open', 'forgroup'));
	else $data=$db->fetch("SELECT id,title,open,forgroup FROM ".PRE."_news_cat ORDER BY title ASC");
	if ( !count($data) ) return '';
	
	foreach ( $data AS $res ) {
		$allowed=unserialize($res['forgroup']);
		if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
		
		if ( $res['open'] && ( $res['forgroup']=='all' || ( is_array($allowed) && in_array($apx->user->info['groupid'],$allowed) ) ) ) {
			$catlist.='<option value="'.$res['id'].'" '.iif($selected==$res['id'],' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
		}
		else {
			$catlist.='<option value="" disabled="disabled">'.$space.replace($res['title']).'</option>';
		}
	}
	
	return $catlist;
}



//Newspic aktualisieren
function update_newspic() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['id'] ) {
		list($current_newspic)=$db->first("SELECT newspic FROM ".PRE."_news WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$this->newspicid=$_REQUEST['id'];
		$this->newspicpath=$current_newspic;
	}
	else {
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_news'");
		$this->newspicid=$tblinfo['Auto_increment'];
		$this->newspicpath='';
	}
	
	//Mediamanager
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
	
	if ( $_POST['delpic'] ) $this->del_newspic();
	if ( $_FILES['pic_upload']['tmp_name'] ) return $this->upload_pic();
	if ( $_POST['pic_copy'] ) return $this->copy_pic();
	
	return true;
}



//News-Bild hochladen
function upload_pic() {
	global $set,$db,$apx;
	if ( !is_uploaded_file($_FILES['pic_upload']['tmp_name']) ) die('newspic has not been uploaded!');
	
	//Darf das Bild bochgeladen werden?
	$ext=$this->mm->getext($_FILES['pic_upload']['name']);
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei hochladen
	$filename='news/newspic-'.$this->newspicid;
	$tempfile='news/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->uploadfile($_FILES['pic_upload'],'',$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_pic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//News-Bild kopieren
function copy_pic() {
	global $set,$db,$apx;
	if ( !file_exists(BASEDIR.getpath('uploads').$_POST['pic_copy']) ) die('source-file does not exist!');
	
	$ext=$this->mm->getext($_POST['pic_copy']);
	/*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei duplizieren
	$filename='news/newspic-'.$this->newspicid;
	$tempfile='news/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->copyfile($_POST['pic_copy'],$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_pic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Bild verarbeiten
function process_pic($tempfile,$filename) {
	global $set,$db,$apx;
	
	require_once(BASEDIR.'lib/class.image.php');
	$this->img=new image;

	//Dateinamen
	$ext=strtolower($this->mm->getext($tempfile));
	if ( $ext=='gif' ) $ext='jpg';
	$picfile=$filename.'.'.$ext;
	$thumbfile=$filename.'-thumb.'.$ext;
	
	list($source,$sourcetype)=$this->img->getimage($tempfile);
	
	//Bild skalieren
	if ( $set['news']['newspic_popup'] && $set['news']['newspic_popup_width'] && $set['news']['newspic_popup_height'] ) {
		$width=$set['news']['newspic_popup_width'];
		$height=$set['news']['newspic_popup_height'];
	}
	else {
		$width=$set['news']['newspic_width'];
		$height=$set['news']['newspic_height'];
	}
	
	if ( $width && $height ) $pic=$this->img->resize($source,$width,$height,$set['news']['newspic_quality'],0);
	else $pic=$source;
	$this->img->saveimage($pic,$sourcetype,$picfile);
	
	//Wenn kein Popup hier ENDE
	if ( !$set['news']['newspic_popup'] || !$set['news']['newspic_width'] || !$set['news']['newspic_height'] ) {
		if ( $picfile!=$this->newspicpath ) $this->del_newspic();
		$this->newspicpath=$picfile;
		return true;
	}
	
	//Thumbnail skalieren
	$thumb=$this->img->resize($source,$set['news']['newspic_width'],$set['news']['newspic_height'],$set['news']['newspic_quality'],0);
	$this->img->saveimage($thumb,$sourcetype,$thumbfile);
	
	if ( $thumbfile!=$this->newspicpath ) $this->del_newspic();
	$this->newspicpath=$thumbfile;
	return true;
}



//Aktuelles News-Bild löschen
function del_newspic() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) return;
	
	$oldpic = $this->newspicpath;
	if ( !$oldpic ) return;
	
	//MM-Klasse laden
	if ( !isset($this->mm) ) {
		require_once(BASEDIR.'lib/class.mediamanager.php');
		$this->mm=new mediamanager;
	}
	
	//News-Bild löschen
	if ( file_exists(BASEDIR.getpath('uploads').$oldpic) ) {
		$this->mm->deletefile($oldpic);
	}
	
	//Thumbnail löschen
	if ( file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.','.',$oldpic)) ) {
		$this->mm->deletefile(str_replace('-thumb.','.',$oldpic));
	}
	
	$this->newspicpath='';
}


} //END CLASS

?>