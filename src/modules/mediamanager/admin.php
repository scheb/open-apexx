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


# MEDIAMANAGER
# ============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {
var $mm;
var $module;
var $refresh;

//Startup
function action() {
	global $apx;
	
	$this->module=$_REQUEST['module'];
	$apx->tmpl->assign_static('MODULE',$this->module);
	
	//MM-Klasse laden
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
	
	//Code um die Explorer-Leiste zu aktualisieren
	$this->refresh=<<<CODE
<script language="JavaScript" type="text/javascript">
<!--

top.frames[1].window.location.reload();

//-->
</script>
CODE;
}


//Verzeichnis-Baum auflisten
function listtree($path='',$space='&nbsp;&nbsp;') {
	$path2=iif($path,$path.'/','');
	
	$listdir=opendir(BASEDIR.getpath('uploads').$path);
	
	while ( $file=readdir($listdir) ) {
		if ( $file=='.' || $file=='..' || !is_dir(BASEDIR.getpath('uploads').$path2.$file) ) continue;
		
		$out.='<option value="'.$path2.$file.'"'.iif($_REQUEST['dir']==$path2.$file.'/',' selected="selected"').'>'.$space.$file.'</option>';
		$out.=$this->listtree($path2.$file,$space."&nbsp;&nbsp;");
	}
	
	closedir($listdir);
	return $out;
}


