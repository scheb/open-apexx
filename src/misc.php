<?php 

define('APXRUN',true);
define('NOSTATS',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////


//Funktionen laden
foreach ( $apx->modules AS $module => $info ) {
	if ( !file_exists(BASEDIR.getmodulepath($module).'misc.php') ) continue;
	include_once(BASEDIR.getmodulepath($module).'misc.php');
}


$call='misc_'.$_REQUEST['action'];
if ( !function_exists($call) ) die('action does not exist!');

//Aktion ausf�hren
$call();


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>