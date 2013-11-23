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


//Sektionen
$selsec = $apx->session->get('section');
$secdata = array();
foreach ( $apx->sections AS $id => $section ) {
	$secdata[] = array(
		'ID' => $id,
		'TITLE' => compatible_hsc($section['title']),
		'SELECTED' => ($selsec==$id)
	);
}
$apx->tmpl->assign('SECTION', $secdata);

//Navigation
$apx->tmpl->assign('NAVI',$html->navi());

$apx->tmpl->parse('navi','/');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('includes/_end.php');  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>