<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('formmailer', 'sendto', 'array_keys', '', 'a:0:{}', '', 1190662990, 1000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
}

//Update
elseif (SETUPMODE == 'update') {
    switch ($installed_version) {
        case 100: //Zu 1.1.0

            //config Update
            updateConfig('formmailer', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('formmailer', 'sendto', 'array_keys', '', 'a:0:{}', '', 1190662990, 1000);
			");
    }
}
