<?php

/**
 * Based on:
 * http://www.tutorials.de/forum/php-codeschnipsel/338463-gueltigkeit-des-personalausweises-pruefen.html.
 */
class IdentyCard
{
    public $valid = false; //Ausweis gültig?
    public $valitity = [];
    public $checkSums = [null, null, null, null]; //Checksummen der einzelnen Abschnitte

    public $values = []; //Werte der einzelnen Abschnitte
    public $bkz; //Behördenkennziffer
    public $birthday; //Geburtstag
    public $expireDate; //Ablaufdatum

    //Konstruktor
    public function IdentyCard($part1, $part2, $part3, $part4)
    {
        $this->values = [$part1, $part2, $part3, $part4];
        $this->valitity[] = $this->analyzePart1($part1);
        $this->valitity[] = $this->analyzePart2($part2);
        $this->valitity[] = $this->analyzePart3($part3);
        $this->valitity[] = $this->valid = $this->analyzePart4($part4);
    }

    //Prüft den ersten Teil des Ausweises
    public function analyzePart1($value)
    {
        $value = trim($value);

        //Enthält der String genau 11 Zeichen?
        if (11 != strlen($value)) {
            return false;
        }

        //Behördenkennzahl, Ausweisnummer + zugehörige Prüfnummer prüfen
        //Zeichen prüfen, ob es sich um Zahlen handelt (Position 1-10)
        for ($i = 0; $i < 10; ++$i) {
            if (!is_numeric($value[$i])) {
                return false;
            }
        }

        // Nationalität (Ist kein Zahlenwert und ist Zeichenkette?)
        if (is_numeric($value[10]) || !is_string($value[10])) {
            return false;
        }

        //Prüfziffer berechnen (eigene)
        $check = $value[0] * 7;
        $check += $value[1] * 3;
        $check += $value[2] * 1;
        $check += $value[3] * 7;
        $check += $value[4] * 3;
        $check += $value[5] * 1;
        $check += $value[6] * 7;
        $check += $value[7] * 3;
        $check += $value[8] * 1;

        //Prüfziffer korrekt?
        if ($check % 10 != $value[9]) {
            return false;
        }

        $this->bkz = $value[0].$value[1].$value[2].$value[3];

        // Prüfziffer berechnen (gesamt)
        $sum = $value[0] * 7;
        $sum += $value[1] * 3;
        $sum += $value[2] * 1;
        $sum += $value[3] * 7;
        $sum += $value[4] * 3;
        $sum += $value[5] * 1;
        $sum += $value[6] * 7;
        $sum += $value[7] * 3;
        $sum += $value[8] * 1;
        $sum += ($check % 10) * 7;

        $this->checkSums[0] = $sum;

        return true;
    }

    //Prüft den zweiten Teil des Ausweises
    public function analyzePart2($value)
    {
        $value = trim($value);

        //Enthält der String genau 7 Zeichen?
        if (7 != strlen($value)) {
            return false;
        }

        //Geburtsdatum + zugehörige Prüfnummer prüfen (Ist Zahlenwert?)
        for ($i = 0; $i < 7; ++$i) {
            if (!is_numeric($value[$i])) {
                return false;
            }
        }

        //Datum prüfen
        //Jahr
        $y = $this->getYear(intval($value[0].$value[1]), 20);

        //Monat
        $m = intval($value[2].$value[3]);
        if ($m > 12 || $m < 1) {
            return false;
        }

        //Tag
        $d = intval($value[4].$value[5]);
        if ($d > 31 || $d < 1) {
            return false;
        }

        $this->birthday = [
            'day' => $d,
            'month' => $m,
            'year' => $y,
        ];

        //Wenn der angegeben Monat mehr Tage als gültig hat
        /*if(date("t", $timestamp) < $d) {
            return false;
        }*/

        // Prüfziffer berechnen (eigene)
        $check = $value[0] * 7;
        $check += $value[1] * 3;
        $check += $value[2] * 1;
        $check += $value[3] * 7;
        $check += $value[4] * 3;
        $check += $value[5] * 1;

        // Prüfziffer korrekt?
        if ($check % 10 != $value[6]) {
            return false;
        }

        // Prüfziffer berechnen (gesamt)
        $sum = $value[0] * 3;
        $sum += $value[1] * 1;
        $sum += $value[2] * 7;
        $sum += $value[3] * 3;
        $sum += $value[4] * 1;
        $sum += $value[5] * 7;
        $sum += ($check % 10) * 3;

        $this->checkSums[1] = $sum;

        return true;
    }

