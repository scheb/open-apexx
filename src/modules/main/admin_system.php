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


//Textfeld mit Editor-Eigenschaften
function main_textbox($name=false,$rows=15,$content='',$editor=true,$templates=true) {
	global $set,$apx,$db;
	static $editor_loaded,$templates_loaded,$options;
	if ( !$name ) echo 'TEXTFIELD: MISSING NAME!';
	
	echo '<textarea name="'.$name.'" id="'.$name.'" cols="80" rows="'.$rows.'" class="code" style="width:98%;">'.$content.'</textarea>';
	
	//EDITOR ERZEUGEN
	if ( $apx->user->info['admin_editor'] && $editor ) {
		
		//Editor beim ersten Aufruf initialisiern
		if ( !$editor_loaded ) {
			echo <<<CODE
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script language="JavaScript" type="text/javascript">

var editor_status=1;
var ei=1;
if ( typeof console != 'undefined' ) console.log();

</script>
CODE;
			$editor_loaded=true;
		}
		
		$height=(($rows+3)*12);
		$width=iif($set['main']['textboxwidth'],$set['main']['textboxwidth'].'px','100%');
		
		echo '<script type="text/javascript">
			yEvent.onDOMReady(function() {
				CKEDITOR.replace(\''.$name.'\', {
					height: '.$height.',
					width: \''.$width.'\',
					enterMode: '.($set['main']['entermode']=='br' ? 'CKEDITOR.ENTER_BR' : 'CKEDITOR.ENTER_P');
		if ( $apx->active_module=='newsletter' ) {
			echo ", filebrowserImageBrowseUrl: 'mediamanager.php?module=newsletter:1', filebrowserFlashBrowseUrl: 'mediamanager.php?module=mediamanager:2'";
		}
		echo '		});
				editors[ei++]=\''.$name.'\';
			});
		</script>';
		
	}
	
	
	//TEMPLATE AUSWAHLFELD ERZEUGEN
	if ( !$templates ) return;
	
	//Templates beim ersten Aufruf initialisieren
	if ( !$templates_loaded ) {
		$sourcereplace=array(
			"'" => "\\"."'",
			"\r" => '',
			"/" => '\\/',
			"\n" => '\n'
		);
		
		$data=$db->fetch("SELECT * FROM ".PRE."_templates ORDER BY title ASC");
		if ( !count($data) ) return;
		
		foreach ( $data AS $res ) {
			$options.='<option value="'.$res['id'].'">'.$res['title'].'</option>';
			$source.="templates[".$res['id']."] = '".strtr($res['code'],$sourcereplace)."';\n";
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
		$templates_loaded=true;
	}
		
	echo '<div>'.$apx->lang->get('CORE_INSERTTEMPLATE').': <select name="tmplid_'.$name.'" onchange="insert_template(this,\''.$name.'\'); "><option value="">'.$apx->lang->get('CORE_CHOOSETEMPLATE').'</option>'.$options.'</select></div>';
}



//Sektionen ausgeben
function main_sections($selected) {
	global $set,$apx,$db;
	
	//Sektionen auflisten
	if ( !is_array($apx->sections) && count($apx->sections) ) return;
	
	//Ausgewählte Sektionen
	if ( !is_array($selected) ) $selected=array();
	
	//Alle Sektionen
	echo '<option value="all" style="font-weight:bold;"'.iif(in_array('all',$selected),' selected="selected"').'>'.$apx->lang->get('ALLSEC').'</option>';
	
	//Auflisten
	foreach ( $apx->sections AS $id => $info ) {
		echo '<option value="'.$id.'"'.iif(in_array($id,$selected),' selected="selected"').'>'.replace($info['title']).'</option>';
	}
}



//Tag-IDs von String bekommen
function produceTagIds($string) {
	$tagids = array();
	
	//Tags aus String auslesen
	$tags = explode(',', $string);
	$tags = array_map('trim', $tags);
	
	//Tags produzieren
	foreach ( $tags AS $tag ) {
		if ( !$tag ) continue;
		$id = getTagId($tag);
		if ( !$id ) {
			$id = createTag($tag);
		}
		$tagids[] = $id;
	}
	return $tagids;
}



//Tag erzeugen und ID zurückgeben
function createTag($tagname) {
	global $db;
	$db->query("INSERT INTO ".PRE."_tags (tag) VALUES ('".addslashes($tagname)."')");
	return $db->insert_id();
}



//ID zu einem Tag auslesen
function getTagId($tagname) {
	global $db;
	list($id) = $db->first("SELECT tagid FROM ".PRE."_tags WHERE tag LIKE '".addslashes_like($tagname)."' LIMIT 1");
	return $id;
}


?>
