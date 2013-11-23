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

#
# Diverse Funktionen (Admin)
# ==========================
#

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function checkToken() {
	global $apx;
	$token = $apx->session->get('sectoken');
	if ( $_REQUEST['sectoken']==$token ) {
		return true;
	}
	else {
		return false;
	}
}



//String für Javascript Escapen
function jsString($text, $type = '"') {
	if ( $type=='"' ) {
		return strtr($text, array('"' => '\\"', '\\' => '\\\\', "\n" => '\\n', "\r" => ''));
	}
	elseif ( $type=="'" ) {
		return strtr($text, array("'" => "\\'", '\\' => '\\\\', "\n" => '\\n', "\r" => ''));
	}
	else {
		return strtr($text, array('"' => '\\"', "'" => "\\'", '\\' => '\\\\', "\n" => '\\n', "\r" => ''));
	}
}



////////////////////////////////////////////////////////////////////////////////// -> INFO

//Info beim Formular-Post ausgebe
function printInfo($text) {
	global $apx, $set;
	$apx->tmpl->loaddesign('blank');
	echo '<meta http-equiv="content-Type" content="text/html; charset='.$set['main']['charset'].'" />';
	echo '<script type="text/javascript"> parent.displayInfo("'.jsString($text).'"); parent.scrollTo(0, 0); </script>';
}



//Info: Angaben fehlen
function infoInvalidToken() {
	global $apx;
	printInfo($apx->lang->get('CORE_INVALIDTOKEN'));
}



//Ungültiges Token
function printInvalidToken() {
	global $apx;
	message($apx->lang->get('CORE_INVALIDTOKEN'));
}



//Info: Angaben fehlen
function infoNotComplete() {
	global $apx;
	printInfo($apx->lang->get('CORE_BACK'));
}



//JS-Redirect ausgeben
function printJSRedirect($url) {
	global $apx;
	$apx->tmpl->loaddesign('blank');
	echo '<script type="text/javascript"> parent.location.href = "'.$url.'"; </script>';
}



//JS-Reload ausgeben
function printJSReload() {
	global $apx;
	$apx->tmpl->loaddesign('blank');
	echo '<script type="text/javascript"> parent.location.href = parent.location.href; </script>';
}



//Object aus dem Overlay heraus aktualisieren
function printJSUpdateObject($id, $code) {
	echo '<script type="text/javascript"> parent.parent.ObjectToolboxManager.get('.$id.').updateObject("'.jsString($code).'"); </script>';
}



//Message senden
function messageOverlay($text,$link=false) {
	global $set,$db,$apx;
	$apx->tmpl->assign_static('OVERLAY', true);
	message($text,$link);
}



//Message aus Template senden
function tmessageOverlay($file,$input=array(),$dir=false) {
	global $set,$db,$apx;
	$apx->tmpl->assign_static('OVERLAY', true);
	tmessage($file,$input,$dir);
}



////////////////////////////////////////////////////////////////////////////////// -> OPTION-BUTTONS

//HTML für Options-Button erzeugen
function optionHTML($icon, $actionid, $params, $title = false) {
	global $apx;
	if ( !$title ) {
		$title = $apx->lang->get('TITLE_'.strtoupper($apx->module()).'_'.strtoupper($apx->action()));
	}
	return '<a href="action.php?action='.$actionid.'&amp;'.compatible_hsc($params).'" title="'.$title.'"><img src="design/'.$icon.'" alt="'.$title.'" /></a>';
}



//HTML für Options-Button erzeugen (Overlay)
function optionHTMLOverlay($icon, $actionid, $params, $title = false) {
	static $modules;
	global $reg, $apx;
	if ( !$title ) {
		$title = $apx->lang->get('TITLE_'.strtoupper($apx->module()).'_'.strtoupper($apx->action()));
	}
	return '<a href="javascript:MessageOverlayManager.createLayer(\'action.php?action='.$actionid.'&amp;'.compatible_hsc($params).'\');" title="'.$title.'"><img src="design/'.$icon.'" alt="'.$title.'" /></a>';
}



