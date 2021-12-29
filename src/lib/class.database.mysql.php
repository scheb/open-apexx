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


class database {

	//Benchmark
	var $benchmark = false;
	var $benchdata = array();
	var $benchtime = 0;

	//Datenbankkonfiguration
	var $conn;
	var $querystring;
	var $quieterror = false;



	////////////////////////////////////////////////////////////////////////////////// -> STARTUP + END

	//Verbindung herstellen
	function __construct($server, $user, $password, $database, $utf8 = false) {
		$this->conn = mysql_connect($server, $user, $password);
		if ( !$this->conn ) {
			error("Verbindung konnte nicht hergestellt werden!<br />MySQL meldet: ".mysql_error($this->conn), 1);
			return;
		}
		if ( !mysql_select_db($database, $this->conn) ) {
			error("Konnte die Datenbank ".$database." nicht ausw&auml;hlen!<br />MySQL meldet: ".mysql_error($this->conn),1);
		}
		if ( $utf8 ) {
			$this->query("SET NAMES 'utf8'");
		}
	}



	//Verbindung schließen
	function close() {
	  @mysql_close($this->conn);

		//Benchmark ausgeben
		if ( $this->benchmark ) {
			echo '<pre>';
			print_r($this->benchdata);
	    echo 'TOTAL: '.sprintf('%1.6f',$this->benchtime);
			echo '</pre>';
		}
	}



	////////////////////////////////////////////////////////////////////////////////// -> INSERT / UPDATE

	//Normale Datenbankanfrage
	function query($query) {
	  $this->querystring = $query;

		$this->bench_start();
	  $result = mysql_query($query, $this->conn);
		$this->bench_end();

		if ( !$result ) error($this->error());
		else return new mysqlresult($result);

	  return false;
	}



	//DnyQuery Cols
	function mkcols($data) {
		$p = explode(',',trim($data));
		$out = array();
		foreach ( $p AS $onecol ) {
			$out[] = trim($onecol);
		}
		return $out;
	}



	//Dynamisches UPDATE
	function dupdate($table, $postcols, $conditions="") {
		$getcols = $this->mkcols($postcols);

		$info = $this->fetch("SHOW COLUMNS FROM ".$table);
		if ( !count($info) ) error('Anforderung der Tabellen-Spalten fehlgeschlagen<br />Tabelle: '.$table);

		$colcache = array();
		foreach ( $info AS $thecol ) {
			if ( in_array($thecol['Field'], $getcols) /*&& isset($_POST[$thecol['Field']])*/ ) {
				$colcache[] = $table.".".$thecol['Field']."='".addslashes($_POST[$thecol['Field']])."'";
			}
		}

		$commands = implode(", ",$colcache);

		if ( $this->query("UPDATE ".$table." SET ".$commands." ".$conditions) ) return true;
		return false;
	}



	//Dynamischer INSERT
	function dinsert($table,$postcols) {
		$getcols = $this->mkcols($postcols);

		$info = $this->fetch("SHOW COLUMNS FROM ".$table);
		if ( !count($info) ) error('Anforderung der Tabellen-Spalten fehlgeschlagen<br />Tabelle: '.$table);

		$colcache = array();
		$valcache = array();
		foreach ( $info AS $thecol ) {
			if ( in_array($thecol['Field'], $getcols) /*&& isset($_POST[$thecol['Field']])*/ ) {
				$colcache[] = $table.".".$thecol['Field'];
				$valcache[] = "'".addslashes($_POST[$thecol['Field']])."'";
			}
		}

		$cols = implode(", ",$colcache);
		$values = implode(", ",$valcache);

		if ( $this->query("INSERT INTO ".$table." (".$cols.") VALUES (".$values.")") ) return true;
		return false;
	}



	//Insert durchführen
	function insert($table, $values) {
		$values = array_map('addslashes', $values);
		$queryString = "INSERT INTO ".$table." (`".implode('`, `', array_keys($values))."`) VALUES ('".implode("','", $values)."')";
		$this->query($queryString);
	}



