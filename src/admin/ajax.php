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


$apx->tmpl->loaddesign('blank');


list($module, $func) = explode('.',$_REQUEST['action'], 2);
if ( file_exists(BASEDIR.getpath('module',array('MODULE'=>$module)).'admin_ajax.php') ) {
	include_once(BASEDIR.getpath('module',array('MODULE'=>$module)).'admin_ajax.php');
	
	$call = $func;
	if ( function_exists($call) ) {
		$call();
	}
	else {
		echo 'function does not exist!';
	}
}
else {
	echo 'ajax-file does not exist!';
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_end.php');  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>