//Dateitypen auslesen
function getftype() {
	global $db,$apx;
	
	$ftype['#UNKNOWN#']['img']='<img src="design/mm/unknown.gif" alt="'.$apx->lang->get('TYPE_UNKNOWN').'" title="'.$apx->lang->get('TYPE_UNKNOWN').'" style="vertical-align:middle;" />';
	$ftype['#UNKNOWN#']['special']='';
	
	$fdata=$db->fetch("SELECT extension,name,special FROM ".PRE."_mediarules");
	if ( count($fdata) ) {
		foreach ( $fdata AS $res ) {
			$res['extension']=strtoupper($res['extension']);
			
			if ( file_exists(BASEDIR.'admin/design/mm/'.strtolower($res['extension']).'.gif') ) $ftype[$res['extension']]['img']='<img src="design/mm/'.strtolower($res['extension']).'.gif" alt="'.replace($res['name']).'" title="'.replace($res['name']).'" style="vertical-align:middle;" />';
			else $ftype[$res['extension']]['img']='<img src="design/mm/blank.gif" alt="'.replace($res['name']).'" title="'.replace($res['name']).'" style="vertical-align:middle;" />';
			
			$ftype[$res['extension']]['special']=$res['special'];
		}
	}
	
	return $ftype;
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////



//***************************** Datei Index *****************************
function index() {
	global $set,$apx,$db,$html;
	
	$ftype=$this->getftype();
	$opendir=BASEDIR.getpath('uploads').$_REQUEST['dir'];
	$parentdirs=explode('/',$_REQUEST['dir']);
	
	//Einrücken
	if ( !$_REQUEST['dir'] ) $space=0;
	else $space=count($parentdirs);
	
	//MUTTERVERZEICHNISSE AUSGEBEN
	if ( count($parentdirs) ) {
		foreach ( $parentdirs AS $currentdir ) {
			if ( $currentdir=='' ) continue;
			++$i;
			
			$mkpath[]=$currentdir;
			
			$parentdata[$i]['NAME']=$currentdir;
			$parentdata[$i]['LINK']='action.php?action=mediamanager.index&dir='.implode('/',$mkpath).'&amp;module='.$this->module;
		}
		
		$apx->tmpl->assign('PARENT',$parentdata);
		
		//Spacer für folgende Dateiauflistung festlegen
		$spacer=str_repeat('<img src="design/mm/spacer.gif" alt="" style="vertical-align:middle;" />',$space);
	}
	
	
	//Dateien auslesen
	$dirs=array();
	$files=array();
	
	$listdir=opendir($opendir);
	if ( !$listdir ) die('can not access directory!');
	
	while ( $file=readdir($listdir) ) {
		if ( $file=='.' || $file=='..' ) continue;
		if ( !strpos('.',$file) && is_dir($opendir.'/'.$file) ) $dirs[]=$file;
		else $files[]=array($file,$this->mm->getext($file));
	}
	
	closedir($listdir);
	
	
	//Ordner/Dateien sortieren
	natcasesort($dirs);
	$sortby=explode('.',$_REQUEST['sortby']);
	if ( $sortby[0]=='change' ) {
		
		//Letzte Änderung auslesen
		foreach ( $files AS $key => $value ) {
			$files[$key][]=filemtime(BASEDIR.getpath('uploads').iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$value[0]);
		}
		
		$files=array_sort($files,2,$sortby[1]);
	}
	else {
		$files=array_sort($files,0,$sortby[1]);
	}
	
	
	//OBEJKT-INDEX
	$objcount=count($dirs)+count($files);
	if ( $objcount ) {
		
		//ORDNER AUSGEBEN
		if ( count($dirs) ) {
			foreach ( $dirs AS $file ) {
				++$obj;
				
				if ( $obj==$objcount ) $dirdata[$obj]['NODE']='<img src="design/mm/closed_end.gif" alt="" style="vertical-align:middle;" />';
				else $dirdata[$obj]['NODE']='<img src="design/mm/closed.gif" alt="" style="vertical-align:middle;" />';
				
				$dirdata[$obj]['IMG']='<img src="design/mm/folder_closed.gif" alt="" style="vertical-align:middle;" />';
				$dirdata[$obj]['NAME']=$file;
				$dirdata[$obj]['LINK']='action.php?action=mediamanager.index&dir='.iif($_REQUEST['dir'],$_REQUEST['dir']."/").$file.'&amp;module='.$this->module;
				
				if ( $apx->user->has_right('mediamanager.dirrename') ) $dirdata[$obj]['OPTIONS'].=optionHTMLOverlay('rename.gif', 'mediamanager.dirrename', 'dir='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file.'&module='.$this->module, $apx->lang->get('RENAME'));
				else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
			$apx->tmpl->assign('DIR',$dirdata);
		}
		
		//DATEIEN AUSGEBEN
		if ( count($files) ) {
			foreach ( $files AS $file ) {
				++$obj;
				list($filename,$extension)=$file;
				
				$filedata[$obj]['ID']=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0];
				$filedata[$obj]['NAME']=$filename;
				$filedata[$obj]['MFUNC']='';
				
				if ( $apx->user->has_right('mediamanager.details') ) $filedata[$obj]['NAME']='<a href="action.php?action=mediamanager.details&amp;file='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0].'&amp;module='.$this->module.'">'.$file[0].'</a>';
				
				if ( array_key_exists($extension,$ftype) ) $filedata[$obj]['IMG']=$ftype[$extension]['img'];
				else $filedata[$obj]['IMG']=$ftype['#UNKNOWN#']['img'];
				
				//Modul-Name
				$modulename = $this->module;
				if ( strpos($this->module,':')!==false ) {
					$mp = explode(':',$this->module,2);
					$modulename = $mp[0];
					$inputids = explode(',',$mp[1]);
					$inputids = array_map('intval',$inputids);
				}
				
				//Modul-Funktionen
				if (
				$modulename
				&& isset($apx->modules[$modulename]['mediainput'])
				&& is_array($apx->modules[$modulename]['mediainput'])
				&& count($apx->modules[$modulename]['mediainput'])
				) {
					$funccache=array();
					$apx->lang->drop('media',$modulename);
					
					foreach ( $apx->modules[$modulename]['mediainput'] AS $key => $func ) {
						
						//Nur bestimmte Inputs
						if ( is_array($inputids) && count($inputids) ) {
							if ( !in_array($key,$inputids) ) continue;
						}
						
						if ( is_array($func['filetype']) && count($func['filetype']) && !in_array($extension,$func['filetype']) ) continue;
						
						if ( $func['urlrel']=='base' ) $url=getpath('uploads').iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0];
						elseif ( $func['urlrel']=='absolute' ) $url=BASEIDIR.getpath('uploads').iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0];
						elseif ( $func['urlrel']=='httpdir' ) $url=HTTPDIR.getpath('uploads').iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0];
						elseif ( $func['urlrel']=='http' ) $url=HTTP.getpath('uploads').iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0];
						elseif ( $func['urlrel']=='uploads' ) $url=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file[0];
						else die('unknown URLREL: '.$func['urlrel']);
						
						$funcRepl = array(
							'{PATH}' => $url,
							'{FUNCNUM}' => $apx->session->get('CKEditorFuncNum') ? $apx->session->get('CKEditorFuncNum') : 1
						);
						
						$funccache[]='<a href="javascript:'.strtr($func['function'], $funcRepl).'">'.$apx->lang->insertpack($func['icon']).'</a>';
					}
					
					$filedata[$obj]['MFUNC']=implode(' ',$funccache);
				}
				
				
				//Optionen
				if ( $apx->user->has_right('mediamanager.copy') ) $filedata[$obj]['OPTIONS'].=optionHTMLOverlay('copy.gif', 'mediamanager.copy', 'file='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$filename.'&module='.$this->module, $apx->lang->get('COPY'));
				else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
				 
				if ( $apx->user->has_right('mediamanager.move') && $ftype[$extension]['special']!="undel" ) $filedata[$obj]['OPTIONS'].=optionHTMLOverlay('move.gif', 'mediamanager.move', 'file='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$filename.'&module='.$this->module, $apx->lang->get('MOVE'));
				else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
				
				if ( $apx->user->has_right('mediamanager.rename') && $ftype[$extension]['special']!="undel" ) $filedata[$obj]['OPTIONS'].=optionHTMLOverlay('rename.gif', 'mediamanager.rename', 'file='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$filename.'&module='.$this->module, $apx->lang->get('RENAME'));
				else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
				
				if ( $apx->user->has_right('mediamanager.del') && $ftype[$extension]['special']!="undel" ) $filedata[$obj]['OPTIONS'].=optionHTMLOverlay('del.gif', 'mediamanager.del', 'file='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$filename.'&module='.$this->module, $apx->lang->get('CORE_DEL'));
				else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
				
				if ( $apx->user->has_right('mediamanager.thumb') && in_array($extension,array('GIF','JPG','JPEG','JPE','PNG')) ) $filedata[$obj]['OPTIONS'].=optionHTMLOverlay('pic.gif', 'mediamanager.thumb', 'file='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$filename.'&module='.$this->module, $apx->lang->get('THUMB'));
				else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		
		$apx->tmpl->assign('FILE',$filedata);
		}
	}
	else {
		$text=$apx->lang->get('NONE');
		if ( $apx->user->has_right('mediamanager.dirdel') && $_REQUEST['dir'] ) {
			$text.='<br />&raquo; <a href="javascript:MessageOverlayManager.createLayer(\'action.php?action=mediamanager.dirdel&dir='.$_REQUEST['dir'].'&amp;module='.$this->module.'\');">'.$apx->lang->get('DIRDEL').'</a> &laquo;';
		}
		$apx->tmpl->assign('NONE',$text);
	}
	
	//Quicklinks
	quicklink_multi('mediamanager.diradd','action.php','dir='.$_REQUEST['dir'].'&amp;module='.$this->module);
	quicklink_multi('mediamanager.upload','action.php','dir='.$_REQUEST['dir'].'&amp;module='.$this->module);
	quicklink_multi('mediamanager.sts','action.php','dir='.$_REQUEST['dir'].'&amp;module='.$this->module);
	quicklink_out();
	
	
	$multiactions = array();
	if ( $apx->user->has_right('mediamanager.del') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=mediamanager.del&module='.$this->module, false);
	if ( $apx->user->has_right('mediamanager.copy') ) $multiactions[] = array($apx->lang->get('COPY'), 'action.php?action=mediamanager.copy&module='.$this->module, true);
	if ( $apx->user->has_right('mediamanager.move') ) $multiactions[] = array($apx->lang->get('MOVE'), 'action.php?action=mediamanager.move&module='.$this->module, true);
	$html->assignfooter($multiactions);
	$apx->tmpl->parse('index');
	
	
	//Sortieren
	$orderdef[0]='file';
	$orderdef['file']=array('NOTHING','ASC','SORT_FILE');
	$orderdef['change']=array('CHANGE','DESC','SORT_LASTCHANGE');
	
	orderstr($orderdef,'action.php?action=mediamanager.index&dir='.$_REQUEST['dir'].'&amp;module='.$this->module);
	save_index($_SERVER['REQUEST_URI']);
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////



//***************************** Ordner erstellen *****************************
function diradd() {
	global $set,$apx,$db;
	
	$newdir=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$_POST['name'];
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['name'] ) infoNotComplete();
		elseif ( !$this->mm->is_valid_dirname($_POST['name']) ) info($apx->lang->get('INFO_WRONGSYNTAX'));
		elseif ( is_dir(BASEDIR.getpath('uploads').$newdir) ) info($apx->lang->get('INFO_EXISTS'));
		else {
			$this->mm->createdir($_POST['name']);
			
			logit('MEDIAMANAGER_DIRADD',$newdir);
			if ( $_REQUEST['dir'] ) {
				echo '<script type="text/javascript"> top.frames[1].tree.requestSubtree("'.$_REQUEST['dir'].'"); </script>';
			}
			else {
				echo '<script type="text/javascript"> top.frames[1].window.location.reload(); </script>';
			}
			printJSRedirect('action.php?action=mediamanager.index&dir='.$_REQUEST['dir'].'&module='.$this->module);
			return;
		}
	}
	else {
		$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
		$apx->tmpl->assign('DIR',$_REQUEST['dir']);
		
		$apx->tmpl->parse('diradd');
	}
}



//***************************** Ordner umbenennen *****************************
function dirrename() {
	global $set,$apx,$db;
	if ( !$_REQUEST['dir'] ) die('missing dir!');	
	$info = '';
	
	if ( $_POST['send']==1 ) {
		$dir=$this->mm->getpath($_REQUEST['dir']);
		$file=$this->mm->getfile($_REQUEST['dir']);
		
		if ( !checkToken() ) $info = $apx->lang->get('CORE_INVALIDTOKEN');
		elseif ( !$_POST['name'] ) $info = $apx->lang->get('CORE_BACK');
		elseif ( !$this->mm->is_valid_dirname($_POST['name']) ) $info = $apx->lang->get('INFO_WRONGSYNTAX');
		elseif ( $_POST['name']!=$file && file_exists(BASEDIR.getpath('uploads').iif($dir,$dir.'/').$_POST['name']) ) $info = $apx->lang->get('INFO_EXISTS');
		else {
			
			//Nur umbenennen wenn Name geändert!
			if ( $_POST['name']!=$file ) {
				$this->mm->renamedir($_REQUEST['dir'],$_POST['name']);
				logit('MEDIAMANAGER_DIRRENAME',$_REQUEST['dir'].' -> "'.$_POST['name'].'"');
				
				if ( $dir ) {
					echo '<script type="text/javascript"> top.frames[1].tree.requestSubtree("'.substr($dir, 0, -1).'"); </script>';
				}
				else {
					echo '<script type="text/javascript"> top.frames[1].window.location.reload(); </script>';
				}
			}
			
			printJSRedirect('action.php?action=mediamanager.index&dir='.$dir.'&module='.$this->module);
			return;
		}
	}
	else $_POST['name']=$this->mm->getfile($_REQUEST['dir']);
	
	$apx->tmpl->assign('INFO',compatible_hsc($info));
	$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
	$apx->tmpl->assign('DIR',$_REQUEST['dir']);
	tmessageOverlay('dirrename');
}



