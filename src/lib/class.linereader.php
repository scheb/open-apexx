<?php

class LineReader
{
    public $filepath;
    public $reader;
    public $buffer = '';
    public $end;
    public $endlength;
    public $length = 102400;

    //Konstrukor
    public function LineReader($filepath, $end = "\n", $length = 102400)
    {
        $this->filepath = $filepath;
        if (is_int($length) && $length > 0) {
            $this->length = $length;
        }
        if (0 == strlen($end)) {
            $end = "\n";
        }
        $this->end = $end;
        $this->endlength = strlen($end);
        $this->openFile();
    }

    //Datei öffnen
    public function openFile()
    {
        $this->reader = fopen($this->filepath, 'r');
        //$this->extendBuffer(); //Erste Daten in Puffer schreiben
    }

    //Datei schließen
    public function close()
    {
        if (!is_null($this->reader)) {
            fclose($this->reader);
            $this->reader = null;
        }
    }

    //Puffer auslesen und Treffer zurückgeben
    public function getNext()
    {
        //Puffer füllen, bis Treffer
        do {
            if (false !== ($line = $this->getNextLine())) {
                return $line;
            }
        } while ($this->extendBuffer());

        //Keine weiteren Treffer => Rest zurückgeben
        if ($this->buffer) {
            $line = $this->buffer;
            $this->buffer = '';

            return $line;
        }

        //Keine Daten mehr im Puffer => Ende

        return false;
    }

    //Puffer erweitern
    public function extendBuffer()
    {
        if (!feof($this->reader) && $chunk = fread($this->reader, $this->length)) {
            $this->buffer .= $chunk;

            return true;
        }

        return false;
    }

    //Nächsten Treffer suchen und zurückgeben, Buffer verkleinern
    public function getNextLine()
    {
        if (false !== ($lineend = strpos($this->buffer, $this->end))) {
            $line = substr($this->buffer, 0, $lineend);
            $this->buffer = substr($this->buffer, $lineend + $this->endlength);

            return $line;
        }

        return false;
    }

    //Dateiende erreicht?
    public function eof()
    {
        return !$this->buffer;
    }
}
