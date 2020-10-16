<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class calendar_functions {

var $picid;
var $picpath;


//Kategorie-Liste holen
function get_catlist() {
	global $set,$db,$apx;
	
	if ( $set['calendar']['subcats'] ) $data = $this->cat->getTree(array('title'));
	else $data=$db->fetch("SELECT id,title FROM ".PRE."_calendar_cat ORDER BY title ASC");
	if ( !count($data) ) return '';
	
	foreach ( $data AS $res ) {
		if ( $res['level'] ) $space = str_repeat('&nbsp;&nbsp;',$res['level']-1);
		$catlist.='<option value="'.$res['id'].'" '.iif($_POST['catid']==$res['id'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
	}
	
	return $catlist;
}



//Daystamp erzeugen
function generate_stamp( $day, $month, $year ) {
	return sprintf('%04d%02d%02d',$year,$month,$day);
}



//Aus einem Daystamp einen Timestamp machen
function stamp2time($stamp) {
	$info = $this->explode_stamp($stamp);
	return mktime(0,0,0,$info['month'],$info['day'],$info['year'])+TIMEDIFF;
}



//Einen Daystamp in seine Bestandteile zerlegen
function explode_stamp($stamp) {
	$stamp=sprintf('%08d',$stamp);
	$info=array(
		'day' => (int)substr($stamp,6,2),
		'month' => (int)substr($stamp,4,2),
		'year' => (int)substr($stamp,0,4)
	);
	return $info;
}



//Bild aktualisieren
function update_pic() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['id'] ) {
		list($current_pic)=$db->first("SELECT picture FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$this->picid=$_REQUEST['id'];
		$this->picpath=$current_pic;
	}
	else {
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_calendar_events'");
		$this->picid=$tblinfo['Auto_increment'];
		$this->picpath='';
	}
	
	//Mediamanager
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
	
	if ( $_POST['delpic'] ) $this->del_pic();
	if ( $_FILES['pic_upload']['tmp_name'] ) return $this->upload_pic();
	if ( $_POST['pic_copy'] ) return $this->copy_pic();
	
	return true;
}



//Bild hochladen
function upload_pic() {
	global $set,$db,$apx;
	if ( !is_uploaded_file($_FILES['pic_upload']['tmp_name']) ) die('pic has not been uploaded!');
	
	//Darf das Bild bochgeladen werden?
	$ext=$this->mm->getext($_FILES['pic_upload']['name']);
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei hochladen
	$filename='calendar/eventpic-'.$this->picid;
	$tempfile='calendar/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->uploadfile($_FILES['pic_upload'],'',$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_pic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Bild kopieren
function copy_pic() {
	global $set,$db,$apx;
	if ( !file_exists(BASEDIR.getpath('uploads').$_POST['pic_copy']) ) die('source-file does not exist!');
	
	$ext=$this->mm->getext($_POST['pic_copy']);
	/*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei duplizieren
	$filename='calendar/eventpic-'.$this->picid;
	$tempfile='calendar/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
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
	if ( $set['calendar']['pic_popup'] && $set['calendar']['pic_popup_width'] && $set['calendar']['pic_popup_height'] ) {
		$width=$set['calendar']['pic_popup_width'];
		$height=$set['calendar']['pic_popup_height'];
	}
	else {
		$width=$set['calendar']['pic_width'];
		$height=$set['calendar']['pic_height'];
	}
	
	if ( $width && $height ) $pic=$this->img->resize($source,$width,$height,$set['calendar']['pic_quality'],0);
	else $pic=$source;
	$this->img->saveimage($pic,$sourcetype,$picfile);
	
	//Wenn kein Popup hier ENDE
	if ( !$set['calendar']['pic_popup'] || !$set['calendar']['pic_width'] || !$set['calendar']['pic_height'] ) {
		if ( $picfile!=$this->picpath ) $this->del_pic();
		$this->picpath=$picfile;
		return true;
	}
	
	//Thumbnail skalieren
	$thumb=$this->img->resize($source,$set['calendar']['pic_width'],$set['calendar']['pic_height'],$set['calendar']['pic_quality'],0);
	$this->img->saveimage($thumb,$sourcetype,$thumbfile);
	
	if ( $thumbfile!=$this->picpath ) $this->del_pic();
	$this->picpath=$thumbfile;
	return true;
}



//Aktuelles Bild löschen
function del_pic() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) return;
	
	$oldpic = $this->picpath;
	if ( !$oldpic ) return;
	
	//MM-Klasse laden
	if ( !isset($this->mm) ) {
		require_once(BASEDIR.'lib/class.mediamanager.php');
		$this->mm=new mediamanager;
	}
	
	//Bild löschen
	if ( file_exists(BASEDIR.getpath('uploads').$oldpic) ) {
		$this->mm->deletefile($oldpic);
	}
	
	//Thumbnail löschen
	if ( file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.','.',$oldpic)) ) {
		$this->mm->deletefile(str_replace('-thumb.','.',$oldpic));
	}
	
	$this->picpath='';
}


} //END CLASS

?>