//***************************** Ordner löschen *****************************
function dirdel() {
	global $set,$apx,$db;
	if ( !$_REQUEST['dir'] ) die('missing directory!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->mm->deletedir($_REQUEST['dir']);
			logit('MEDIAMANAGER_DIRDEL',$_REQUEST['dir']);
			$newdir=$this->mm->getpath($_REQUEST['dir']);
			
			if ( $newdir ) {
				echo '<script type="text/javascript"> top.frames[1].tree.requestSubtree("'.substr($newdir, 0, -1).'"); </script>';
			}
			else {
				echo '<script type="text/javascript"> top.frames[1].window.location.reload(); </script>';
			}
			
			printJSRedirect('action.php?action=mediamanager.index&dir='.$newdir.'&module='.$this->module);
		}
	}
	else {
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($this->mm->getfile($_REQUEST['dir'])))));
		$apx->tmpl->assign('DIR',$_REQUEST['dir']);
		tmessageOverlay('dirdel');
	}
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////



//***************************** Datei hochladen *****************************
function upload() {
	global $set,$apx,$db;	
	
	//Ausführungszeit setzten -> 10 Min.
	@set_time_limit(600);
	
	if ( $_POST['send']==1 ) {
		for ( $i=1; $i<=5; $i++ ) if ( $_FILES['upload'.$i]['name'] ) ++$todo;
		for ( $i=1; $i<=5; $i++ ) if ( $_FILES['picture'.$i]['name'] ) ++$todo;
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$todo ) infoNotComplete();
		else {
			$error = array();
			
			
			//DATEIEN HOCHLADEN
			for ( $i=1; $i<=5; $i++ ) {
				if ( !$_FILES['upload'.$i]['name'] ) continue;
				$file=$_FILES['upload'.$i];
				$newpath=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file['name'];
				
				$input['NAME']=$file['name'];
				
				if ( !is_uploaded_file($file['tmp_name']) ) $error[] = 'file '.$file['name'].' has not been uploaded!';
				elseif ( !$this->mm->is_valid_filename($file['name']) ) $error[] = $apx->lang->get('INFO_WRONGSYNTAX',$input);
				elseif ( !$this->mm->is_allowed($file['name']) ) $error[] = $apx->lang->get('INFO_NOTALLOWED',$input);
				elseif ( file_exists(BASEDIR.getpath('uploads').$newpath) ) $error[] = $apx->lang->get('INFO_EXISTS',$input);
			}
			if ( !$error ) {
				for ( $i=1; $i<=5; $i++ ) {
					if ( !$_FILES['upload'.$i]['name'] ) continue;
					$file=$_FILES['upload'.$i];
					$newpath=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file['name'];
					$this->mm->uploadfile($file);
					logit('MEDIAMANAGER_UPLOAD',$newpath);
					++$done;
				}
			}
			
			
			//BILDER HOCHLADEN
			require(BASEDIR.'lib/class.image.php');
			$img=new image;
			
			for ( $i=1; $i<=5; $i++ ) {
				if ( !$_FILES['picture'.$i]['name'] ) continue;
				$file=$_FILES['picture'.$i];
				$newpath=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file['name'];
				$ext=$this->mm->getext($file['name']);
				
				$input['NAME']=$file['name'];
				$imginfo = @getimagesize($file['tmp_name']);
				
				if ( !is_uploaded_file($file['tmp_name']) ) $error[] = 'file '.$file['name'].' has not been uploaded!';
				elseif ( !$this->mm->is_valid_filename($file['name']) ) $error[] = $apx->lang->get('INFO_WRONGSYNTAX',$input);
				elseif ( !$this->mm->is_allowed($file['name']) ) $error[] = $apx->lang->get('INFO_NOTALLOWED',$input);
				elseif ( file_exists(BASEDIR.getpath('uploads').$newpath) ) $error[] = $apx->lang->get('INFO_EXISTS',$input);
				elseif ( !in_array($imginfo[2],array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) ) $error[] = $apx->lang->get('INFO_NOPIC',$input);
			}
			if ( !$error ) {
				for ( $i=1; $i<=5; $i++ ) {
					if ( !$_FILES['picture'.$i]['name'] ) continue;
					$file=$_FILES['picture'.$i];
					$newpath=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file['name'];
					$ext=$this->mm->getext($file['name']);
					
					//Datei in Uploads-Ordner verschieben
					$this->mm->uploadfile($file);
					logit('MEDIAMANAGER_UPLOAD',$newpath);
					
					$_POST['watermark'.$i]=(int)$_POST['watermark'.$i];
					$_POST['resizex'.$i]=(int)$_POST['resizex'.$i];
					$_POST['resizey'.$i]=(int)$_POST['resizey'.$i];
					$_POST['thumbx'.$i]=(int)$_POST['thumbx'.$i];
					$_POST['thumby'.$i]=(int)$_POST['thumby'.$i];
					
					//Wenn keine Bildverarbeitung statt finden soll -> Log-Eintrag + Continue
					if ( $ext=='GIF' || ( !$_POST['watermark'.$i] && !$_POST['resizex'.$i] && !$_POST['resizey'.$i] && !$_POST['thumbx'.$i] && !$_POST['thumby'.$i] ) ) {
						++$done;
						continue;
					}
					
					//AB HIER NUR BILDVERARBEITUNG!!!
					$sourcepath=$newpath;
					
					//THUMBNAIL ERSTELLEN
					$thumbpath=$this->mm->getpath($newpath).$this->mm->getname($newpath).'-thumb.'.strtolower($this->mm->getext($newpath));
					if ( file_exists(BASEDIR.getpath('uploads').$thumbpath) ) {
						$input['NAME']=$this->mm->getfile($thumbpath);
						info($apx->lang->get('INFO_EXISTS',$input).$apx->lang->get('INFO_FAILEDTHUMB'));
					}
					elseif ( $_POST['thumbx'.$i] || $_POST['thumby'.$i] ) {
						list($picture,$picturetype)=$img->getimage($newpath);
    				$thumbsizex=$_POST['thumbx'.$i];
  					$thumbsizey=$_POST['thumby'.$i];
	  				$thumb=$img->resize(
							$picture,
							$thumbsizex,
							$thumbsizey,
							$set['mediamanager']['quality_resize'],
							iif($thumbsizex && $thumbsizey,1,0)
						);
  					
  					$img->saveimage($thumb,$picturetype,$thumbpath);
  				}
					
					//Bild laden
					if ( $_POST['resizex'.$i] || $_POST['resizey'.$i] || $_POST['watermark'.$i] ) {
						list($picture,$picturetype)=$img->getimage($newpath);
					}
					
					//RESIZE
					if ( $_POST['resizex'.$i] || $_POST['resizey'.$i] ) {
						$newsizex=$_POST['resizex'.$i];
						$newsizey=$_POST['resizey'.$i];
						$picture=$img->resize(
							$picture,
							$newsizex,
							$newsizey,
							$set['mediamanager']['quality_resize'],
							iif($newsizex && $newsizey,1,0)
						);
					}
						
					//WATERMARK
					if ( $_POST['watermark'.$i] ) {
						$picture=$img->watermark(
							$picture,
							$set['mediamanager']['watermark'],
							$set['mediamanager']['watermark_position'],
							$set['mediamanager']['watermark_transp']
						);
					}
					
					//Bild speichern
					$img->saveimage($picture,$picturetype,$newpath);
				}
			}
			
			if ( $error ) {
				info(implode('<br />', $error));
			}
			else {
				printJSRedirect('action.php?action=mediamanager.index&dir='.$_REQUEST['dir'].'&sortby=change.DESC&module='.$this->module);
			}
		}
	}
	else {
		$apx->tmpl->assign('DIR',$_REQUEST['dir']);
		$apx->tmpl->parse('upload');
	}
}