    //Prüft den dritten Teil des Ausweises
    public function analyzePart3($value)
    {
        $value = trim($value);

        //Enthält der String genau 7 Zeichen?
        if (7 != strlen($value)) {
            return false;
        }

        //Ablaufdatum + zugehörige Prüfnummer prüfen (Ist Zahlenwert?)
        for ($i = 0; $i < 7; ++$i) {
            if (!is_numeric($value[$i])) {
                return false;
            }
        }

        //Datum prüfen
        //Jahr
        $y = $this->getYear(intval($value[0].$value[1]), 70);

        //Monat
        $m = intval($value[2].$value[3]);
        if ($m > 12 || $m < 1) {
            return false;
        }

        //Tag
        $d = intval($value[4].$value[5]);
        if ($d > 31 || $d < 1) {
            return false;
        }

        $this->expireDate = [
            'day' => $d,
            'month' => $m,
            'year' => $y,
        ];

        //Wenn der angegeben Monat mehr Tage als gültig hat
        /*if(date("t", $timestamp) < $d) {
            return false;
        }*/

        // Prüfziffer berechnen (eigene)
        $check = $value[0] * 7;
        $check += $value[1] * 3;
        $check += $value[2] * 1;
        $check += $value[3] * 7;
        $check += $value[4] * 3;
        $check += $value[5] * 1;

        // Prüfziffer korrekt?
        if ($check % 10 != $value[6]) {
            return false;
        }

        // Prüfziffer berechnen (gesamt)
        $sum = $value[0] * 1;
        $sum += $value[1] * 7;
        $sum += $value[2] * 3;
        $sum += $value[3] * 1;
        $sum += $value[4] * 7;
        $sum += $value[5] * 3;
        $sum += ($check % 10) * 1;

        $this->checkSums[2] = $sum;

        return true;
    }

    // Prüft den vierten Teil des Ausweises
    public function analyzePart4($value)
    {
        $value = trim($value);

        //Enthält der String genau 1 Zeichen?
        if (1 != strlen($value)) {
            return false;
        }

        //Prüfnummer prüfen
        if (!is_numeric($value)) {
            return false;
        }

        //Fehlt eine Checksumme?
        if (is_null($this->checkSums[0]) || is_null($this->checkSums[1]) || is_null($this->checkSums[2])) {
            return false;
        }

        //Zwischensummen addieren
        $check = $this->checkSums[0] + $this->checkSums[1] + $this->checkSums[2];

        // Prüfziffer korrekt?
        if ($check % 10 != $value) {
            return false;
        }

        $this->checkSums[3] = $check;

        return true;
    }

    //Jahreszahl auslesen
    public function getYear($year, $border)
    {
        if ($year < $border) {
            return 2000 + $year;
        }

        return 1900 + $year;
    }

    //Werte der Abschnitte zurückgeben
    public function getValues()
    {
        return $this->values;
    }

    //Alter zurückgeben
    public function getAge()
    {
        if (!$this->valid) {
            return null;
        }

        $nowYear = intval(date('Y'));
        $bdYear = $this->birthday['year'];
        $age = $nowYear - $bdYear;

        $nowStamp = intval(date('md'));
        $bdStamp = sprintf('%02d%02d', $this->birthday['month'], $this->birthday['day']);

        //Prüfen, ob die Person in diesem Jahr schon Geburtstag hatte
        if ($bdStamp > $nowStamp) {
            --$age;
        }

        return $age;
    }

    //Ablaufdatum zurückgeben
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    //Geburtstag zurückgeben
    public function getBirthday()
    {
        return $this->birthday;
    }

    //Ist der Ausweis gültig?
    public function isValid()
    {
        return $this->valid;
    }

    //Ist der Ausweis abgelaufen?
    public function isExpired()
    {
        if (!$this->valid) {
            return false;
        }

        $todayStamp = intval(date('Ymd'));
        $expireStamp = intval(sprintf(
            '%04d%02d%02d',
            $this->expireDate['year'],
            $this->expireDate['month'],
            $this->expireDate['day']
        ));

        return $todayStamp > $expireStamp;
    }
}

?> 