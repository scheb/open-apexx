<?php

//Produkt-Modul wird benötigt
if ( !$apx->is_module('products') ) {
	filenotfound();
	return;
}

//Include von Produkt-Modul
require(BASEDIR.getmodulepath('products').'pub/collection.php');

?>