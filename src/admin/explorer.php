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
require('includes/_start.php');  /////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

if ( $_REQUEST['displaynavi'] ) {
	$apx->tmpl->loaddesign('blank');
	$apx->tmpl->assign('NAVI',$html->mm_navi());
	$apx->tmpl->parse('mediamanager_navi','/');
}


/////////////////////////////////////////////////////////////////////////////////////// ORDNERSTRUKTUR
else {
	
	//Ordner auslesen
	function readout_dir($dirname) {
		$dirs = array();
		$handle=opendir(BASEDIR.getpath('uploads').$dirname);
		while ( $file=readdir($handle) ) {
			if ( $file=='.' || $file=='..' ) continue;
			if ( is_dir(BASEDIR.getpath('uploads').iif($dirname, $dirname.'/').$file) ) {
				$dirs[] = $file;
			}
		}
		closedir($handle);
		return $dirs;
	}
	
	//Unterordner auslesen und Templatevariablen erzeugen
	function get_subtree($dir) {
		$dirs = readout_dir($dir);
		$numdirs = count($dirs);
		$dirdata=array();
		$i=0;
		foreach ( $dirs AS $dirname ) {
			++$i;
			$dirdata[] = array(
				'NAME' => compatible_hsc($dirname),
				'PATH' => iif($dir, $dir.'/').$dirname,
				'LAST' => $i==$numdirs
			);
		}
		return $dirdata;
	}
	
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm = new mediamanager;
	if ( $_REQUEST['dir'] ) {
		$apx->tmpl->assign('DIR',get_subtree($_REQUEST['dir']));
		$apx->tmpl->loaddesign('blank');
		$apx->tmpl->parse('mediamanager_subtree','/');
	}
	else {
		$apx->tmpl->assign('DIR',get_subtree(''));
		$apx->lang->dropaction('mediamanager','index');
		$apx->tmpl->loaddesign('blank');
		$apx->tmpl->parse('mediamanager_explorer','/');
	}
}



////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_end.php');  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>