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


class links_functions {

var $linkpicid;
var $linkpicpath;


//Kategorie-Liste holen
function get_catlist($selected=null) {
	global $set,$db,$apx;
	
	if ( is_null($selected) ) {
		$selected = $_POST['catid'];
	}
	
	//Neue Kategorie erstellen
	if ( $apx->user->has_right('links.catadd') ) {
		$catlist='<option value=""></option>';
	}
	
	$data = $this->cat->getTree(array('title', 'open'));
	if ( !count($data) ) return '';
	
	foreach ( $data AS $res ) {
		if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
		
		if ( $res['open'] ) {
			$catlist.='<option value="'.$res['id'].'" '.iif($selected==$res['id'],' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
		}
		else {
			$catlist.='<option value="" disabled="disabled">'.$space.replace($res['title']).'</option>';
		}
	}
	
	return $catlist;
}



//Linkpic aktualisieren
function update_linkpic() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['id'] ) {
		list($current_linkpic)=$db->first("SELECT linkpic FROM ".PRE."_links WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$this->linkpicid=$_REQUEST['id'];
		$this->linkpicpath=$current_linkpic;
	}
	else {
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_links'");
		$this->linkpicid=$tblinfo['Auto_increment'];
		$this->linkpicpath='';
	}
	
	//Mediamanager
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
	
	if ( $_POST['delpic'] ) $this->del_linkpic();
	if ( $_FILES['pic_upload']['tmp_name'] ) return $this->upload_pic();
	if ( $_POST['pic_copy'] ) return $this->copy_pic();
	
	return true;
}



//Link-Bild hochladen
function upload_pic() {
	global $set,$db,$apx;
	if ( !is_uploaded_file($_FILES['pic_upload']['tmp_name']) ) die('linkpic has not been uploaded!');
	
	//Darf das Bild bochgeladen werden?
	$ext=$this->mm->getext($_FILES['pic_upload']['name']);
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei hochladen
	$filename='links/linkpic-'.$this->linkpicid;
	$tempfile='links/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->uploadfile($_FILES['pic_upload'],'',$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_pic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Link-Bild kopieren
function copy_pic() {
	global $set,$db,$apx;
	if ( !file_exists(BASEDIR.getpath('uploads').$_POST['pic_copy']) ) die('source-file does not exist!');
	
	$ext=$this->mm->getext($_POST['pic_copy']);
	/*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei duplizieren
	$filename='links/linkpic-'.$this->linkpicid;
	$tempfile='links/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
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
	if ( $set['links']['linkpic_popup'] && $set['links']['linkpic_popup_width'] && $set['links']['linkpic_popup_height'] ) {
		$width=$set['links']['linkpic_popup_width'];
		$height=$set['links']['linkpic_popup_height'];
	}
	else {
		$width=$set['links']['linkpic_width'];
		$height=$set['links']['linkpic_height'];
	}
	
	if ( $width && $height ) $pic=$this->img->resize($source,$width,$height,$set['links']['linkpic_quality'],0);
	else $pic=$source;
	$this->img->saveimage($pic,$sourcetype,$picfile);
	
	//Wenn kein Popup hier ENDE
	if ( !$set['links']['linkpic_popup'] || !$set['links']['linkpic_width'] || !$set['links']['linkpic_height'] ) {
		if ( $picfile!=$this->linkpicpath ) $this->del_linkpic();
		$this->linkpicpath=$picfile;
		return true;
	}
	
	//Thumbnail skalieren
	$thumb=$this->img->resize($source,$set['links']['linkpic_width'],$set['links']['linkpic_height'],$set['links']['linkpic_quality'],0);
	$this->img->saveimage($thumb,$sourcetype,$thumbfile);
	
	if ( $thumbfile!=$this->linkpicpath ) $this->del_linkpic();
	$this->linkpicpath=$thumbfile;
	return true;
}



//Aktuelles Link-Bild löschen
function del_linkpic() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) return;
	
	$oldpic = $this->linkpicpath;
	if ( !$oldpic ) return;
	
	//MM-Klasse laden
	if ( !isset($this->mm) ) {
		require_once(BASEDIR.'lib/class.mediamanager.php');
		$this->mm=new mediamanager;
	}
	
	//Link-Bild löschen
	if ( file_exists(BASEDIR.getpath('uploads').$oldpic) ) {
		$this->mm->deletefile($oldpic);
	}
	
	//Thumbnail löschen
	if ( file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.','.',$oldpic)) ) {
		$this->mm->deletefile(str_replace('-thumb.','.',$oldpic));
	}
	
	$this->linkpicpath='';
}


} //END CLASS

?>