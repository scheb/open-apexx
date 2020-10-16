<?php

// captcha Function Class
// =====================

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

if ($set['main']['old_captcha']) {
    require_once dirname(__FILE__).'/class.captcha.v1.php';
} else {
    require_once dirname(__FILE__).'/class.captcha.v2.php';
}