////////////////////////////////////////////////////////////////////////////////// -> STRINGS

//Tag/Zeit Wählen
function choosetime($id,$empty=0,$sel=0) {
	global $apx;
	if ( $sel>0 ) $date=getdate($sel-TIMEDIFF);
	
	//JS
	$string.='<script type="text/javascript" src="../lib/yui/calendar/calendar-min.js"></script>';
	$string.='<script type="text/javascript" src="../lib/yui/container/container_core-min.js"></script>';
	$string.='<script type="text/javascript" src="../lib/yui/element/element-min.js"></script>';
	$string.='<script type="text/javascript" src="../lib/javascript/calendarselection.js"></script>';
	
	//Tage
	$string.='<select name="t_day_'.$id.'">'.iif($empty,'<option value=""'.iif(!isset($date['mday']),' selected="selected"').'></option>');
	for ($i=1; $i<=31; $i++ ) $string.='<option value="'.$i.'"'.iif($date['mday']==$i,' selected="selected"').'>'.sprintf("%02.d",$i).'</option>';
	$string.='</select>. ';
	
	//Monate
	$string.='<select name="t_mon_'.$id.'">'.iif($empty,'<option value=""'.iif(!isset($date['mon']),' selected="selected"').'></option>');
	$string.='<option value="1"'.iif($date['mon']==1,' selected="selected"').'>'.$apx->lang->get('MONTH_JAN').'</option>';
	$string.='<option value="2"'.iif($date['mon']==2,' selected="selected"').'>'.$apx->lang->get('MONTH_FEB').'</option>';
	$string.='<option value="3"'.iif($date['mon']==3,' selected="selected"').'>'.$apx->lang->get('MONTH_MAR').'</option>';
	$string.='<option value="4"'.iif($date['mon']==4,' selected="selected"').'>'.$apx->lang->get('MONTH_APR').'</option>';
	$string.='<option value="5"'.iif($date['mon']==5,' selected="selected"').'>'.$apx->lang->get('MONTH_MAY').'</option>';
	$string.='<option value="6"'.iif($date['mon']==6,' selected="selected"').'>'.$apx->lang->get('MONTH_JUN').'</option>';
	$string.='<option value="7"'.iif($date['mon']==7,' selected="selected"').'>'.$apx->lang->get('MONTH_JUL').'</option>';
	$string.='<option value="8"'.iif($date['mon']==8,' selected="selected"').'>'.$apx->lang->get('MONTH_AUG').'</option>';
	$string.='<option value="9"'.iif($date['mon']==9,' selected="selected"').'>'.$apx->lang->get('MONTH_SEP').'</option>';
	$string.='<option value="10"'.iif($date['mon']==10,' selected="selected"').'>'.$apx->lang->get('MONTH_OCT').'</option>';
	$string.='<option value="11"'.iif($date['mon']==11,' selected="selected"').'>'.$apx->lang->get('MONTH_NOV').'</option>';
	$string.='<option value="12"'.iif($date['mon']==12,' selected="selected"').'>'.$apx->lang->get('MONTH_DEC').'</option>';
	$string.='</select> ';
	
	//Jahre
	$string.='<select name="t_year_'.$id.'">'.iif($empty,'<option value=""'.iif(!isset($date['year']),' selected="selected"').'></option>');
	for ($i=2000; $i<=2020; $i++ ) $string.='<option value="'.$i.'"'.iif($date['year']==$i,' selected="selected"').'>'.sprintf('%02.d',$i).'</option>';
	$string.='</select> - ';
	
	//Ab hier === damit "kein Wert" als "ungleich" 0 erkannt wird
	
	//Stunden
	$string.='<select name="t_hour_'.$id.'">'.iif($empty,'<option value=""'.iif(!isset($date['hours']),' selected="selected"').'></option>');
	for ($i=0; $i<=23; $i++ ) $string.='<option value="'.$i.'"'.iif($date['hours']===$i,' selected="selected"').'>'.sprintf('%02.d',$i).'</option>';
	$string.='</select>:';
	
	//Minuten
	$string.='<select name="t_min_'.$id.'">'.iif($empty,'<option value=""'.iif(!isset($date['minutes']),' selected="selected"').'></option>');
	for ($i=0; $i<=59; $i++ ) $string.='<option value="'.$i.'"'.iif($date['minutes']===$i,' selected="selected"').'>'.sprintf('%02.d',$i).'</option>';
	$string.='</select>:';
	
	//Sekunden
	$string.='<select name="t_sec_'.$id.'">'.iif($empty,'<option value=""'.iif(!isset($date['seconds']),' selected="selected"').'></option>');
	for ($i=0; $i<=59; $i++ ) $string.='<option value="'.$i.'"'.iif($date['seconds']===$i,' selected="selected"').'>'.sprintf('%02.d',$i).'</option>';
	$string.='</select> Uhr';
	
	//JS Init
	$string.='<script language="JavaScript" type="text/javascript">';
	$string.='yEvent.onDOMReady(function() { ';
	$string.='	var form = document.forms[0]; ';
	$string.='	new CalendarSelection({day: form[\'t_day_'.$id.'\'], month: form[\'t_mon_'.$id.'\'], year: form[\'t_year_'.$id.'\']}, \'t_'.$id.'\');';
	$string.='});';
	$string.='</script>';
	
	return $string;
}

