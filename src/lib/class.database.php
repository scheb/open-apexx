<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//API-Version wählen
if ('mysqli' == $set['mysql_api']) {
    require BASEDIR.'lib/class.database.mysqli.php';
} else {
    require BASEDIR.'lib/class.database.mysql.php';
}
