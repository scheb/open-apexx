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


//CKEditor-Funcnum
if ( $_REQUEST['CKEditorFuncNum'] ) {
	$apx->session->set('CKEditorFuncNum', $_REQUEST['CKEditorFuncNum']);
}


if ( $apx->user->info['userid'] ) {
	$apx->tmpl->loaddesign('blank');
	$apx->tmpl->parse('mediamanager','/');
}
else {
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: action.php?action=user.login');
	exit;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_end.php');  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>