//Aus einem choosetime()-Feld einen Timestamp generieren
function maketime($id) {
	if ( $_POST['t_hour_'.$id]===''
	|| $_POST['t_min_'.$id]===''
	|| $_POST['t_sec_'.$id]===''
	|| $_POST['t_mon_'.$id]===''
	|| $_POST['t_day_'.$id]===''
	|| $_POST['t_year_'.$id]===''
	|| !isset($_POST['t_hour_'.$id])
	|| !isset($_POST['t_min_'.$id])
	|| !isset($_POST['t_sec_'.$id])
	|| !isset($_POST['t_mon_'.$id])
	|| !isset($_POST['t_day_'.$id])
	|| !isset($_POST['t_year_'.$id])
	) return 0;
	
	$time=mktime(
		$_POST['t_hour_'.$id],
		$_POST['t_min_'.$id],
		$_POST['t_sec_'.$id],
		$_POST['t_mon_'.$id],
		$_POST['t_day_'.$id],
		$_POST['t_year_'.$id]
	)+TIMEDIFF;
	
	return $time;
}

//Aus einem timestamp $_POST Vars für choosetime() generieren
function maketimepost($id,$time) {
	$date=getdate($time-TIMEDIFF);
	$_POST['t_day_'.$id]=$date['mday'];
	$_POST['t_mon_'.$id]=$date['mon'];
	$_POST['t_year_'.$id]=$date['year'];
	$_POST['t_hour_'.$id]=$date['hours'];
	$_POST['t_min_'.$id]=$date['minutes'];
	$_POST['t_sec_'.$id]=$date['seconds'];
}


//WYSIWYG-Editor laden
function mkeditor($fields) {
	return;
	global $apx;
	if ( !$apx->user->info['admin_editor'] ) return;
	if ( is_string($fields) ) $fields=array($fields);

	$code=<<<CODE
<script type="text/javascript" src="editor/fckeditor.js"></script>
<script type="text/javascript">

window.onload = function() {
var sBasePath='editor/';
var ei=1;
CODE;

	foreach ( $fields AS $field ) {
		$code.=<<<CODE
var editor_{$field} = new FCKeditor('{$field}','100%',getobject('{$field}').rows*16+'px');
editor_{$field}.BasePath = sBasePath;
editor_{$field}.ReplaceTextarea();
editors[ei++]='{$field}';

CODE;
	}

	$code.=<<<CODE
}

</script>
CODE;
	
	$apx->tmpl->set_static('JS_FOOTER');
	$apx->tmpl->extend('JS_FOOTER',$code);
}


