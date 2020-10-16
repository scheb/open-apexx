<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function generate_release($ele) {
	if ( $ele['month'] && $ele['year'] ) unset($ele['quater']);
	if ( $ele['quater'] ) unset($ele['day'],$ele['month']);
	
	//Variante 1: Datum
	if ( $ele['day'] && $ele['month'] && $ele['year'] ) {
		$reldata=array(
			'day' => $ele['day'],
			'month' => $ele['month'],
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],$ele['month'],$ele['day']);
	}
	
	//Variante 2: Monat
	elseif ( $ele['month'] && $ele['year'] ) {
		$reldata=array(
			'month' => $ele['month'],
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],$ele['month'],40); //Ende des Monats
	}
	
	//Variante 3: Quartal
	elseif ( $ele['quater'] && $ele['year'] ) {
		$reldata=array(
			'quater' => $ele['quater'],
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],$ele['quater']*3,50); //Ende des Quartals
	}
	
	//Variante 4: Jahreszahl
	else {
		$reldata=array(
			'year' => $ele['year']
		);
		$relstamp=sprintf('%04d%02d%02d',$ele['year'],12,60); //Ende des Jahres
	}
	
	if ( isset($ele['system']) ) return array($reldata,$relstamp,$ele['system']);
	else return array($reldata,$relstamp);
}


?>