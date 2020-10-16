<?php

//Forum-Modul muss aktiv sein
if (!$apx->is_module('forum')) {
    filenotfound();

    return;
}

if (in_array($_REQUEST['option'], ['addforum', 'addthread'])) {
    require dirname(__FILE__).'/subscribe_add.php';
} elseif ('edit' == $_REQUEST['option']) {
    require dirname(__FILE__).'/subscribe_edit.php';
} elseif ('delete' == $_REQUEST['option']) {
    require dirname(__FILE__).'/subscribe_del.php';
} else {
    filenotfound();
}