//Templates wählen
function mktemplates($fields) {
	global $db,$apx;
	return;
	if ( is_string($fields) ) $fileds=array($fields);
	
	$sourcereplace=array(
		"'" => "\\"."'",
		"\r" => '',
		"\n" => '\n'
	);
	
	$data=$db->fetch("SELECT * FROM ".PRE."_templates ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$options.='<option value="'.$res['id'].'">'.$res['title'].'</option>';
			$source.="templates[".$res['id']."] = '".strtr($res['code'],$sourcereplace)."';\n";
		}
		
		foreach ( $fields AS $one ) {
			$code=$apx->lang->get('CORE_INSERTTEMPLATE').': <select name="tmplid_'.$one.'" onchange="insert_template(this,\''.$one.'\'); "><option value="">'.$apx->lang->get('CORE_CHOOSETEMPLATE').'</option>'.$options.'</select>';
			$apx->tmpl->assign('TEMPLATES_CHOOSE_'.strtoupper($one),$code);
		}
		
		$footercode=<<<CODE
<script language="JavaScript" type="text/javascript">
<!--

var templates = new Array();
{$source}

//-->
</script>
CODE;
		
		$apx->tmpl->set_static('JS_FOOTER');
		$apx->tmpl->extend('JS_FOOTER',$footercode);
	}
	
	//Keine Vorlagen vorhanden
	else {
		foreach ( $fields AS $one ) {
			$apx->tmpl->assign('TEMPLATES_CHOOSE_'.strtoupper($one),'');
		}
	}

}



////////////////////////////////////////////////////////////////////////////////// -> FILES

//Copy picture with thumbnail
function copy_with_thumbnail($oldImage, $newImage) {
	$oldPoppic = str_replace('-thumb.','.',$oldImage);
	$newPoppic = str_replace('-thumb.','.',$newImage);
	
	if ( $oldImage && file_exists(BASEDIR.getpath('uploads').$oldImage) ) {
		copy(BASEDIR.getpath('uploads').$oldImage, BASEDIR.getpath('uploads').$newImage);
		if ( $oldPoppic && file_exists(BASEDIR.getpath('uploads').$oldPoppic) ) {
			copy(BASEDIR.getpath('uploads').$oldPoppic, BASEDIR.getpath('uploads').$newPoppic);
		}
		return true;
	}
	return false;
}



////////////////////////////////////////////////////////////////////////////////// -> PROTOKOLL

//Protokoll-Eintrag machen
function logit($text,$affect=false) {
	global $db,$apx;
	
	$text=strtoupper($text);
	if ( $affect===false ) $affect='';
	
	$db->query("INSERT INTO ".PRE."_log VALUES ('".date('Y/m/d H:i:s',time()-TIMEDIFF)."','".$apx->user->info['userid']."','".get_remoteaddr()."','".addslashes('{LOG_'.$text.'}')."','".addslashes($affect)."')");
}



////////////////////////////////////////////////////////////////////////////////// -> INDEX-SEITEN DER MODULE SPEICHERN

//Index-URL speichern
function save_index($url,$action=false) {
	global $apx;
	if ( $action===false ) $action=$apx->active_module.'.'.$apx->active_action;
	$apx->session->set('indexpage_'.$action, $url);
}


//Index-URL laden
function get_index($action) {
	global $apx;
	
	$url = $apx->session->get('indexpage_'.$action);
	if ( $url ) {
		return $url;
	}
	
	return $_SERVER['PHP_SELF'].'?action='.$action;
}



////////////////////////////////////////////////////////////////////////////////// -> BAUM AUFLISTEN

//Baumstruktur parsen
function parse_tree($data,$img=false) {
	if ( !count($data) ) return array(array(),array());
	
	$follow=parse_tree_follow($data);
	$space=parse_tree_space($follow,$img);
	
	return array($space,$follow);
}


