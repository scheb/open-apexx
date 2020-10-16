<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class products_functions {

var $picid;
var $picpath;

var $unitpicid;
var $unitpicpath;



//Release-Datensatz erzeugen
function generate_release($ele) {
	if ( $ele['month'] && $ele['year'] ) unset($ele['quater']);
	if ( $ele['quater'] ) unset($ele['day'],$ele['month']);
	
	//Variante 1: Datum
	if ( $ele['day'] && $ele['month'] && $ele['year'] ) {
		$reldata=array(
			'day' => $ele['day'],
			'month' => $ele['month'],
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],$ele['month'],$ele['day']);
	}
	
	//Variante 2: Monat
	elseif ( $ele['month'] && $ele['year'] ) {
		$reldata=array(
			'month' => $ele['month'],
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],$ele['month'],40); //Ende des Monats
	}
	
	//Variante 3: Quartal
	elseif ( $ele['quater'] && $ele['year'] ) {
		$reldata=array(
			'quater' => $ele['quater'],
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],$ele['quater']*3,50); //Ende des Quartals
	}
	
	//Variante 4: Jahreszahl
	else {
		$reldata=array(
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],12,60); //Ende des Jahres
	}
	
	if ( isset($ele['system']) ) return array($reldata,$relstamp,$ele['system']);
	else return array($reldata,$relstamp);
}



//Einheiten-Liste
function get_units($select=0, $type='all') {
	global $set,$db,$apx;
	static $cache;
	$select=(int)$select;
	
	if ( !isset($cache[$type]) ) {
		if ( $type=='person' || $type=='company' ) {
			$cache[$type] = $db->fetch("SELECT id,title FROM ".PRE."_products_units WHERE type='".$type."' OR id='".$select."' ORDER BY title ASC");
		}
		else {
			$cache['all'] = $db->fetch("SELECT id,title FROM ".PRE."_products_units ORDER BY title ASC");
		}
	}
	
	if ( $type=='person' || $type=='company' ) {
		$data = $cache[$type];
	}
	else {
		$data = $cache['all'];
	}
	
	$spcharlist='<option value=""></option>';
	$list='';
	$letters=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$firstletter=strtolower(substr($res['title'],0,1));
			
			//Alpha-Zeichen
			if ( in_array($firstletter,$letters) ) {
				
				if ( $firstletter!=$lastletter && in_array($firstletter,$letters) ) {
					if ( $lastletter ) $list.='</optgroup>';
					$list.='<optgroup label="'.strtoupper($firstletter).'">';
				}
				
				$list.='<option value="'.$res['id'].'"'.iif($res['id']==$select,'selected="selected"').'>'.replace($res['title']).'</option>';
				$lastletter=strtolower(substr($res['title'],0,1));
			}
			
			//Sonderzeichen
			else {
				$spcharlist.='<option value="'.$res['id'].'"'.iif($res['id']==$select,'selected="selected"').'>'.replace($res['title']).'</option>';
			}
			
		}
	}
	
	//Letzte Optgroup schlieﬂen
	if ( $lastletter ) $list.='</optgroup>';
	
	return $spcharlist.$list;
}



//Genre-Liste
function get_genre($type,$select=0) {
	global $set,$db,$apx;
	$select=(int)$select;
	
	$list='<option value=""></option>';
	$data=$db->fetch("SELECT id,title FROM ".PRE."_products_groups WHERE grouptype='genre' AND type='".addslashes($type)."' ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$list.='<option value="'.$res['id'].'"'.iif($res['id']==$select,' selected="selected"').'>'.replace($res['title']).'</option>';
		}
	}
	
	return $list;
}



//Medien-Liste
function get_media($type,$select=array()) {
	global $set,$db,$apx;
	if ( !is_array($select) ) $select = array(intval($select));
	
	$data=$db->fetch("SELECT id,title FROM ".PRE."_products_groups WHERE grouptype='medium' AND type='".addslashes($type)."' ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$list.='<option value="'.$res['id'].'"'.iif(in_array($res['id'], $select),' selected="selected"').'>'.replace($res['title']).'</option>';
		}
	}
	
	return $list;
}



//Systeme auflisten
function get_systems($select) {
	global $set,$db,$apx;
	if ( !is_array($select) ) $select=array();
	$list='';
	$data=$db->fetch("SELECT id,title FROM ".PRE."_products_groups WHERE grouptype='system' ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$list.='<option value="'.$res['id'].'"'.iif(in_array($res['id'],$select),' selected="selected"').'>'.replace($res['title']).'</option>';
		}
	}
	
	return $list;
}



