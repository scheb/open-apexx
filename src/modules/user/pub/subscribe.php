<?php

//Forum-Modul muss aktiv sein
if ( !$apx->is_module('forum') ) {
	filenotfound();
	return;
}

if ( in_array($_REQUEST['option'],array('addforum','addthread')) ) {
	require(dirname(__FILE__).'/subscribe_add.php');
}
elseif ( $_REQUEST['option']=='edit' ) {
	require(dirname(__FILE__).'/subscribe_edit.php');
}
elseif ( $_REQUEST['option']=='delete' ) {
	require(dirname(__FILE__).'/subscribe_del.php');
}
else {
	filenotfound();
}

?>