//Nachfolger und Vorgänger im Baum bestimmen
function parse_tree_follow($data) {
	$follow=array();
	$prev_in_level=array();
	
	foreach ( $data AS $res ) {
		$level=$res['level'];
		$id=$res['id'];
		
		//Alle höheren Level löschen, wenn ein Level tiefer als Vorgängerlevel
		if ( $level<$lastlevel ) {
			for ( $li=$level+1; ; $li++ ) {
				if ( !isset($prev_in_level[$li]) ) break;
				unset($prev_in_level[$li]);
			}
		}
		
		//Vorgänger im gleichen Level bestimmen
		$prev=$prev_in_level[$level];
		$mother=$prev_in_level[$level-1];
		
		//Aktuelle ID
		$follow[$id]=array(
			'mother' => $mother,
			'level' => $level,
			'prev' => iif($prev,1,0),
			'next' => 0
		);
		
		//Vorgänger -> hat einen Nachfolger
		if ( $prev ) {
			$follow[$prev]['next']=1;
		}
		
		$lastlevel=$level;
		$prev_in_level[$level]=$id;
	}
	
	return $follow;
}


//Einrückzeichen im Baum generieren
function parse_tree_space($follow,$img) {
	$space=array();
	
	if ( !is_array($img) ) {
		$img=array(
			'space' => '<img src="design/node_space.gif" alt="" style="vertical-align:middle;" />',
			'node' => '<img src="design/node.gif" alt="" style="vertical-align:middle;" />',
			'node_end' => '<img src="design/node_end.gif" alt="" style="vertical-align:middle;" />',
			'line' => '<img src="design/node_line.gif" alt="" style="vertical-align:middle;" />',
		);
	}
	
	foreach ( $follow AS $id => $info ) {
		if ( $info['level']==1 ) {
			$space[$id]='';
			continue;
		}
		
		$spacecode='';
		
		//Weiterführende Linien höherer Level
		$mother=$info['mother'];
		while ( true ) {
			if ( !isset($follow[$mother]) || $follow[$mother]['level']==1 ) break; //Beenden, wenn kein Mutterelement existiert
			
			$motherinfo=$follow[$mother];
			$mother=$motherinfo['mother'];
			
			if ( $motherinfo['next'] ) $spacecode=$img['line'].$spacecode;
			else $spacecode=$img['space'].$spacecode;
		}
		
		//Node-Symbol
		if ( $info['next'] ) $spacecode.=$img['node'];
		else $spacecode.=$img['node_end'];
		
		$space[$id]=$spacecode;
	}
	
	return $space;
}



////////////////////////////////////////////////////////////////////////////////// -> NACHRICHTEN


//Info-Text senden
function info($text) {
	printInfo($text);
}


//Quicklink ausgeben
function quicklink($action,$file='action.php',$addurl='') {
	echo '<p class="slink">'.quicklink_generate($action,$file,$addurl).'</p>';
}


//Mehrere Quicklinks
$quicklink_dump='';
function quicklink_multi($action,$file='action.php',$addurl='') {
	global $quicklink_dump;
	$quicklink_dump.=iif($quicklink_dump,'<br />').quicklink_generate($action,$file,$addurl);
}


//Quicklink zum Index
function quicklink_index($action) {
	global $quicklink_dump,$apx;
	if ( !$apx->user->has_right($action) ) return;
	
	$quicklink_dump.=iif($quicklink_dump,'<br />').'&raquo; <a href="'.get_index($action).'">'.$apx->lang->get('TITLE_'.strtoupper(str_replace('.','_',$action))).'</a>';
}


//Mehrere ausgeben
function quicklink_out() {
	global $quicklink_dump;
	if ( !$quicklink_dump ) return;
	echo '<p class="slink">'.$quicklink_dump.'</p>';
	$quicklink_dump='';
}


//Quicklink generieren
function quicklink_generate($action,$file='action.php',$addurl='') {
	global $apx;
	if ( !$apx->user->has_right($action) ) return;
	
	$out='&raquo; <a href="'.$file.'?action='.$action.iif($addurl,'&amp;'.$addurl).'">';
	$out.=$apx->lang->get('TITLE_'.strtoupper(str_replace('.','_',$action)));
	$out.='</a>';
	
	return $out;
}