//***************************** Datei von Server transferieren *****************************
function sts() {
	global $set,$apx,$db;	
	
	if ( $_POST['send']==1 ) {
		$newpath=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$_POST['filename'];
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['source'] || $_POST['source']=='http://' ) infoNotComplete();
		elseif ( !$this->mm->is_valid_filename($_POST['filename']) ) info($apx->lang->get('INFO_WRONGSYNTAX'));
		elseif ( !$this->mm->is_allowed($_POST['filename']) ) info($apx->lang->get('INFO_NOTALLOWED'));
		elseif ( file_exists(BASEDIR.getpath('uploads').iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$_POST['filename']) ) info($apx->lang->get('INFO_EXISTS'));
		else {
			
			//Ausführungszeit setzten -> 10 Min.
			@set_time_limit(600);
			
			$starttime=time();
			
			//Datei herunterladen
			$handle=fopen($_POST['source'],'r');
			if ( !$handle ) {
				message($apx->lang->get('MSG_CONNFAILED'),'javascript:history.back()');
				return;
			}
			
			$buffer=tmpfile();
			while ( !feof($handle) ) {
				$result=fgets($handle,40960);
				fwrite($buffer,$result);
			}
			@fclose($handle);
			
			
			//Datei speichern
			rewind($buffer);
			$saveto=@fopen(BASEDIR.getpath('uploads').$newpath,'w');
			if ( !$handle ) {
				message($apx->lang->get('MSG_WRITEFAILED'),'javascript:history.back()');
				return;
			}
			
			while ( !feof($buffer) ) {
				$result=fgets($buffer,40960);
				fwrite($saveto,$result);
			}
			
			@fclose($saveto);
			@fclose($buffer);
			
			logit('MEDIAMANAGER_STS',$newpath);
			printJSRedirect('action.php?action=mediamanager.index&dir='.$_REQUEST['dir'].'&sortby=change.DESC&module='.$this->module);
		}
	}
	else {
		$_POST['source']='http://';
		
		$apx->tmpl->assign('SOURCE',compatible_hsc($_POST['source']));
		$apx->tmpl->assign('FILENAME',compatible_hsc($_POST['filename']));
		$apx->tmpl->assign('DIR',$_REQUEST['dir']);
		
		$apx->tmpl->parse('sts');
	}
}



//***************************** Datei umbenennen *****************************
function rename() {
	global $set,$apx,$db;
	if ( !$_REQUEST['file'] ) die('missing file!');
	if ( $this->mm->is_protected($_REQUEST['file']) ) die("this file is protected!");
	$info = '';
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) $info = $apx->lang->get('CORE_INVALIDTOKEN');
		else {
			$dir=$this->mm->getpath($_REQUEST['file']);
			$file=$this->mm->getfile($_REQUEST['file']);
			$newpath=iif($dir,$dir.'/').$_POST['name'];
			
			if ( !$_POST['name'] ) $info = $apx->lang->get('CORE_BACK');
			elseif ( !$this->mm->is_valid_filename($_POST['name']) ) $info = $apx->lang->get('INFO_WRONGSYNTAX');
			elseif ( !$this->mm->is_allowed($_REQUEST['file']) ) $info = $apx->lang->get('INFO_NOTALLOWED');
			elseif ( $_POST['name']!=$file && file_exists(BASEDIR.getpath('uploads').$newpath) ) $info = $apx->lang->get('INFO_EXISTS');
			else {
				
				//Nur umbenennen wenn Name geändert!
				if ( $_POST['name']!=$file ) {
					$this->mm->renamefile($_REQUEST['file'],$_POST['name']);
					logit('MEDIAMANAGER_RENAME',$_REQUEST['dir'].' -> "'.$_POST['name'].'"');
				}
				
				printJSRedirect(get_index('mediamanager.index'));
				return;
			}
		}
	}
	else $_POST['name']=$this->mm->getfile($_REQUEST['file']);
	
	$apx->tmpl->assign('INFO',compatible_hsc($info));
	$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
	$apx->tmpl->assign('FILE',$_REQUEST['file']);
	tmessageOverlay('rename');
}



//***************************** Datei kopieren *****************************
function copy() {
	global $set,$apx,$db;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				foreach ( $_POST['multiid'] AS $file ) {
					$id=$this->mm->securefile($file);	
					$newfile=iif($_POST['newdir'],$_POST['newdir'].'/').$this->mm->getfile($file);
					
					if ( $newfile==$file || file_exists(BASEDIR.getpath('uploads').$newfile) ) continue;
					
					$this->mm->copyfile($file,$newfile);
					logit('MEDIAMANAGER_COPY',$file.' -> '.$newfile);
				}
				
				printJSRedirect(get_index('mediamanager.index'));
			}
			return;
		}
		
		//Dateien auflisten
		$files='';
		$fcache=array();
		
		foreach ( $_REQUEST['multiid'] AS $file ) {
			if ( !isset($_REQUEST['newdir']) ) $_REQUEST['newdir'] = $this->mm->getpath($file); //Vorgabewert
			$fcache[]=$this->mm->getfile($file).'<input type="hidden" name="multiid[]" value="'.compatible_hsc($file).'" />';
		}
		
		$dirlist='<option value=""'.iif($_REQUEST['newdir']=="",' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
		$dirlist.=$this->listtree();
		
		$apx->tmpl->assign('FILES',$files);
		$apx->tmpl->assign('FILENAME',implode(', ',$fcache));
		$apx->tmpl->assign('DIRLIST',$dirlist);
		
		tmessageOverlay('multi_copy');
	}
	
	//Einzeln
	else {
		if ( !$_REQUEST['file'] ) die('missing file!');	
		$info = '';
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) $info = $apx->lang->get('CORE_INVALIDTOKEN');
			else {
				$newfile=iif($_POST['dir'],$_POST['dir'].'/').$_POST['filename'];
				
				if ( !$_POST['filename'] ) $info = $apx->lang->get('CORE_BACK');
				elseif ( !$this->mm->is_valid_filename($_POST['filename']) ) $info = $apx->lang->get('INFO_WRONGSYNTAX');
				elseif ( !$this->mm->is_allowed($_REQUEST['filename']) ) $info = $apx->lang->get('INFO_NOTALLOWED');
				elseif ( file_exists(BASEDIR.getpath('uploads').$newfile) ) $info = $apx->lang->get('INFO_EXISTS');
				else {
					
					//Nur kopieren wenn Dateipfad geändert!
					if ( $newfile!=$_REQUEST['file'] ) {
						$this->mm->copyfile($_REQUEST['file'],$newfile);
						logit('MEDIAMANAGER_COPY',$_REQUEST['file'].' -> '.$newfile);			
					}
					
					printJSRedirect('action.php?action=mediamanager.index&dir='.$_REQUEST['dir'].'&module='.$this->module);
					return;
				}
			}
		}
		else {
			$_POST['filename']=$this->mm->getfile($_REQUEST['file']);
			$_REQUEST['dir']=$this->mm->getpath($_REQUEST['file']);
		}
		
		$dirlist='<option value=""'.iif($_REQUEST['dir']=="",' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
		$dirlist.=$this->listtree();
		
		$apx->tmpl->assign('INFO',compatible_hsc($info));
		$apx->tmpl->assign('FILENAME',compatible_hsc($_POST['filename']));
		$apx->tmpl->assign('DIRLIST',$dirlist);
		$apx->tmpl->assign('FILE',compatible_hsc($_REQUEST['file']));
		
		tmessageOverlay('copy');
	}
}



