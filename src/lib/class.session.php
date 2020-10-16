<?php

global $set;

//API-Version wählen
if ('db' == $set['session_api']) {
    require BASEDIR.'lib/class.dbsession.php';
} else {
    require BASEDIR.'lib/class.phpsession.php';
}