//Mediamanager-Link
function mediamanager($module=false) {
	global $apx;
	if ( !$apx->user->has_right('mediamanager.upload') ) return;
	echo '<p class="slink">&raquo; <a href="javascript:openmm(\'mediamanager.php?action=mediamanager.index'.iif($module,'&amp;module='.$module).'\')">'.$apx->lang->get('CORE_MEDIAMANAGER').'</a></p>';
}



////////////////////////////////////////////////////////////////////////////////// -> SEITENZAHLEN + LETTERS

function pages($link,$count,$epp=0,$varname='p') {
	global $set;
	$count=(int)$count;
	$epp=(int)$epp;
	
	//Variablen vorbereiten
	$_REQUEST[$varname]=(int)$_REQUEST[$varname];
	if ( strpos($link,'?') ) $sticky='&amp;'; else $sticky='?';
	if ( !$epp ) $epp=$set['main']['admin_epp'];
	$tmpl = new tengine;
	
	//Seitenzahlen bereichnen, evtl. REQUEST berichtigen
	$pages=ceil($count/$epp);
	if ( $_REQUEST[$varname]<1 || $_REQUEST[$varname]>$pages ) $_REQUEST[$varname]=1;
	
	//Wenn kein $epp, alle Einträge zeigen -> Seiten (1): [1]
	if ( $epp==0 ) {
		$tmpl->assign('PAGE_COUNT',1);
		$tmpl->assign('PAGE',array(array(
			'NUMBER'=>1,
			'LINK'=>'#'
		)));
		$tmpl->parse('pages','/');
		return;
	}
	
	//Wenn es keine Einträge gibt
	if ( !$count ) {
		$tmpl->assign('PAGE_COUNT',0);
		$tmpl->parse('pages','/');
		return;
	}
	
	//Seitenzahlen generieren
	for ( $i=1; $i<=$pages; $i++ ) {
		$pagedata[$i]['NUMBER']=$i;
		$pagedata[$i]['LINK']=$link.$sticky.$varname.'='.$i;
	}
	
	if ( $_REQUEST[$varname]>1 ) $previous=$link.$sticky.$varname.'='.($_REQUEST[$varname]-1);
	if ( $_REQUEST[$varname]<$pages ) $next=$link.$sticky.$varname.'='.($_REQUEST[$varname]+1);
	$first=$link.$sticky.$varname.'=1';
	$last=$link.$sticky.$varname.'='.$pages;
	
	$tmpl->assign('PAGE_PREVIOUS',$previous);
	$tmpl->assign('PAGE_NEXT',$next);
	$tmpl->assign('PAGE_FIRST',$first);
	$tmpl->assign('PAGE_LAST',$last);
	$tmpl->assign('PAGE_COUNT',$pages);
	$tmpl->assign('PAGE_SELECTED',$_REQUEST[$varname]);
	$tmpl->assign('PAGE',$pagedata);
	$tmpl->parse('pages','/');
}