//***************************** Datei verschieben *****************************
function move() {
	global $set,$apx,$db;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				foreach ( $_REQUEST['multiid'] AS $file ) {
					$file=$this->mm->securefile($file);
					$newfile=iif($_POST['newdir'],$_POST['newdir'].'/').$this->mm->getfile($file);
					
					if ( $newfile==$file || $this->mm->is_protected($file) ) continue;
					
					$this->mm->movefile($file,$newfile);
					logit('MEDIAMANAGER_MOVE',$file.' -> '.$newfile);
				}
				
				printJSRedirect(get_index('mediamanager.index'));
			}
			return;
		}
	
		//Dateien auflisten
		$files='';
		$fcache=array();
		
		foreach ( $_REQUEST['multiid'] AS $file ) {
			if ( !isset($_REQUEST['newdir']) ) $_REQUEST['newdir']=$this->mm->getpath($file); //Vorgabewert
			$fcache[]=$this->mm->getfile($file).'<input type="hidden" name="multiid[]" value="'.compatible_hsc($file).'" />';
		}
		
		$dirlist='<option value=""'.iif($_REQUEST['newdir']=="",' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
		$dirlist.=$this->listtree();
		
		$apx->tmpl->assign('FILES',$files);
		$apx->tmpl->assign('FILENAME',implode(', ',$fcache));
		$apx->tmpl->assign('DIRLIST',$dirlist);
		
		tmessageOverlay('multi_move');
	}
	
	
	//Einzeln
	else {
		if ( !$_REQUEST['file'] ) die('missing file!');	
		if ( $this->mm->is_protected($_REQUEST['file']) ) die('this file is protected!');
		$info = '';
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) $info = $apx->lang->get('CORE_INVALIDTOKEN');
			else {
				$newfile=iif($_POST['dir'],$_POST['dir'].'/').$_POST['filename'];
				
				if ( !$_POST['filename'] ) $info = $apx->lang->get('CORE_BACK');
				elseif ( !$this->mm->is_valid_filename($_POST['filename']) ) $info = $apx->lang->get('INFO_WRONGSYNTAX');
				elseif ( !$this->mm->is_allowed($_REQUEST['filename']) ) $info = $apx->lang->get('INFO_NOTALLOWED');
				elseif ( file_exists(BASEDIR.getpath('uploads').$newfile) ) $info = $apx->lang->get('INFO_EXISTS');
				else {
					
					//Nur verschieben wenn Dateipfad geändert!
					if ( $newfile!=$_REQUEST['file'] ) {
						$this->mm->movefile($_REQUEST['file'],$newfile);
						logit('MEDIAMANAGER_MOVE',$_REQUEST['file'].' -> '.$newfile);			
					}
					
					printJSRedirect('action.php?action=mediamanager.index&dir='.$_REQUEST['dir'].'&module='.$this->module);
					return;
				}
			}
		}
		else {
			$_POST['filename']=$this->mm->getfile($_REQUEST['file']);
			$_REQUEST['dir']=$this->mm->getpath($_REQUEST['file']);
		}
		
		$dirlist='<option value=""'.iif($_REQUEST['dir']=="",' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
		$dirlist.=$this->listtree();
		
		$apx->tmpl->assign('INFO',compatible_hsc($info));
		$apx->tmpl->assign('FILENAME',compatible_hsc($_POST['filename']));
		$apx->tmpl->assign('DIRLIST',$dirlist);
		$apx->tmpl->assign('FILE',compatible_hsc($_REQUEST['file']));
		
		tmessageOverlay('move');
	}
}



//***************************** Thumbnail erstellen *****************************
function thumb() {
	global $set,$apx,$db;
	$info = '';
	
	$size=@getimagesize(BASEDIR.'uploads/'.$_REQUEST['file']);
	if ( !in_array($size[2],array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) ) die('file is not a valid image!');
	
	if ( $_POST['send']==1 ) {
		$dir=$this->mm->getpath($_REQUEST['file']);
		$ext=$this->mm->getext($_REQUEST['file']);
		if ( $ext=='GIF' ) $ext=='JPG'; //Konvertieren
		
		$newname=$_POST['newname'].'.'.strtolower($ext);
		$newpath=iif($dir,$dir.'/').$newname;
		
		if ( !$_POST['file'] || !$_POST['newname'] || !$_POST['width'] || !$_POST['height'] ) $info = $apx->lang->get('CORE_BACK');
		elseif ( !$this->mm->is_valid_filename($newname) ) $info = $apx->lang->get('INFO_WRONGSYNTAX');
		elseif ( file_exists(BASEDIR.getpath('uploads').$newpath) ) $info = $apx->lang->get('INFO_EXISTS');
		else {
			require(BASEDIR.'lib/class.image.php');
			$img=new image;
			
			list($picture,$picturetype)=$img->getimage($_REQUEST['file']);
			
			$thumbnail=$img->resize($picture,$_POST['width'],$_POST['height'],$set['mediamanager']['quality_resize'],1);
			$img->saveimage($thumbnail,$picturetype,$newpath);
			
			logit('MEDIAMANAGER_THUMB',$newpath);
			printJSRedirect(get_index('mediamanager.index'));
			return;
		}
	}
	else {
		$_POST['newname']=$this->mm->getname($_REQUEST['file']).'-thumb';
		$_POST['width']=$size[0];
		$_POST['height']=$size[1];
		$_POST['keepratio']=true;
	}
	
	$apx->tmpl->assign('SIZE_X',$size[0]);
	$apx->tmpl->assign('SIZE_Y',$size[1]);
	$apx->tmpl->assign('KEEPRATIO',iif($_POST['keepratio'],1,0));
	
	$apx->tmpl->assign('WIDTH',(int)$_POST['width']);
	$apx->tmpl->assign('HEIGHT',(int)$_POST['height']);
	
	$apx->tmpl->assign('FILE',$_REQUEST['file']);
	$apx->tmpl->assign('NEWNAME',compatible_hsc($_POST['newname']));
	$apx->tmpl->assign('FIT',$_POST['fit']);
	$apx->tmpl->assign('EXT',strtolower(iif($this->mm->getext($_REQUEST['file'])=='GIF','JPG',$this->mm->getext($_REQUEST['file']))));
	
	tmessageOverlay('thumb');
}



