<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class database extends mysqli
{
    //Benchmark
    public $benchmark = false;
    public $benchdata = [];
    public $benchtime = 0;

    //Datenbankkonfiguration
    public $conn;
    public $querystring;
    public $quieterror = false;

    ////////////////////////////////////////////////////////////////////////////////// -> STARTUP + END

    //Verbindung herstellen
    public function database($server, $user, $password, $database, $utf8 = false)
    {
        parent::__construct($server, $user, $password);
        if ($this->connect_error) {
            error('Verbindung konnte nicht hergestellt werden!<br />MySQL meldet: '.$this->connect_error, 1);

            return;
        }
        if (!$this->select_db($database)) {
            error('Konnte die Datenbank '.$database.' nicht ausw&auml;hlen!<br />MySQL meldet: '.$this->error, 1);
        }
        if ($utf8) {
            $this->query("SET NAMES 'utf8'");
        } else {
            $this->query("SET NAMES 'latin1'");
        }
        if ($this->server_version >= 50700) {
            $this->query("SET sql_mode=''");
        }
    }

    //Verbindung schlie�en
    public function close()
    {
        parent::close();

        //Benchmark ausgeben
        if ($this->benchmark) {
            echo '<pre>';
            print_r($this->benchdata);
            echo 'TOTAL: '.sprintf('%1.6f', $this->benchtime);
            echo '</pre>';
        }
    }

    ////////////////////////////////////////////////////////////////////////////////// -> INSERT / UPDATE

    //Normale Datenbankanfrage
    public function query($query)
    {
        $this->querystring = $query;

        $this->bench_start();
        $result = parent::query($query);
        $this->bench_end();

        if (!$result) {
            error($this->error());
        } else {
            return $result;
        }

        return false;
    }

    //DnyQuery Cols
    public function mkcols($data)
    {
        $p = explode(',', trim($data));
        $out = [];
        foreach ($p as $onecol) {
            $out[] = trim($onecol);
        }

        return $out;
    }

    //Dynamisches UPDATE
    public function dupdate($table, $postcols, $conditions = '')
    {
        $getcols = $this->mkcols($postcols);
        $info = $this->fetch('SHOW COLUMNS FROM '.$table);
        if (!count($info)) {
            error('Anforderung der Tabellen-Spalten fehlgeschlagen<br />Tabelle: '.$table);
        }

        $colcache = [];
        foreach ($info as $thecol) {
            if (in_array($thecol['Field'], $getcols) /*&& isset($_POST[$thecol['Field']])*/) {
                $colcache[] = $table.'.'.$thecol['Field']."='".addslashes($_POST[$thecol['Field']])."'";
            }
        }

        $commands = implode(', ', $colcache);

        if ($this->query('UPDATE '.$table.' SET '.$commands.' '.$conditions)) {
            return true;
        }

        return false;
    }

    //Dynamischer INSERT
    public function dinsert($table, $postcols)
    {
        $getcols = $this->mkcols($postcols);

        $info = $this->fetch('SHOW COLUMNS FROM '.$table);
        if (!count($info)) {
            error('Anforderung der Tabellen-Spalten fehlgeschlagen<br />Tabelle: '.$table);
        }

        $colcache = [];
        $valcache = [];
        foreach ($info as $thecol) {
            if (in_array($thecol['Field'], $getcols) /*&& isset($_POST[$thecol['Field']])*/) {
                $colcache[] = $table.'.'.$thecol['Field'];
                $valcache[] = "'".addslashes($_POST[$thecol['Field']])."'";
            }
        }

        $cols = implode(', ', $colcache);
        $values = implode(', ', $valcache);

        if ($this->query('INSERT INTO '.$table.' ('.$cols.') VALUES ('.$values.')')) {
            return true;
        }

        return false;
    }

    //Insert durchf�hren
    public function insert($table, $values)
    {
        $values = array_map('addslashes', $values);
        $queryString = 'INSERT INTO '.$table.' (`'.implode('`, `', array_keys($values))."`) VALUES ('".implode("','", $values)."')";
        $this->query($queryString);
    }

    //Insert durchf�hren
    public function update($table, $values, $where)
    {
        $values = array_map('addslashes', $values);
        $valueString = '';
        foreach ($values as $field => $value) {
            if ($valueString) {
                $valueString .= ', ';
            }
            $valueString .= '`'.$field."`='".$value."'";
        }
        $queryString = 'UPDATE '.$table.' SET '.$valueString.' '.$where;
        $this->query($queryString);
    }

    ////////////////////////////////////////////////////////////////////////////////// -> SELECT

    //Select Datenbankanfrage, mehrzeilig
    public function fetch($query, $restype = 0)
    {
        $this->querystring = $query;

        if (1 == $restype) {
            $restype = MYSQLI_ASSOC;
        } else {
            $restype = MYSQLI_BOTH;
        }

        $result = $this->query($query); //Query
        if (!$result) {
            return false;
        }
        $fetched = [];
        while ($element = $result->fetch_array($restype)) {
            $fetched[] = $element;
        }
        $result->free();

        return $fetched;
    }

    //Select Datenbankanfrage, mehrzeilig
    public function fetch_index($query, $indexcol, $restype = 0)
    {
        $this->querystring = $query;

        if (1 == $restype) {
            $restype = MYSQLI_ASSOC;
        } else {
            $restype = MYSQLI_BOTH;
        }

        $result = $this->query($query); //Query
        if (!$result) {
            return false;
        }
        $fetched = [];
        while ($element = $result->fetch_array($restype)) {
            if (isset($element[$indexcol])) {
                $fetched[$element[$indexcol]] = $element;
            } else {
                $fetched[] = $element;
            }
        }
        $result->free();

        return $fetched;
    }

    //Select Datenbankanfrage, erste Zeile
    public function first($query, $restype = 0)
    {
        $this->querystring = $query;

        if (1 == $restype) {
            $restype = MYSQLI_ASSOC;
        } else {
            $restype = MYSQLI_BOTH;
        }

        $result = $this->query($query); //Query
        if (!$result) {
            return false;
        }
        $row = $result->fetch_array($restype);
        $result->free();

        return $row;
    }

    //Insert-Id
    public function insert_id()
    {
        return $this->insert_id;
    }

    //Affected Rows
    public function affected_rows()
    {
        return $this->affected_rows;
    }

    //Free Result
    public function free_result($query)
    {
        $query->free();
    }

    //Server-Info
    public function server_info()
    {
        $this->server_info;
    }

    ////////////////////////////////////////////////////////////////////////////////// -> BENCHMARK

    public function bench_start()
    {
        if (!$this->benchmark) {
            return;
        }
        $this->benchstart = microtime();
    }

    public function bench_end()
    {
        if (!$this->benchmark) {
            return;
        }
        list($usec, $sec) = explode(' ', microtime());
        $b2 = ((float) $usec + (float) $sec);
        list($usec, $sec) = explode(' ', $this->benchstart);
        $b1 = ((float) $usec + (float) $sec);
        $result = $b2 - $b1;

        $this->benchdata[] = [
            'query' => $this->querystring,
            'time' => sprintf('%1.6f', $result),
        ];

        $this->benchtime += $b2 - $b1;
    }

    ////////////////////////////////////////////////////////////////////////////////// -> FEHLERMELDUNGEN

    //MySQL-Fehler -> Text
    public function error()
    {
        if ($this->quieterror) {
            return;
        }

        return 'Anfrage: '.$this->querystring.'<br />MySQL meldet: '.$this->error;
    }

    //Letzter Query
    public function lq()
    {
        echo $this->querystring;
    }
} //END CLASS