//Release-Liste
function get_release($type) {
	global $set,$db,$apx;
	$release=array();
	
	for ( $i=1; $i<=10; $i++ ) {
		if ( !isset($_POST['release'][$i]) ) continue;
		$info=$_POST['release'][$i];
		if ( !$info['quater'] && !$info['day'] && !$info['month'] && !$info['year'] ) continue;
		if ( $type=='game' ) {
			$release[$i]['SYSTEM']=$this->get_systems(array($info['system']));
		}
		else {
			$release[$i]['MEDIA']=$this->get_media($type, $info['system']);
		}
		$release[$i]['DAY']=(int)$info['day'];
		$release[$i]['MONTH']=(int)$info['month'];
		$release[$i]['YEAR']=(int)$info['year'];
		$release[$i]['QUATER']=(int)$info['quater'];
		$release[$i]['DISPLAY']=1;
	}
	
	while( count($release)<10 ) {
		++$i;
		$release[$i]['DISPLAY']=iif(count($release)==0,1,0);
		if ( $type=='game' ) {
			$release[$i]['SYSTEM']=$this->get_systems(array());
		}
		else {
			$release[$i]['MEDIA']=$this->get_media($type, 0);
		}
	}
	
	return $release;
}



//Produkt-Pic aktualisieren
function update_pic() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['id'] ) {
		list($current_pic)=$db->first("SELECT picture FROM ".PRE."_products WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$this->picid=$_REQUEST['id'];
		$this->picpath=$current_pic;
	}
	else {
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_products'");
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



//Produkt-Bild hochladen
function upload_pic() {
	global $set,$db,$apx;
	if ( !is_uploaded_file($_FILES['pic_upload']['tmp_name']) ) die('pic has not been uploaded!');
	
	//Darf das Bild bochgeladen werden?
	$ext=$this->mm->getext($_FILES['pic_upload']['name']);
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei hochladen
	$filename='products/pic-'.$this->picid;
	$tempfile='products/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->uploadfile($_FILES['pic_upload'],'',$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_pic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Produkt-Bild kopieren
function copy_pic() {
	global $set,$db,$apx;
	if ( !file_exists(BASEDIR.getpath('uploads').$_POST['pic_copy']) ) die('source-file does not exist!');
	
	$ext=$this->mm->getext($_POST['pic_copy']);
	/*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei duplizieren
	$filename='products/pic-'.$this->picid;
	$tempfile='products/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
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
	if ( $set['products']['pic_popup'] && $set['products']['pic_popup_width'] && $set['products']['pic_popup_height'] ) {
		$width=$set['products']['pic_popup_width'];
		$height=$set['products']['pic_popup_height'];
	}
	else {
		$width=$set['products']['pic_width'];
		$height=$set['products']['pic_height'];
	}
	
	if ( $width && $height ) $pic=$this->img->resize($source,$width,$height,$set['products']['pic_quality'],0);
	else $pic=$source;
	$this->img->saveimage($pic,$sourcetype,$picfile);
	
	//Wenn kein Popup hier ENDE
	if ( !$set['products']['pic_popup'] || !$set['products']['pic_width'] || !$set['products']['pic_height'] ) {
		if ( $picfile!=$this->picpath ) $this->del_pic();
		$this->picpath=$picfile;
		return true;
	}
	
	//Thumbnail skalieren
	$thumb=$this->img->resize($source,$set['products']['pic_width'],$set['products']['pic_height'],$set['products']['pic_quality'],0);
	$this->img->saveimage($thumb,$sourcetype,$thumbfile);
	
	if ( $thumbfile!=$this->picpath ) $this->del_pic();
	$this->picpath=$thumbfile;
	return true;
}



//Aktuelles Produkt-Bild lˆschen
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
	
	//Produkt-Bild lˆschen
	if ( file_exists(BASEDIR.getpath('uploads').$oldpic) ) {
		$this->mm->deletefile($oldpic);
	}
	
	//Thumbnail lˆschen
	if ( file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.','.',$oldpic)) ) {
		$this->mm->deletefile(str_replace('-thumb.','.',$oldpic));
	}
	
	$this->picpath='';
}



//Teaser-Pic aktualisieren
function update_teaserpic() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['id'] ) {
		list($current_teaserpic)=$db->first("SELECT teaserpic FROM ".PRE."_products WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$this->teaserpicid=$_REQUEST['id'];
		$this->teaserpicpath=$current_teaserpic;
	}
	else {
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_products'");
		$this->teaserpicid=$tblinfo['Auto_increment'];
		$this->teaserpicpath='';
	}
	
	//Mediamanager
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
	
	if ( $_POST['delteaserpic'] ) $this->del_teaserpic();
	if ( $_FILES['teaserpic_upload']['tmp_name'] ) return $this->upload_teaserpic();
	if ( $_POST['teaserpic_copy'] ) return $this->copy_teaserpic();
	
	return true;
}



//Produkt-Bild hochladen
function upload_teaserpic() {
	global $set,$db,$apx;
	if ( !is_uploaded_file($_FILES['teaserpic_upload']['tmp_name']) ) die('teaserpic has not been uploaded!');
	
	//Darf das Bild bochgeladen werden?
	$ext=$this->mm->getext($_FILES['teaserpic_upload']['name']);
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei hochladen
	$filename='products/teaserpic-'.$this->teaserpicid;
	$tempfile='products/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->uploadfile($_FILES['teaserpic_upload'],'',$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_teaserpic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Produkt-Bild kopieren
function copy_teaserpic() {
	global $set,$db,$apx;
	if ( !file_exists(BASEDIR.getpath('uploads').$_POST['teaserpic_copy']) ) die('source-file does not exist!');
	
	$ext=$this->mm->getext($_POST['teaserpic_copy']);
	/*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei duplizieren
	$filename='products/teaserpic-'.$this->teaserpicid;
	$tempfile='products/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->copyfile($_POST['teaserpic_copy'],$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_teaserpic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Bild verarbeiten
function process_teaserpic($tempfile,$filename) {
	global $set,$db,$apx;
	
	require_once(BASEDIR.'lib/class.image.php');
	$this->img=new image;

	//Dateinamen
	$ext=strtolower($this->mm->getext($tempfile));
	if ( $ext=='gif' ) $ext='jpg';
	$teaserpicfile=$filename.'.'.$ext;
	$thumbfile=$filename.'-thumb.'.$ext;
	
	list($source,$sourcetype)=$this->img->getimage($tempfile);
	
	//Bild skalieren
	if ( $set['products']['teaserpic_popup'] && $set['products']['teaserpic_popup_width'] && $set['products']['teaserpic_popup_height'] ) {
		$width=$set['products']['teaserpic_popup_width'];
		$height=$set['products']['teaserpic_popup_height'];
	}
	else {
		$width=$set['products']['teaserpic_width'];
		$height=$set['products']['teaserpic_height'];
	}
	
	if ( $width && $height ) $teaserpic=$this->img->resize($source,$width,$height,$set['products']['teaserpic_quality'],0);
	else $teaserpic=$source;
	$this->img->saveimage($teaserpic,$sourcetype,$teaserpicfile);
	
	//Wenn kein Popup hier ENDE
	if ( !$set['products']['teaserpic_popup'] || !$set['products']['teaserpic_width'] || !$set['products']['teaserpic_height'] ) {
		if ( $teaserpicfile!=$this->teaserpicpath ) $this->del_teaserpic();
		$this->teaserpicpath=$teaserpicfile;
		return true;
	}
	
	//Thumbnail skalieren
	$thumb=$this->img->resize($source,$set['products']['teaserpic_width'],$set['products']['teaserpic_height'],$set['products']['teaserpic_quality'],0);
	$this->img->saveimage($thumb,$sourcetype,$thumbfile);
	
	if ( $thumbfile!=$this->teaserpicpath ) $this->del_teaserpic();
	$this->teaserpicpath=$thumbfile;
	return true;
}



//Aktuelles Produkt-Bild lˆschen
function del_teaserpic() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) return;
	
	$oldteaserpic = $this->teaserpicpath;
	if ( !$oldteaserpic ) return;
	
	//MM-Klasse laden
	if ( !isset($this->mm) ) {
		require_once(BASEDIR.'lib/class.mediamanager.php');
		$this->mm=new mediamanager;
	}
	
	//Produkt-Bild lˆschen
	if ( file_exists(BASEDIR.getpath('uploads').$oldteaserpic) ) {
		$this->mm->deletefile($oldteaserpic);
	}
	
	//Thumbnail lˆschen
	if ( file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.','.',$oldteaserpic)) ) {
		$this->mm->deletefile(str_replace('-thumb.','.',$oldteaserpic));
	}
	
	$this->teaserpicpath='';
}



//Hersteller-Pic aktualisieren
function update_unitpic() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['id'] ) {
		list($current_unitpic)=$db->first("SELECT picture FROM ".PRE."_products_units WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$this->unitpicid=$_REQUEST['id'];
		$this->unitpicpath=$current_unitpic;
	}
	else {
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_products_units'");
		$this->unitpicid=$tblinfo['Auto_increment'];
		$this->unitpicpath='';
	}
	
	//Mediamanager
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
	
	if ( $_POST['delpic'] ) $this->del_unitpic();
	if ( $_FILES['pic_upload']['tmp_name'] ) return $this->upload_unitpic();
	if ( $_POST['pic_copy'] ) return $this->copy_unitpic();
	
	return true;
}



//Produkt-Bild hochladen
function upload_unitpic() {
	global $set,$db,$apx;
	if ( !is_uploaded_file($_FILES['pic_upload']['tmp_name']) ) die('pic has not been uploaded!');
	
	//Darf das Bild bochgeladen werden?
	$ext=$this->mm->getext($_FILES['pic_upload']['name']);
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei hochladen
	$filename='products/unit-'.$this->unitpicid;
	$tempfile='products/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->uploadfile($_FILES['pic_upload'],'',$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_unitpic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Produkt-Bild kopieren
function copy_unitpic() {
	global $set,$db,$apx;
	if ( !file_exists(BASEDIR.getpath('uploads').$_POST['pic_copy']) ) die('source-file does not exist!');
	
	$ext=$this->mm->getext($_POST['pic_copy']);
	/*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
	if ( !in_array($ext,array('GIF','JPG','JPEG','JPE','PNG')) ) { info($apx->lang->get('INFO_NOIMAGE')); return false; }
	
	//Datei duplizieren
	$filename='products/unit-'.$this->unitpicid;
	$tempfile='products/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
	$this->mm->copyfile($_POST['pic_copy'],$tempfile);
	
	//Bild verarbeiten
	$feedback=$this->process_unitpic($tempfile,$filename);
	$this->mm->deletefile($tempfile);
	
	return $feedback;
}



//Bild verarbeiten
function process_unitpic($tempfile,$filename) {
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
	if ( $set['products']['pic_popup'] && $set['products']['pic_popup_width'] && $set['products']['pic_popup_height'] ) {
		$width=$set['products']['pic_popup_width'];
		$height=$set['products']['pic_popup_height'];
	}
	else {
		$width=$set['products']['pic_width'];
		$height=$set['products']['pic_height'];
	}
	
	if ( $width && $height ) $pic=$this->img->resize($source,$width,$height,$set['products']['pic_quality'],0);
	else $pic=$source;
	$this->img->saveimage($pic,$sourcetype,$picfile);
	
	//Wenn kein Popup hier ENDE
	if ( !$set['products']['pic_popup'] || !$set['products']['pic_width'] || !$set['products']['pic_height'] ) {
		if ( $picfile!=$this->picpath ) $this->del_unitpic();
		$this->unitpicpath=$picfile;
		return true;
	}
	
	//Thumbnail skalieren
	$thumb=$this->img->resize($source,$set['products']['pic_width'],$set['products']['pic_height'],$set['products']['pic_quality'],0);
	$this->img->saveimage($thumb,$sourcetype,$thumbfile);
	
	if ( $thumbfile!=$this->unitpicpath ) $this->del_unitpic();
	$this->unitpicpath=$thumbfile;
	return true;
}



//Aktuelles Produkt-Bild lˆschen
function del_unitpic() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) return;
	
	$oldpic = $this->unitpicpath;
	if ( !$oldpic ) return;
	
	//MM-Klasse laden
	if ( !isset($this->mm) ) {
		require_once(BASEDIR.'lib/class.mediamanager.php');
		$this->mm=new mediamanager;
	}
	
	//Produkt-Bild lˆschen
	if ( file_exists(BASEDIR.getpath('uploads').$oldpic) ) {
		$this->mm->deletefile($oldpic);
	}
	
	//Thumbnail lˆschen
	if ( file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.','.',$oldpic)) ) {
		$this->mm->deletefile(str_replace('-thumb.','.',$oldpic));
	}
	
	$this->unitpicpath='';
}


} //END CLASS

?>