//***************************** Datei löschen *****************************
function del() {
	global $set,$apx,$db;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			foreach ( $_REQUEST['multiid'] AS $file ) {
				$file=$this->mm->securefile($file);
				if ( $this->mm->is_protected($file) ) continue;
				$this->mm->deletefile($file);
				logit('MEDIAMANAGER_DEL',$file);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('mediamanager.index'));
		}
	}
	
	//Einzeln
	else {
		if ( !$_REQUEST['file'] ) die('missing file!');	
		if ( $this->mm->is_protected($_REQUEST['file']) ) die('this file is protected!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$this->mm->deletefile($_REQUEST['file']);
				logit('MEDIAMANAGER_DEL',$_REQUEST['file']);
				printJSRedirect(get_index('mediamanager.index'));
			}
		}
		else {
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($this->mm->getfile($_REQUEST['file'])))));
			tmessageOverlay('del',array('FILE' => $_REQUEST['file']));
		}
	}
}



//***************************** Datails zeigen *****************************
function details() {
global $set,$apx,$db;
	if ( !$_REQUEST['file'] ) die('missing file!');	
	
	//Datei nicht gefunden
	if ( !file_exists(BASEDIR.getpath('uploads').$_REQUEST['file']) ) {
		message($apx->lang->get('MSG_NOTEXISTS'),'javascript:history.back()');
		return;
	}
	
	$ext=$this->mm->getext($_REQUEST['file']);
	if ( !$ext ) die('file has no extension!');
	
	$previewfiles=array('GIF','JPG','JPEG','JPE','PNG','TXT','HTM','HTML','XML');
	if ( in_array($ext,$previewfiles) ) $preview=$_REQUEST['file'];
	
	$type=$db->first("SELECT * FROM ".PRE."_mediarules WHERE extension='".$ext."'");
	
	if ( !$type['extension'] ) $image='<img src="design/mm/unknown.gif" alt="'.$apx->lang->get('TYPE_UNKNOWN').'" title="'.$apx->lang->get('TYPE_UNKNOWN').'" style="vertical-align:middle;" />';
	elseif ( file_exists('design/mm/'.$type['extension'].'.gif') ) $image='<img src="design/mm/'.strtolower($type['extension']).'.gif" alt="'.replace($type['name']).'" title="'.replace($type['name']).'" style="vertical-align:middle;" />';
	else $image='<img src="design/mm/blank.gif" alt="'.replace($type['name']).'" title="'.replace($type['name']).'" style="vertical-align:middle;" />';
	
	if ( $type['name'] ) $filetype=$type['name'];
	else $filetype=$apx->lang->get('TYPE_UNKNOWN');
	
	$fsize=filesize(BASEDIR.getpath('uploads').$_REQUEST['file']);
	if($fsize<=1024) $filesize=$fsize.' Byte';
	elseif ( ($fsize >= 1024) && ($fsize <= 1048576) ) $filesize=round($fsize/(1024),1).' KB';
	elseif($fsize >= 1048576) $filesize=round($fsize/(1024*1024),1).' MB';
	
	$lastchange=filemtime(BASEDIR.getpath('uploads').$_REQUEST['file']);
	
	$apx->tmpl->assign('IMG',$image);
	$apx->tmpl->assign('TYPE',$filetype);
	$apx->tmpl->assign('SIZE',$filesize);
	$apx->tmpl->assign('LASTCHANGE',mkdate($lastchange));
	
	$apx->tmpl->assign('URL',HTTP.getpath('uploads').$_REQUEST['file']);
	$apx->tmpl->assign('FILENAME',$this->mm->getfile($_REQUEST['file']));
	$apx->tmpl->assign('LOCATION',HTTPDIR.getpath('uploads').$this->mm->getpath($_REQUEST['file']));
	$apx->tmpl->assign('PREVIEW',$preview);
	
	$apx->tmpl->parse('details');
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////



//***************************** Dateiregeln *****************************
function rules() {
	global $set,$apx,$db,$html;
	
	quicklink('mediamanager.radd','action.php','module='.$this->module);
	
	$orderdef[0]='extension';
	$orderdef['extension']=array('extension','ASC','SORT_EXTENSION');
	$orderdef['name']=array('name','ASC','SORT_NAME');
	
	$col[]=array('',1,'align="center"');
	$col[]=array('COL_EXTENSION',20,'align="center"');
	$col[]=array('COL_NAME',80,'class="title"');
	
	$data=$db->fetch("SELECT id,extension,name FROM ".PRE."_mediarules ".getorder($orderdef));	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( file_exists("design/mm/".strtolower($res['extension']).".gif") ) $tabledata[$i]['COL1']='<img src="design/mm/'.strtolower($res['extension']).'.gif" alt="'.strtoupper($res['extension']).'" title="'.strtoupper($res['extension']).'" />';
			else $tabledata[$i]['COL1']='<img src="design/mm/blank.gif" alt="'.strtoupper($res['extension']).'" title="'.strtoupper($res['extension']).'" />';
			
			$tabledata[$i]['COL2']=replace(strtoupper($res['extension']));
			$tabledata[$i]['COL3']=replace($res['name']);
			
			//Optionen
			if ( $apx->user->has_right("mediamanager.redit") ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'mediamanager.redit', 'id='.$res['id'].'&module='.$this->module, $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right("mediamanager.rdel") ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'mediamanager.rdel', 'id='.$res['id'].'&module='.$this->module, $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=mediamanager.rules');
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Dateiregel erstellen *****************************
function radd() {
global $set,$apx,$db;

	if ( $_POST['send']==1 ) {
		list($check)=$db->first("SELECT id FROM ".PRE."_mediarules WHERE extension='".addslashes(strtoupper($_POST['extension']))."' LIMIT 1");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['extension'] || !$_POST['name'] ) infoNotComplete();
		elseif ( !preg_match('#^[A-Za-z0-9]+$#',$_POST['extension']) ) info($apx->lang->get('INFO_WRONGEXT'));
		elseif ( $check ) info($apx->lang->get('INFO_EXISTS'));
		else {
			$_POST['extension']=strtoupper($_POST['extension']);
			$db->dinsert(PRE.'_mediarules','extension,name,special');
			logit('MEDIAMANAGER_RADD','ID #'.$_REQUEST['id']);
			printJSRedirect('action.php?action=mediamanager.rules&module='.$this->module);
		}
	}
	else {
		$apx->tmpl->assign('ACTION','radd');
		$apx->tmpl->assign('EXTENSION',compatible_hsc($_POST['extension']));
		$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
		$apx->tmpl->assign('SPECIAL',$_POST['special']);
		
		$apx->tmpl->parse('radd_redit');
	}
}



//***************************** Dateiregel bearbeiten *****************************
function redit() {
global $set,$apx,$db;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$_REQUEST['id']=(int)$_REQUEST['id'];
	
	if ( $_POST['send']==1 ) {
		list($check)=$db->first("SELECT id FROM ".PRE."_mediarules WHERE ( extension='".addslashes(strtoupper($_POST['extension']))."' AND id!='".$_REQUEST['id']."' ) LIMIT 1");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['extension'] || !$_POST['name'] ) infoNotComplete();
		elseif ( !preg_match('#^[A-Za-z0-9]+$#',$_POST['extension']) ) info($apx->lang->get('INFO_WRONGEXT'));
		elseif ( $check ) info($apx->lang->get('INFO_EXISTS'));
		else {
			$_POST['extension']=strtoupper($_POST['extension']);
			$db->dupdate(PRE.'_mediarules','extension,name,special',"WHERE id='".$_REQUEST['id']."'");
			logit('MEDIAMANAGER_REDIT','ID #'.$_REQUEST['id']);
			printJSRedirect('action.php?action=mediamanager.rules&module='.$this->module);
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_mediarules WHERE id='".$_REQUEST['id']."' LIMIT 1");
		foreach ( $res AS $key => $val ) $_POST[$key]=$val;
		
		$apx->tmpl->assign('ACTION','redit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('EXTENSION',compatible_hsc($_POST['extension']));
		$apx->tmpl->assign('NAME',compatible_hsc($_POST['name']));
		$apx->tmpl->assign('SPECIAL',$_POST['special']);
		
		$apx->tmpl->parse('radd_redit');
	}
}



//***************************** Dateiregel löschen *****************************
function rdel() {
global $set,$apx,$db;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$_REQUEST['id']=(int)$_REQUEST['id'];
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_mediarules WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('MEDIAMANAGER_RDEL','ID #'.$_REQUEST['id']);
			printJSRedirect('action.php?action=mediamanager.rules&module='.$this->module);
		}
	}
	else {
		list($title) = $db->first("SELECT extension FROM ".PRE."_mediarules WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		$input['ID']=$_REQUEST['id'];
		tmessageOverlay('rdel',$input);
	}
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////



//***************************** Suchen *****************************
function search() {
global $set,$apx,$db;	

	if ( $_POST['send']==1 ) {
		if ( !$_REQUEST['item'] ) infoNotComplete();
		else {
			
			if ( preg_match("/^[A-Za-z0-9\.\*_-]+$/",$_REQUEST['item']) ) {
				$searchstring=$_REQUEST['item'];
				$searchstring=strtr($searchstring,array('.'=>'\.','*'=>'(.*)'));
				$found=$this->searchtree($searchstring,$_REQUEST['dir']);
			}
			
			if ( is_array($found) && count($found) ) {
				$ftype=$this->getftype();
				
				foreach ( $found AS $path => $files ) {
					foreach ( $files AS $file ) {
						++$obj;
						$extension=$this->mm->getext($file);
						$filepath=iif($path,$path.'/',$path).$file;
					
						$filedata[$obj]['NAME']=$file;
						$filedata[$obj]['PATH']=replace($path);
						
						if ( $apx->user->has_right('mediamanager.details') ) $filedata[$obj]['NAME']='<a href="action.php?action=mediamanager.details&amp;file='.$filepath.'&amp;module='.$this->module.'">'.$file.'</a>';
						
						if ( array_key_exists($extension,$ftype) ) $filedata[$obj]['IMG']=$ftype[$extension]['img'];
						else $filedata[$obj]['IMG']=$ftype['#UNKNOWN#']['img'];
						
						//Optionen
						if ( $apx->user->has_right('mediamanager.copy') ) $filedata[$obj]['OPTIONS'].='<a href="action.php?action=mediamanager.copy&amp;file='.$filepath.'&amp;module='.$this->module.'"><img src="design/copy.gif" title="'.$apx->lang->get('COPY').'" alt="'.$apx->lang->get('COPY').'" style="vertical-align:middle;" /></a>'; else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
						if ( $apx->user->has_right('mediamanager.move') && $ftype[$extension]['special']!="undel" ) $filedata[$obj]['OPTIONS'].='<a href="action.php?action=mediamanager.move&amp;file='.$filepath.'&amp;module='.$this->module.'"><img src="design/move.gif" title="'.$apx->lang->get('MOVE').'" alt="'.$apx->lang->get('MOVE').'" style="vertical-align:middle;" /></a>'; else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
						if ( $apx->user->has_right('mediamanager.rename') && $ftype[$extension]['special']!="undel" ) $filedata[$obj]['OPTIONS'].='<a href="action.php?action=mediamanager.rename&amp;file='.$filepath.'&amp;module='.$this->module.'"><img src="design/rename.gif" title="'.$apx->lang->get('RENAME').'" alt="'.$apx->lang->get('RENAME').'" style="vertical-align:middle;" /></a>'; else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
						if ( $apx->user->has_right('mediamanager.del') && $ftype[$extension]['special']!="undel" ) $filedata[$obj]['OPTIONS'].='<a href="action.php?action=mediamanager.del&amp;file='.$filepath.'&amp;module='.$this->module.'"><img src="design/del.gif" title="'.$apx->lang->get('CORE_DEL').'" alt="'.$apx->lang->get('CORE_DEL').'" style="vertical-align:middle;" /></a>'; else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
						$filedata[$obj]['OPTIONS'].='&nbsp;';
						if ( $apx->user->has_right('mediamanager.thumb') && in_array($extension,array('GIF','JPG','JPEG','JPE','PNG')) ) $filedata[$obj]['OPTIONS'].='<a href="action.php?action=mediamanager.thumb&amp;file='.$filepath.'&amp;module='.$this->module.'"><img src="design/pic.gif" title="'.$apx->lang->get('THUMB').'" alt="'.$apx->lang->get('THUMB').'" style="vertical-align:middle;" /></a>'; else $filedata[$obj]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
					}
				}
			}
		}
	}
	
	$dirlist='<option value=""'.iif($_REQUEST['dir']=='',' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
	$dirlist.=$this->listtree();
	
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
	$apx->tmpl->assign('DIRLIST',$dirlist);
	$apx->tmpl->assign('FILE',$filedata);
	
	$apx->tmpl->parse('search');
	save_index($_SERVER['REQUEST_URI']);
}


//Verzeichnisse durchsuchen
function searchtree($string,$dir,$found=array()) {
	$foundfiles=array();
	$subdirs=array();
	
	$listdir=opendir(BASEDIR.getpath('uploads').$dir);
	while ( $file=readdir($listdir) ) {
		if ( $file=='.' || $file=='..' ) continue;
		if ( is_dir(BASEDIR.getpath('uploads').iif($dir,$dir.'/',$dir).$file) ) $subdirs[]=$file;
		elseif ( preg_match('#'.$string.'#siU',$file) ) $foundfiles[]=$file;
	}
	
	sort($subdirs);
	sort($foundfiles);
	
	//Gefundene Dateien der Liste hinzufügen
	if ( count($foundfiles) ) {
		$found[$dir]=$foundfiles;
	}
	
	//Unterordner durchsuchen
	foreach ( $subdirs AS $subdir ) {
		$found=$this->searchtree($string,iif($dir,$dir.'/',$dir).$subdir,$found);
	}
	
	return $found;
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//***************************** Inline-Screens *****************************
function inline() {
	global $set,$apx,$db;	
	$_REQUEST['mid']=(int)$_REQUEST['mid'];
	
	//Aktionen ausführen
	if ( $_REQUEST['upload'] ) $this->inline_upload();
	if ( $_REQUEST['delpic'] ) $this->inline_del();
	if ( $_REQUEST['editpic'] ) $this->inline_edit();
	
	//Template-Voreinstellungen
	$apx->tmpl->loaddesign('mediamanager_inline');
	$apx->tmpl->assign('MID',$_REQUEST['mid']);
	$apx->tmpl->assign('HASH',$_REQUEST['hash']);
	$apx->tmpl->assign('FIELDS',$_REQUEST['fields']);
	
	//Inserts
	$pp=array();
	if ( $_REQUEST['fields'] ) {
		$pp=explode(',',$_REQUEST['fields']);
		foreach ( $pp AS $one ) {
			$insertdata[]['ID']=$one;
		}
	}
	
	//Where
	if ( $_REQUEST['mid'] ) $where="mid='".$_REQUEST['mid']."'";
	else $where="hash='".addslashes($_REQUEST['hash'])."'";
	
	//Screens auslesen
	$data=$db->fetch("SELECT * FROM ".PRE."_inlinescreens WHERE ( module='".addslashes($_REQUEST['module'])."' AND ".$where." )");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$screendata[$i]['ID']=$res['id'];
			$screendata[$i]['PICTURE']=HTTPDIR.getpath('uploads').$res['picture'];
			$screendata[$i]['POPUP']=iif($res['popup'],HTTPDIR.getpath('uploads').$res['popup']);
			$screendata[$i]['TEXT']=compatible_hsc($res['text']);
			$screendata[$i]['ALIGN']=$res['align'];
		}
	}
	
	$apx->tmpl->assign('INSERT',$insertdata);
	$apx->tmpl->assign('SCREEN',$screendata);
	$apx->tmpl->parse('inline');
}


//Datei hochladen & speichern
function inline_upload() {
	global $set,$db,$apx;
	$file=$_FILES['image'];
	
	//Prüfen ob eine Datei hochgeladen wurde, falls ja verschieben
	if ( !$file['tmp_name'] ) return;
	if ( !is_uploaded_file($file['tmp_name']) ) return;
	if ( !checkToken() ) { printInvalidToken(); exit; }
	$ext=strtolower($this->mm->getext($file['name']));
	if ( $ext=='gif' ) $ext='jpg';
	$tempfile=md5(microtime()).'.'.$ext;
	$this->mm->uploadfile($file,'inline',$tempfile);
	
	//Nächste ID auslesen
	$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_inlinescreens'");
	$now=time(); //Einheitliche Upload-Zeit, auch wenns nicht stimmt
	
	$newname='pic'.'-'.($tblinfo['Auto_increment']).'.'.$ext;
	$newfile='inline/'.$newname;
	$popname='pic'.'-'.($tblinfo['Auto_increment']).'-popup.'.$ext;
	$popfile='inline/'.$popname;
	
	//Bild einlesen
	require_once(BASEDIR.'lib/class.image.php');
	$img=new image;
	
	////// NORMALES BILD
	list($picture,$picturetype)=$img->getimage('inline/'.$tempfile);
	
	//Skalieren
	if ( $picture!==false && ( $_POST['size_x'] || $_POST['size_y'] ) ) {
		$scaled=$img->resize(
			$picture,
			$_POST['size_x'],
			$_POST['size_y'],
			$set['mediamanager']['quality_resize'],
			0
		);
		
		if ( $scaled!=$picture ) imagedestroy($picture);
		$picture=$scaled;
	}
	
	//Wasserzeichen einfügen
	if ( $picture!==false && $set['mediamanager']['watermark'] && $_POST['watermark'] ) {
		$watermarked=$img->watermark(
			$picture,
			$set['mediamanager']['watermark'],
			$set['mediamanager']['watermark_position'],
			$set['mediamanager']['watermark_transp']
		);
		
		if ( $watermarked!=$picture ) imagedestroy($picture);
		$picture=$watermarked;
	}
	
	//Bild erstellen
	$img->saveimage($picture,$picturetype,$newfile);
	imagedestroy($picture);
	
	
	////// POPUP-BILD
	if ( $_POST['popup'] ) {
		list($picture,$picturetype)=$img->getimage('inline/'.$tempfile);
		
		//Skalieren
		if ( $picture!==false && ( $_POST['popup_size_x'] || $_POST['popup_size_y'] ) ) {
			$scaled=$img->resize(
				$picture,
				$_POST['popup_size_x'],
				$_POST['popup_size_y'],
				$set['mediamanager']['quality_resize'],
				0
			);
			
			if ( $scaled!=$picture ) imagedestroy($picture);
			$picture=$scaled;
		}
		
		//Wasserzeichen einfügen
		if ( $picture!==false && $set['mediamanager']['watermark'] && $_POST['popup_watermark'] ) {
			$watermarked=$img->watermark(
				$picture,
				$set['mediamanager']['watermark'],
				$set['mediamanager']['watermark_position'],
				$set['mediamanager']['watermark_transp']
			);
			
			if ( $watermarked!=$picture ) imagedestroy($picture);
			$picture=$watermarked;
		}
		
		//Bild erstellen
		$img->saveimage($picture,$picturetype,$popfile);
		imagedestroy($picture);
	}
	
	//Cachefile löschen
	$this->mm->deletefile('inline/'.$tempfile);
	
	$_POST['addtime']=time();
	$_POST['picture']=$newfile;
	if ( $_POST['popup'] ) $_POST['popup']=$popfile;
	else $_POST['popup']=''; 
	
	$db->dinsert(PRE.'_inlinescreens','module,mid,hash,picture,popup,align,text,addtime');
	logit('MEDIAMANAGER_INLINE','ID #'.$db->insert_id());
	printJSRedirect('action.php?action=mediamanager.inline&module='.$_REQUEST['module'].'&mid='.$_REQUEST['mid'].'&hash='.$_REQUEST['hash'].'&fields='.$_REQUEST['fields']);
	exit;
}


//Eintrag bearbeiten
function inline_edit() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) return;
	if ( !checkToken() ) { printInvalidToken(); exit; }
	
	$db->query("UPDATE ".PRE."_inlinescreens SET text='".addslashes($_POST['text'])."',align='".addslashes($_POST['align'])."' WHERE ( module='".addslashes($_REQUEST['module'])."' AND id='".$_REQUEST['id']."' )");
	printJSRedirect('action.php?action=mediamanager.inline&module='.$_REQUEST['module'].'&mid='.$_REQUEST['mid'].'&hash='.$_REQUEST['hash'].'&fields='.$_REQUEST['fields']);
	exit;
}


//Eintrag löschen
function inline_del() {
	global $set,$apx,$db;	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) return;
	if ( !checkToken() ) { printInvalidToken(); exit; }
	
	list($picture,$popup)=$db->first("SELECT picture,popup FROM ".PRE."_inlinescreens WHERE ( module='".addslashes($_REQUEST['module'])."' AND id='".$_REQUEST['id']."' ) LIMIT 1");
	if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
	if ( $popup && file_exists(BASEDIR.getpath('uploads').$popup) ) $this->mm->deletefile($popup);
	
	$db->query("DELETE FROM ".PRE."_inlinescreens WHERE ( module='".addslashes($_REQUEST['module'])."' AND id='".$_REQUEST['id']."' )");
	printJSRedirect('action.php?action=mediamanager.inline&module='.$_REQUEST['module'].'&mid='.$_REQUEST['mid'].'&hash='.$_REQUEST['hash'].'&fields='.$_REQUEST['fields']);
	exit;
}


} //END CLASS


?>