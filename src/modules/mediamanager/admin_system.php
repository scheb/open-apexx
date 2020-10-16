<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Inlinescreens
function mediamanager_inline($insert='',$id=false) {
	global $set,$db,$apx;
	
	//Felder, in die eingefügt werden darf
	if ( !$insert ) {
		echo 'missing fields!';
		return;
	}
	
	//Umgebungsvariablen
	$module=$apx->module();
	$id=(int)$id;
	$status=(int)$_POST['inline_status'];
	
	//Hash-Wert ermitteln
	if ( !$id && $_POST['inline_hash'] ) $hash=compatible_hsc($_POST['inline_hash']);
	elseif ( !$id ) $hash=compatible_hsc(md5(microtime()));
	
	//Button-Beschriftungen
	$apx->lang->dropaction('mediamanager','inline');
	$lang_open=$apx->lang->get('INLINE_OPEN');
	$lang_close=$apx->lang->get('INLINE_CLOSE');
	
	//Iframe anzeigen, oder nicht anzeigen...
	if ( $status ) $hide_button='display:none;';
	else $hide_iframe='display:none;';
	
	echo <<<CODE
<div id="inline_button" style="{$hide_button}"><input type="button" name="inline_open" value="{$lang_open}" onclick="open_inline();" class="button" /></div>
<div id="inline_iframe" style="{$hide_iframe}">
<div style="padding-bottom:3px;"><input type="button" name="inline_open" value="{$lang_close}" onclick="close_inline();" class="button" /></div>
<iframe src="action.php?action=mediamanager.inline&amp;module={$module}&amp;mid={$id}&amp;hash={$hash}&amp;fields={$insert}" width="98%" height="250" name="inline" frameborder="0" style="width:98%;height:250px;">Sorry, your browser does not support frames!</iframe>
</div>
<script language="JavaScript" type="text/javascript">
<!--

//Inlinescreens öffnen
function open_inline() {
	getobject('inline_button').style.display='none';
	getobject('inline_iframe').style.display='';
	getobject('inline_status').value=1;
}

//Inlinescreens schließen
function close_inline() {
	getobject('inline_button').style.display='';
	getobject('inline_iframe').style.display='none';
	getobject('inline_status').value=0;
}

//-->
</script>
<input type="hidden" name="inline_hash" value="{$hash}" />
<input type="hidden" name="inline_status" id="inline_status" value="{$status}" />
CODE;
}



//Bilder mit dem POST-Hash der ID hinzufügen
function mediamanager_setinline($id) {
	global $set,$db,$apx;
	
	$id=(int)$id;
	$hash=$_POST['inline_hash'];
	$module=$apx->module();
	if ( !$id || !$hash ) return;
	
	$db->query("UPDATE ".PRE."_inlinescreens SET mid='".$id."' WHERE ( module='".addslashes($module)."' AND hash='".addslashes($hash)."' )");
}


?>