//Buchstaben-Auswahl
function letters($link) {
	global $apx;
	static $letters;
	if ( !isset($letters) ) $letters=array('#','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	if ( strpos($link,'?')!==false ) $sticky='&amp;';
	else $sticky='?';
	
	if ( strpos($link,'{LETTER}')!==false ) $fulllink=str_replace('{LETTER}','0',$link);
	else $fulllink=$link;
	
	$letterdata[]=array(
		'TEXT' => $apx->lang->get('CORE_NOLETTER'),
		'LINK' => $fulllink,
		'SELECTED' => iif(!$_REQUEST['letter'],1,0)
	);
	
	//Buchstaben
	foreach ( $letters AS $letter ) {
		$selected=0;
		if ( $letter=='#' && $_REQUEST['letter']=='spchar' ) $selected=1;
		if ( $letter==$_REQUEST['letter'] ) $selected=1;
		
		if ( strpos($link,'{LETTER}')!==false ) $fulllink=str_replace('{LETTER}',iif($letter=='#','spchar',$letter),$link);
		else $fulllink=$link.$sticky.'letter='.iif($letter=='#',iif($letter=='#','spchar',$letter),$letter);
		
		$letterdata[]=array(
			'TEXT' => strtoupper($letter),
			'LINK' => $fulllink,
			'SELECTED' => $selected
		);
	}
	
	
	$apx->tmpl->assign('LETTER_SELECTED',$_REQUEST['letter']);
	$apx->tmpl->assign('LETTER',$letterdata);
	$apx->tmpl->parse('letters','/');
}



////////////////////////////////////////////////////////////////////////////////// -> MYSQL

//Sektionen filtern
function section_filter($and=true,$fieldname='secid') {
	global $apx,$user;
	
	$sections = $apx->sections;
	$secid = $apx->session->get('section');
	
	/////// Keine Sektionen verwendet -> Alles anzeigen
	if ( !is_array($sections) || !count($sections) ) return iif($and,' AND ')." 1 "; //true
	
	/////// Nur gewählte Sektion
	if ( $secid ) return iif($and,' AND ')." ( ".$fieldname."='all' OR ".$fieldname." LIKE '%|".$secid."|%' ) ";
	
	/////// Alle Sektionen auflisten
	return iif($and, '', '1');
}



//"Sortieren nach" Auswahlliste
function orderstr($info,$link) {
	global $apx;
	
	//Check ob Standard-Sortby definiert
	if ( !is_array($info) || !count($info) ) return '';
	if ( !$info[0] ) echo "WARNING: No default 'sort by' column defined!";
	
	$sort=explode('.',$_REQUEST['sortby']);
	$sort[1]=strtoupper($sort[1]);
	
	//Wenn $sort ein gültiger Sortby-Index ist
	if ( isset($info[$sort[0]]) ) {
		//Wenn Sorttype ungültig
		if ( $sort[1]!="ASC" && $sort[1]!="DESC" ) $sort[1]=$info[$sort[0]][1];
	}
	//Wenn $sort kein gültiger Sortby Index ist -> Standard verwenden
	else $sort=array($info[0],$info[$info[0]][1]);
	
	//Liste machen
	foreach ( $info AS $id => $data ) {
		if ( !$id ) continue;
		if ( $id==$sort[0] ) {
			if ( $sort[1]=='ASC' ) $scache[]='<a href="'.$link.'&amp;sortby='.$id.'.DESC">'.$apx->lang->get($data[2]).'</a> <img src="design/asc.gif" alt="ASC" style="vertical-align:middle;" />';
			else $scache[]='<a href="'.$link.'&amp;sortby='.$id.'.ASC">'.$apx->lang->get($data[2]).'</a> <img src="design/desc.gif" alt="DESC" style="vertical-align:middle;" />';
		}
		else $scache[]='<a href="'.$link.'&amp;sortby='.$id.'.'.$data[1].'">'.$apx->lang->get($data[2]).'</a>';
	}
	
	echo '<p class="sortby">Sortieren nach: '.@implode(" | ",$scache).'</p>';
}



////////////////////////////////////////////////////////////////////////////////// -> ARRAYS


//Array sortieren auf Basis von Info
function array_sort_def($array,$info) {
	//Check ob Standard-Sortby definiert
	if ( !is_array($info) || !count($info) ) return '';
	if ( !$info[0] ) echo 'WARNING: No default "sort by" column defined!';
	
	$sort=explode('.',$_REQUEST['sortby']);
	$sort[1]=strtoupper($sort[1]);
	
	if ( isset($info[$sort[0]]) ) {
		if ( $sort[1]!='ASC' && $sort[1]!='DESC' ) $sort[1]=$info[$sort[0]][1];
		return array_sort($array,$info[$sort[0]][0],$sort[1]);
	} 
	else return array_sort($array,$info[$info[0]][0],$info[$info[0]][1]);
}

?>