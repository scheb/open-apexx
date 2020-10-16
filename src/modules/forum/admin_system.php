<?php 

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