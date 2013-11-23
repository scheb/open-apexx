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


# Mediamanager Function Class
# ===========================

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class mediamanager {

//STARTUP
function mediamanager() {
	umask(0); //CHMOD
	
	if ( isset($_REQUEST['dir']) )     $_REQUEST['dir']=$this->securepath($_REQUEST['dir']);
	if ( isset($_REQUEST['newdir']) )  $_REQUEST['newdir']=$this->securepath($_REQUEST['newdir']);
	if ( isset($_REQUEST['file']) )    $_REQUEST['file']=$this->securefile($_REQUEST['file']);
}



////////////////////////////////////////////////////////////////////////////////// -> FUNKTIONEN

//Pfad auf Manipulation checken
function securepath($path) {
	$path=trim($path);
	$path=str_replace('\\','',$path);         //Backslash entfernen!
	$path=str_replace('.','',$path);          //Punkte entfernen
	$path=preg_replace('#[/]{2,}#','/',$path); //Doppelte Slashes entfernen
	
	if ( strpos($path,'/')===0 ) $path=substr($path,1); //Slash am Anfang entfernen
	
	return $path;
}


//Datei-Pfad auf Manipulation checken
function securefile($path) {
	$path=trim($path);
	$last=strrpos($path,'/');
	
	if ( $last===false ) $p=array('',$path);
	else $p=array($this->securepath(substr($path,0,$last)),substr($path,$last+1));
	
	$p[1]=str_replace('\\','',$p[1]);         //Backslash entfernen!
	
	return iif($p[0],$p[0].'/').$p[1];
}


//Pfad holen
function getpath($filepath) {
	$filepath=str_replace('\\','/',$filepath);
	$dirname=dirname($filepath);
	if ( $dirname=='.' ) return '';
	else return $dirname.'/';
}


//Dateiname holen
function getfile($filepath) {
	$filepath=str_replace('\\','/',$filepath);
	return basename($filepath);
}


//Endung holen
function getext($filepath) {
	$filepath=str_replace('\\','/',$filepath);
	$pathinfo=pathinfo($filepath);
	return strtoupper($pathinfo['extension']);
}


//Dateinamen ohne Endung
function getname($filepath) {
	$filepath=str_replace('\\','/',$filepath);
	$filename=$this->getfile($filepath);
	return substr($filename,0,strrpos($filename,'.'));
}



////////////////////////////////////////////////////////////////////////////////// -> ORDNER


//ORDNER ERSTELLEN
function createdir($name,$dir='') {
	if ( !$dir && MODE=='admin' ) $dir=$_REQUEST['dir'];
	$newdir=iif($dir,$dir.'/').$name;
	
	if ( !is_writeable(BASEDIR.getpath('uploads').$dir) ) {
		echo 'directory "'.BASEDIR.getpath('uploads').$dir.'" is not writeable!';
		return;
	}
	
	if ( !mkdir(BASEDIR.getpath('uploads').$newdir,0777) ) {
		echo 'can not create directory!';
		return;
	}
	
	@chmod(BASEDIR.getpath('uploads').$newdir,0777);
}


//ORDNER UMBENENNEN
function renamedir($oldpath,$newname) {
	$dir=$this->getpath($oldpath);
	$newpath=iif($dir,$dir.'/').$newname;
	
	if ( !rename(BASEDIR.getpath('uploads').$oldpath,BASEDIR.getpath('uploads').$newpath) ) {
		echo('can not rename directory!');
	}
}


//ORDNER LÖSCHEN
function deletedir($dirpath) {
	if ( !rmdir(BASEDIR.getpath('uploads').$dirpath) ) echo('can not delete directory!');
}



////////////////////////////////////////////////////////////////////////////////// -> DATEIEN


//DATEI HOCHLADEN
function uploadfile($file,$dir='',$filename='') {
	if ( !$dir && MODE=='admin' ) $dir=$_REQUEST['dir'];
	if ( !$filename && MODE=='admin' ) $filename=$file['name'];
	$uploadpath=iif($dir,$dir.'/').$filename;
	
	if ( !is_writeable(BASEDIR.getpath('uploads').$dir) ) {
		echo 'directory "'.BASEDIR.getpath('uploads').$dir.'" is not writeable!';
		return;
	}
	
	$tmpfile=tempnam(BASEDIR.getpath('uploads'),'upload');
	$feedback=move_uploaded_file($file['tmp_name'],$tmpfile);
	if ( !$feedback ) {
		echo $file['tmp_name'].' has not been found!';
		return false;
	}
	
	if ( file_exists(BASEDIR.getpath('uploads').$uploadpath) ) {
		echo 'file '.getpath('uploads').$uploadpath.' already exists!';
		unlink($tmpfile);
		return false;
	}
	
	rename($tmpfile,BASEDIR.getpath('uploads').$uploadpath);
	
	@chmod(BASEDIR.getpath('uploads').$uploadpath,0777);
	
	if ( $feedback ) return true;
	else return false;
}


//DATEI UMBENENNEN
function renamefile($oldpath,$newname) {
	$dir=$this->getpath($oldpath);
	$newpath=iif($dir,$dir.'/').$newname;
	
	if ( !rename(BASEDIR.getpath('uploads').$oldpath,BASEDIR.getpath('uploads').$newpath) ) echo('can not rename file!');

}


//DATEI KOPIEREN
function copyfile($source,$newpath) {
	if ( !copy(BASEDIR.getpath('uploads').$source,BASEDIR.getpath('uploads').$newpath) ) echo('can not copy file!');
}


//DATEI VERSCHIEBEN
function movefile($source,$newpath) {
	if ( !copy(BASEDIR.getpath('uploads').$source,BASEDIR.getpath('uploads').$newpath) ) echo('can not copy new file!');
	if ( !unlink(BASEDIR.getpath('uploads').$source) ) echo('can not delete old file!');
}


//DATEI LÖSCHEN
function deletefile($filepath) {
	if ( !unlink(BASEDIR.getpath('uploads').$filepath) ) echo('can not delete file!');
}


////////////////////////////////////////////////////////////////////////////////// -> DATEIEN


//DATEITYP IST ERLAUBT?
function is_allowed($file) {
	global $db;
	static $cache;
	$ext=$this->getext($file);
	
	if ( isset($cache[$ext]) ) return $cache[$ext];
	
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='block' ) {
		$cache[$ext]=false;
		return false;
	}
	
	$cache[$ext]=true;
	return true;
}


//DATEITYP IST GESCHÜTZT?
function is_protected($file) {
	global $db;
	static $cache;
	$ext=$this->getext($file);
	
	if ( isset($cache[$ext]) ) return $cache[$ext];
	
	list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
	if ( $special=='undel' ) {
		$cache[$ext]=true;
		return true;
	}
	
	$cache[$ext]=false;
	return false;
}


//IST EIN VALIDER DATEINAME
function is_valid_filename($name) {
	if ( preg_match('#^[A-Za-z0-9\._-]+[\.]{1}[A-Za-z0-9]+$#',$name) ) return true;
	else return false;
}


//IST EIN VALIDER VERZEICHNISNAME
function is_valid_dirname($name) {
	if ( preg_match('#^[A-Za-z0-9_-]+$#',$name) ) return true;
	else return false;
}


} //END CLASS

?>