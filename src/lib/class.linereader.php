<?php


class LineReader {
	
	var $filepath;
	var $reader = null;
	var $buffer = '';
	var $end;
	var $endlength;
	var $length = 102400;
	
	
	//Konstrukor
	function LineReader($filepath, $end = "\n", $length = 102400) {
		$this->filepath = $filepath;
		if ( is_int($length) && $length>0 ) {
			$this->length = $length;
		}
		if ( strlen($end)==0 ) {
			$end = "\n";
		}
		$this->end = $end;
		$this->endlength = strlen($end);
		$this->openFile();
	}
	
	
	
	//Datei öffnen
	function openFile() {
		$this->reader = fopen($this->filepath, 'r');
		//$this->extendBuffer(); //Erste Daten in Puffer schreiben
	}
	
	
	
	//Datei schließen
	function close() {
		if ( !is_null($this->reader) ) {
			fclose($this->reader);
			$this->reader = null;
		}
	}
	
	
	
	//Puffer auslesen und Treffer zurückgeben
	function getNext() {
		
		//Puffer füllen, bis Treffer
		do {
			if ( ($line = $this->getNextLine())!==false ) {
				return $line;
			}
		}
		while ( $this->extendBuffer() );
		
		//Keine weiteren Treffer => Rest zurückgeben
		if ( $this->buffer ) {
			$line = $this->buffer;
			$this->buffer = '';
			return $line;
		}
		
		//Keine Daten mehr im Puffer => Ende
		else {
			return false;
		}
	}
	
	
	
	//Puffer erweitern
	function extendBuffer() {
		if ( !feof($this->reader) && $chunk = fread($this->reader, $this->length) ) {
			$this->buffer .= $chunk;
			return true;
		}
		else {
			return false;
		}
	}
	
	
	
	//Nächsten Treffer suchen und zurückgeben, Buffer verkleinern
	function getNextLine() {
		if ( ($lineend = strpos($this->buffer, $this->end))!==false ) {
			$line = substr($this->buffer, 0, $lineend);
			$this->buffer = substr($this->buffer, $lineend+$this->endlength);
			return $line;
		}
		else {
			return false;
		}
	}
	
	
	
	//Dateiende erreicht?
	function eof() {
		return !$this->buffer;
	}
	
}



?>