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

//Serialize mit Strich
function forum_serialize($array) {
	if ( !count($array) || !is_array($array) ) return '|';
	return '|'.implode('|',$array).'|';
}


//Unserialize mit Strich
function forum_unserialize($string) {
	if ( $string=='|' ) return array();
	if ( $string[0]!='|' || $string[strlen($string)-1]!='|' ) return array();
	$string=substr($string,1,strlen($string)-2);
	$array=explode('|',$string);
	return $array;
}


?>