	//Insert durchführen
	function update($table, $values, $where) {
		$values = array_map('addslashes', $values);
		$valueString = '';
		foreach ( $values AS $field => $value ) {
			if ( $valueString ) $valueString .= ', ';
			$valueString .= '`'.$field."`='".$value."'";
		}
		$queryString = "UPDATE ".$table." SET ".$valueString." ".$where;
		$this->query($queryString);
	}



	////////////////////////////////////////////////////////////////////////////////// -> SELECT

	//Select Datenbankanfrage, mehrzeilig
	function fetch($query, $restype=0) {
		$this->querystring = $query;

		if ( $restype==1 ) $restype = MYSQL_ASSOC;
		else $restype = MYSQL_BOTH;

		$result = $this->query($query); //Query
		if ( !$result ) return false;

		$fetched = array();
		while ( $element = $result->fetch_array($restype) ) {
			$fetched[] = $element;
		}
		$result->free();

		return $fetched;
	}



	//Select Datenbankanfrage, mehrzeilig
	function fetch_index($query, $indexcol, $restype=0) {
		$this->querystring = $query;

		if ( $restype==1 ) $restype = MYSQL_ASSOC;
		else $restype = MYSQL_BOTH;

		$result = $this->query($query); //Query
		if ( !$result ) return false;

		$fetched = array();
		while ( $element = $result->fetch_array($restype) ) {
			if ( isset($element[$indexcol]) ) $fetched[$element[$indexcol]] = $element;
			else $fetched[] = $element;
		}
		$result->free();

		return $fetched;
	}



	//Select Datenbankanfrage, erste Zeile
	function first($query,$restype=0) {
		$this->querystring = $query;

		if ( $restype==1 ) $restype = MYSQL_ASSOC;
		else $restype = MYSQL_BOTH;

		$result = $this->query($query); //Query
		if ( !$result ) return false;

		$row = $result->fetch_array($restype);
		$result->free();

		return $row;
	}



	//Insert-Id
	function insert_id() {
		return mysql_insert_id($this->conn);
	}



	//Affected Rows
	function affected_rows() {
		return mysql_affected_rows($this->conn);
	}



	//Free Result
	function free_result($query) {
		@mysql_free_result($query);
	}


	//Server-Info
	function server_info() {
		return mysql_get_server_info($this->conn);
	}



	////////////////////////////////////////////////////////////////////////////////// -> BENCHMARK

	function bench_start() {
		if ( !$this->benchmark ) return;
		$this->benchstart = microtime();
	}



	function bench_end() {
		if ( !$this->benchmark ) return;

		list($usec,$sec) = explode(' ',microtime());
		$b2 = ((float)$usec+(float)$sec);
		list($usec,$sec) = explode(' ',$this->benchstart);
		$b1 = ((float)$usec+(float)$sec);
		$result = $b2-$b1;

		$this->benchdata[] = array(
			'query' => $this->querystring,
			'time' => sprintf('%1.6f',$result)
		);

	  $this->benchtime += $b2-$b1;
	}



	////////////////////////////////////////////////////////////////////////////////// -> FEHLERMELDUNGEN

	//MySQL-Fehler -> Text
	function error() {
		if ( $this->quieterror ) return;
		return 'Anfrage: '.$this->querystring.'<br />MySQL meldet: '.mysql_error($this->conn);
	}



	//Letzter Query
	function lq() {
		echo $this->querystring;
	}


} //END CLASS






//RESULT CLASS
class mysqlresult {

	var $result;


	function mysqlresult($result) {
		$this->result = $result;
		$this->conn = $conn;
	}


	function fetch_array() {
		return mysql_fetch_array($this->result);
	}


	function free() {
		mysql_free_result($this->result);
	}

} //END CLASS

?>
