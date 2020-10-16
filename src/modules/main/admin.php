<?php

// MAIN CLASS
// ==========

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class action
{
    //STARTUP
    public function action()
    {
        //Code um die Explorer-Leiste zu aktualisieren
        $this->refresh = <<<'CODE'
<script language="JavaScript" type="text/javascript">
<!--

top.frames[0].window.location.reload();

//-->
</script>
CODE;
    }

    //***************************** Index-Seite *****************************
    public function index()
    {
        global $set,$apx,$db;

        //Ist der Nutzer angemeldet?
        if (!$apx->user->info['userid']) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: action.php?action=user.login');

            return;
        }

        //Online-Liste
        list($online) = $db->first('SELECT count(userid) FROM '.PRE.'_user LEFT JOIN '.PRE."_user_groups USING(groupid) WHERE ( gtype IN ('admin','indiv') AND lastactive>='".(time() - $apx->user->timeout * 60)."' )");
        $data = $db->fetch('SELECT username FROM '.PRE.'_user LEFT JOIN '.PRE."_user_groups USING(groupid) WHERE ( gtype IN ('admin','indiv') AND lastactive>='".(time() - $apx->user->timeout * 60)."' ) ORDER BY username ASC");
        foreach ($data as $res) {
            $usernames[] = $res['username'];
        }
        $apx->tmpl->assign('ONLINE_COUNT', $online);
        $apx->tmpl->assign('ONLINE', implode(', ', $usernames));

        //Benutzer-Informationen
        list($groupname) = $db->first('SELECT name FROM '.PRE."_user_groups WHERE groupid='".$apx->user->info['groupid']."' LIMIT 1");

        $apx->tmpl->assign('USERID', $apx->user->info['userid']);
        $apx->tmpl->assign('USERNAME_LOGIN', replace($apx->user->info['username_login']));
        $apx->tmpl->assign('USERNAME', replace($apx->user->info['username']));
        $apx->tmpl->assign('EMAIL', replace($apx->user->info['email']));
        $apx->tmpl->assign('GROUP', replace($groupname));
        $apx->tmpl->assign('SESSION', mkdate($apx->user->info['lastonline']));

        $apx->tmpl->assign('VERSION', VERSION);
        $apx->tmpl->assign('MODULES', count($apx->modules));

        $apx->tmpl->parse('index');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Module *****************************
    public function mshow()
    {
        global $set,$apx,$db,$html;

        //Navi aktualisieren
        if ($_REQUEST['resetnav']) {
            echo $this->refresh;
        }

        //Liste aktualisieren
        if ('refresh' == $_REQUEST['do']) {
            $this->mshow_refresh();

            return;
        }

        //Alle Modul-Informationen einlesen
        $regmods = $this->get_modinfo();

        //Module, die nicht aktiv sind -> Sprachpaket laden
        foreach ($regmods as $modulename => $module) {
            if (in_array($modulename, $apx->coremodules)) {
                continue;
            }
            $apx->lang->module_load($modulename);
        }
        $apx->lang->dropall('modulename');

        echo'<p class="slink">&raquo; <a href="action.php?action=main.mshow&amp;do=refresh">'.$apx->lang->get('REFRESH').'</a></p>';

        $col[] = ['', 1, 'align="center"'];
        $col[] = ['COL_TITLE', 70, 'class="title"'];
        $col[] = ['COL_ID', 30, 'align="center"'];

        //STATISCHE MODULE
        foreach ($apx->coremodules as $modulename) {
            ++$i;
            $module = $regmods[$modulename];

            $tabledata[$i]['ID'] = $modulename;
            $tabledata[$i]['COL1'] = '<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
            $tabledata[$i]['COL2'] = $apx->lang->get('MODULENAME_'.strtoupper($modulename));
            $tabledata[$i]['COL3'] = $modulename;

            $alert = $apx->lang->get('TITLE').': '.$apx->lang->get('MODULENAME_'.strtoupper($modulename)).'\n';
            $alert .= $apx->lang->get('AUTHOR').': '.$module['author'].'\n';
            $alert .= $apx->lang->get('CONTACT').': '.$module['contact'].'\n';
            $alert .= $apx->lang->get('DEPENDENCE').': ';
            if (is_array($module['dependence']) && count($module['dependence'])) {
                $alert .= @implode(', ', $module['dependence']);
            } else {
                $alert .= $apx->lang->get('NO');
            }
            if (is_array($module['requirement']) && count($module['requirement'])) {
                $alert .= '\n\n';
                $alert .= $apx->lang->get('REQUIREMENTS').': ';
                $ri = 0;
                foreach ($module['requirement'] as $reqModule => $reqVersion) {
                    ++$ri;
                    if ($ri > 1) {
                        $alert .= ', ';
                    }
                    $alert .= $apx->lang->get('MODULENAME_'.strtoupper($reqModule)).' ('.$reqVersion.')';
                }
            }
            $alert .= '\n\n';
            $alert .= $apx->lang->get('INSTALLEDVERSION').': '.$module['installed_version_dotted'].'\n';
            $alert .= $apx->lang->get('CURRENTVERSION').': '.$module['current_version_dotted'];
            if ($module['installed_version'] < $module['current_version']) {
                $alert .= '\n'.$apx->lang->get('UPDATEREQUIRED');
            }

            //Optionen
            $tabledata[$i]['OPTIONS'] .= '<a href="javascript:void(0)" onclick="alert(\''.$alert.'\')"><img src="design/info.gif" alt="" style="vertical-align:middle;" /></a>';
            $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';

            //Aktualisieren
            if (file_exists(BASEDIR.getmodulepath($modulename).'setup.php')) {
                if ($module['installed'] && $module['installed_version'] < $module['current_version'] && $this->checkRequirement($module, $regmods) && $apx->user->has_right('main.mupdate')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('update.gif', 'main.mupdate', 'module='.$modulename, $apx->lang->get('UPDATE'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }
            }

            if ($apx->user->has_right('main.mconfig')) {
                $tabledata[$i]['OPTIONS'] .= optionHTML('config.gif', 'main.mconfig', 'module='.$modulename, $apx->lang->get('CONFIG'));
            } else {
                $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
            }

            unset($module,$action);
        }

        //ZUSATZ-MODULE
        if (count($regmods)) {
            //Spacer
            $tabledata[$i + 1]['SPACER'] = true;

            foreach ($regmods as $modulename => $module) {
                if (in_array($modulename, $apx->coremodules)) {
                    continue;
                }
                ++$i;

                if ($module['active']) {
                    $tabledata[$i]['COL1'] = '<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
                } else {
                    $tabledata[$i]['COL1'] = '<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
                }

                $tabledata[$i]['ID'] = $modulename;
                $tabledata[$i]['COL2'] = $apx->lang->get('MODULENAME_'.strtoupper($modulename));
                $tabledata[$i]['COL3'] = $modulename;

                $alert = $apx->lang->get('TITLE').': '.$apx->lang->get('MODULENAME_'.strtoupper($modulename)).'\n';
                $alert .= $apx->lang->get('AUTHOR').': '.$module['author'].'\n';
                $alert .= $apx->lang->get('CONTACT').': '.$module['contact'].'\n';
                $alert .= $apx->lang->get('DEPENDENCE').': ';
                if (is_array($module['dependence']) && count($module['dependence'])) {
                    $alert .= @implode(', ', $module['dependence']);
                } else {
                    $alert .= $apx->lang->get('NO');
                }
                if (is_array($module['requirement']) && count($module['requirement'])) {
                    $alert .= '\n\n';
                    $alert .= $apx->lang->get('REQUIREMENTS').': ';
                    $ri = 0;
                    foreach ($module['requirement'] as $reqModule => $reqVersion) {
                        ++$ri;
                        if ($ri > 1) {
                            $alert .= ', ';
                        }
                        $alert .= $apx->lang->get('MODULENAME_'.strtoupper($reqModule)).' ('.$reqVersion.')';
                    }
                }
                $alert .= '\n\n';
                $alert .= $apx->lang->get('INSTALLEDVERSION').': '.$module['installed_version_dotted'].'\n';
                $alert .= $apx->lang->get('CURRENTVERSION').': '.$module['current_version_dotted'];
                if ($module['installed_version'] < $module['current_version']) {
                    $alert .= '\n'.$apx->lang->get('UPDATEREQUIRED');
                }

                //Optionen
                $tabledata[$i]['OPTIONS'] .= '<a href="javascript:void(0)" onclick="alert(\''.$alert.'\')"><img src="design/info.gif" alt="" style="vertical-align:middle;" /></a>';

                //Aktivieren/Deaktivieren
                if ($module['active']) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('disable.gif', 'main.mdisable', 'module='.$modulename, $apx->lang->get('CORE_DISABLE'));
                } elseif (!$module['active'] && $module['installed']) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('enable.gif', 'main.menable', 'module='.$modulename, $apx->lang->get('CORE_ENABLE'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                //Installieren/Aktualisieren/Deinstallieren
                if (file_exists(BASEDIR.getmodulepath($modulename).'setup.php')) {
                    if (!$module['installed'] && $this->checkRequirement($module, $regmods) && $apx->user->has_right('main.minstall')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('install.gif', 'main.minstall', 'module='.$modulename, $apx->lang->get('INSTALL'));
                    } elseif ($module['installed'] && $module['installed_version'] < $module['current_version'] && $this->checkRequirement($module, $regmods) && $apx->user->has_right('main.mupdate')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('update.gif', 'main.mupdate', 'module='.$modulename, $apx->lang->get('UPDATE'));
                    } elseif ($module['installed'] && !$module['active'] && $apx->user->has_right('main.muninstall')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('uninstall.gif', 'main.muninstall', 'module='.$modulename, $apx->lang->get('UNINSTALL'));
                    } else {
                        $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                    }
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('main.mconfig') && $module['installed'] && $module['active']) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('config.gif', 'main.mconfig', 'module='.$modulename, $apx->lang->get('CONFIG'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }
            }
        }

        $multiactions = [];
        if ($apx->user->has_right('main.menable')) {
            $multiactions[] = [$apx->lang->get('CORE_ENABLE'), 'action.php?action=main.menable', false];
        }
        if ($apx->user->has_right('main.mdisable')) {
            $multiactions[] = [$apx->lang->get('CORE_DISABLE'), 'action.php?action=main.mdisable', false];
        }
        if ($apx->user->has_right('main.minstall')) {
            $multiactions[] = [$apx->lang->get('INSTALL'), 'action.php?action=main.minstall', false];
        }
        if ($apx->user->has_right('main.muninstall')) {
            $multiactions[] = [$apx->lang->get('UNINSTALL'), 'action.php?action=main.muninstall', false];
        }
        if ($apx->user->has_right('main.mupdate')) {
            $multiactions[] = [$apx->lang->get('UPDATE'), 'action.php?action=main.mupdate', false];
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col, $multiactions);
    }

    //REGISTRIERTE MODULE -> INFO AUSLESEN
    public function get_modinfo($module = false)
    {
        global $set,$apx,$db;

        //Ein Modul
        if ($module) {
            $res = $db->first('SELECT * FROM '.PRE."_modules WHERE module='".addslashes($module)."' LIMIT 1");

            return $this->regmod_readout($res);
        }

        //Alle

        $data = $db->fetch('SELECT * FROM '.PRE.'_modules ORDER BY module');
        if (!count($data)) {
            return [];
        }
        foreach ($data as $res) {
            $feedback = $this->regmod_readout($res);
            if (false === $feedback) {
                continue;
            }
            $mods[$res['module']] = $feedback;
        }

        return $mods;
    }

    //INFO VERARBEITEN
    public function regmod_readout($res)
    {
        $modulename = $res['module'];
        if (!file_exists(BASEDIR.getmodulepath($modulename).'init.php')) {
            return false;
        }
        include BASEDIR.getmodulepath($modulename).'init.php';

        $info = $module;
        $info['active'] = $res['active'];
        $info['installed'] = $res['installed'];
        $info['installed_version'] = $res['version'];
        $info['current_version'] = intval(str_replace('.', '', $info['version']));

        $info['installed_version_dotted'] = $res['version'][0].'.'.$res['version'][1].'.'.$res['version'][2];
        $info['current_version_dotted'] = $info['version'];
        //unset($info['version']);

        //Aktualisierbare Versionen
        if (!isset($info['updateable'])) {
            $info['updateable'] = [];
        } elseif (is_array($info['updateable'])) {
            foreach ($info['updateable'] as $key => $version) {
                $info['updateable'][$key] = intval(str_replace('.', '', $version));
            }
        } else {
            $temp = [intval(str_replace('.', '', $info['updateable']))];
            $info['updateable'] = $temp;
        }

        return $info;
    }

    //Anforderungen prüfen
    public function checkRequirement($module, $allmodules)
    {
        if (!is_array($module['requirement'])) {
            return true;
        }
        foreach ($module['requirement'] as $reqModule => $reqVersion) {
            $reqVersion = intval(str_replace('.', '', $reqVersion));
            if (!isset($allmodules[$reqModule])) {
                return false;
            }
            if ($allmodules[$reqModule]['installed_version'] < $reqVersion) {
                return false;
            }
        }

        return true;
    }

    //ÜBERSICHT AKTUALISIEREN
    public function mshow_refresh()
    {
        global $set,$apx,$db;
        $regmods = $this->get_modinfo();

        $db->query('DELETE FROM '.PRE.'_modules');

        $listdir = opendir(BASEDIR.getpath('moduledir'));
        while ($file = readdir($listdir)) {
            if ('.' == $file || '..' == $file || !is_dir(BASEDIR.getpath('moduledir').$file)) {
                continue;
            }
            $db->query('INSERT INTO '.PRE."_modules VALUES ('".$file."','".iif(isset($regmods[$file]), $regmods[$file]['active'], '0')."','".iif(isset($regmods[$file]), $regmods[$file]['installed'], '0')."','".iif(isset($regmods[$file]), $regmods[$file]['installed_version'], '0')."')");
        }
        @closedir($listdir);

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: action.php?action=main.mshow');
    }

    //***************************** Module aktivieren *****************************
    public function menable()
    {
        global $set,$apx,$db;

        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                foreach ($_REQUEST['multiid'] as $module) {
                    if (in_array($module, $apx->coremodules)) {
                        continue;
                    } //Core herausfiltern
                    $modules[] = $module;
                }

                //Nix zu tun
                if (!count($modules)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: action.php?action=main.mshow');

                    return;
                }

                //Aktion ausführen
                $db->query('UPDATE '.PRE."_modules SET active='1' WHERE ( installed='1' AND module IN ('".implode("','", array_map('addslashes', $modules))."') )");
                foreach ($plain as $id) {
                    logit('MAIN_MENABLE', $id);
                }

                //Cache leeren!
                $apx->tmpl->clear_cache();

                //Navigation aktualisieren
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: action.php?action=main.mshow&resetnav=1');
            }
        }

        //Einzeln
        else {
            $module = $this->get_modinfo($_REQUEST['module']);

            if (!$_REQUEST['module']) {
                die('missing module!');
            }
            if (!is_array($module)) {
                die('unknown module!');
            }
            if (!$module['installed']) {
                die('can not enable, module is not installed!');
            }

            if ($_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    $db->query('UPDATE '.PRE."_modules SET active='1' WHERE module='".addslashes($_REQUEST['module'])."' LIMIT 1");
                    logit('MAIN_MENABLE', $_REQUEST['module']);
                    printJSRedirect('action.php?action=main.mshow&resetnav=1');

                    //Cache leeren!
                    $apx->tmpl->clear_cache();
                }
            } else {
                //Sprachpaket erst laden, weil ist ja deaktiviert
                $apx->lang->module_load($_REQUEST['module']);
                $apx->lang->dropall('modulename');

                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($apx->lang->get('MODULENAME_'.strtoupper($_REQUEST['module'])))]));
                $insert['MODULE'] = $_REQUEST['module'];
                tmessageOverlay('menable', $insert);
            }
        }
    }

    //***************************** Module deaktivieren *****************************
    public function mdisable()
    {
        global $set,$apx,$db;

        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                foreach ($_REQUEST['multiid'] as $module) {
                    if (in_array($module, $apx->coremodules)) {
                        continue;
                    } //Core herausfiltern
                    $modules[] = $module;
                }

                //Nix zu tun
                if (!count($modules)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: action.php?action=main.mshow');

                    return;
                }

                //Aktion ausführen
                $db->query('UPDATE '.PRE."_modules SET active='0' WHERE ( installed='1' AND module IN ('".implode("','", array_map('addslashes', $modules))."') )");
                foreach ($plain as $id) {
                    logit('MAIN_MDISABLE', $id);
                }

                //Cache leeren!
                $apx->tmpl->clear_cache();

                //Navigation aktualisieren
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: action.php?action=main.mshow&resetnav=1');
            }
        }

        //Einzeln
        else {
            $module = $this->get_modinfo($_REQUEST['module']);
            $regmods = $this->get_modinfo();

            if (!$_REQUEST['module']) {
                die('missing module!');
            }
            if (!is_array($module)) {
                die('unknown module!');
            }
            if (in_array($_REQUEST['module'], $apx->coremodules)) {
                die('can not disable core-modules!');
            }

            //Dependence-Check
            if ($_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    $db->query('UPDATE '.PRE."_modules SET active='0' WHERE module='".addslashes($_REQUEST['module'])."' LIMIT 1");
                    logit('MAIN_MDISABLE', $_REQUEST['module']);
                    printJSRedirect('action.php?action=main.mshow&resetnav=1');

                    //Cache leeren!
                    $apx->tmpl->clear_cache();
                }
            } else {
                $isdep = [];

                foreach ($regmods as $modulename => $info) {
                    if (!is_array($info['dependence']) || !in_array($_REQUEST['module'], $info['dependence'])) {
                        continue;
                    }
                    $isdep[] = $modulename;
                }

                if (count($isdep)) {
                    $input['DEPENDENCE'] = implode(', ', $isdep);
                }

                $apx->lang->dropall('modulename');
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($apx->lang->get('MODULENAME_'.strtoupper($_REQUEST['module'])))]));
                $input['MODULE'] = $_REQUEST['module'];
                tmessageOverlay('mdisable', $input);
            }
        }
    }

    //***************************** Module installieren *****************************
    public function minstall()
    {
        global $set,$apx,$db;
        require_once BASEDIR.'lib/functions.setup.php';

        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $regmods = $this->get_modinfo();
                define('SETUPMODE', 'install');

                foreach ($_REQUEST['multiid'] as $module) {
                    if (!isset($regmods[$module])) {
                        continue;
                    } //Modul existiert nicht
                if ($regmods[$module]['installed']) {
                    continue;
                } //Modul ist schon installiert
                if (!$this->checkRequirement($regmods[$module], $regmods)) {
                    continue;
                } //Anforderungen nicht erfüllt
                if (!file_exists(BASEDIR.getmodulepath($module).'setup.php')) {
                    die('missing setup.php of module "'.$module.'"!');
                }
                    $modules[] = $module;
                }

                //Nix zu tun
                if (!count($modules)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: action.php?action=main.mshow');

                    return;
                }

                //Installation ausführen
                set_time_limit(600);
                foreach ($modules as $module) {
                    require BASEDIR.getmodulepath($module).'setup.php';
                    $db->query('UPDATE '.PRE."_modules SET installed='1',version='".$regmods[$module]['current_version']."' WHERE module='".addslashes($module)."' LIMIT 1");
                    logit('MAIN_MINSTALL', $module);
                }

                //Cache leeren!
                $apx->tmpl->clear_cache();

                //Navigation aktualisieren
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: action.php?action=main.mshow&resetnav=1');
            }
        }

        //Einzeln
        else {
            if (!$_REQUEST['module']) {
                die('missing module!');
            }

            $regmods = $this->get_modinfo();
            $module = $regmods[$_REQUEST['module']];

            if (!is_array($module)) {
                die('unknown module!');
            }
            if (!$this->checkRequirement($module, $regmods)) {
                die('requirements not complied!');
            }
            if ($module['installed']) {
                die('can not install, module is already installed!');
            }

            define('SETUPMODE', 'install');

            if (1 == $_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    if (!file_exists(BASEDIR.getmodulepath($_REQUEST['module']).'setup.php')) {
                        die('missing setup.php');
                    }

                    //Installation ausführen
                    set_time_limit(600);
                    require BASEDIR.getmodulepath($_REQUEST['module']).'setup.php';

                    //MYSQL-Update
                    $db->query('UPDATE '.PRE."_modules SET installed='1',version='".$module['current_version']."' WHERE module='".addslashes($_REQUEST['module'])."'");

                    logit('MAIN_MINSTALL', $_REQUEST['module']);
                    printJSRedirect('action.php?action=main.mshow');
                }
            } else {
                //Sprachpaket erst laden, weil ist ja deaktiviert
                $apx->lang->module_load($_REQUEST['module']);
                $apx->lang->dropall('modulename');

                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($apx->lang->get('MODULENAME_'.strtoupper($_REQUEST['module'])))]));
                $input['MODULE'] = $_REQUEST['module'];
                tmessageOverlay('minstall', $input);
            }
        }
    }

    //***************************** Module deinstallieren *****************************
    public function muninstall()
    {
        global $set,$apx,$db;
        require_once BASEDIR.'lib/functions.setup.php';

        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $regmods = $this->get_modinfo();
                define('SETUPMODE', 'uninstall');

                foreach ($_REQUEST['multiid'] as $module) {
                    if (in_array($moduleid, $apx->coremodules)) {
                        continue;
                    } //Core herausfiltern
                if (!isset($regmods[$module])) {
                    continue;
                } //Modul existiert nicht
                if (!$regmods[$module]['installed'] || $regmods[$module]['active']) {
                    continue;
                } //Nicht installiert oder noch aktiv
                if (!file_exists(BASEDIR.getmodulepath($module).'setup.php')) {
                    die('missing setup.php of module "'.$module.'"!');
                }
                    $modules[] = $module;
                }

                //Nix zu tun
                if (!count($modules)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: action.php?action=main.mshow');

                    return;
                }

                //Deinstallation ausführen
                set_time_limit(600);
                foreach ($modules as $module) {
                    require BASEDIR.getmodulepath($module).'setup.php';
                    $db->query('DELETE FROM '.PRE."_config WHERE module='".addslashes($module)."'"); //Config entfernen
                    $db->query('UPDATE '.PRE."_modules SET installed='0',version='0' WHERE module='".addslashes($module)."' LIMIT 1");
                    logit('MAIN_MUNINSTALL', $module);
                }

                //Cache leeren!
                $apx->tmpl->clear_cache();

                //Navigation aktualisieren
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: action.php?action=main.mshow&resetnav=1');
            }
        }

        //Einzeln
        else {
            $module = $this->get_modinfo($_REQUEST['module']);

            if (!$_REQUEST['module']) {
                die('missing module!');
            }
            if (!is_array($module)) {
                die('unknown module!');
            }
            if ($module['active']) {
                die('can not uninstall, module still active!');
            }
            if (!$module['installed']) {
                die('can not uninstall, module is not installed!');
            }
            if (in_array($_REQUEST['module'], $apx->coremodules)) {
                die('can not uninstall core-modules!');
            }

            define('SETUPMODE', 'uninstall');

            if (1 == $_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    if (!file_exists(BASEDIR.getmodulepath($_REQUEST['module']).'setup.php')) {
                        die('missing setup.php');
                    }

                    //Deinstallation ausführen
                    set_time_limit(600);
                    require BASEDIR.getmodulepath($_REQUEST['module']).'setup.php';

                    //MySQL-Update
                    $db->query('UPDATE '.PRE."_modules SET installed='0',version='0' WHERE module='".addslashes($_REQUEST['module'])."'");
                    $db->query('DELETE FROM '.PRE."_config WHERE module='".addslashes($_REQUEST['module'])."'");

                    logit('MAIN_MUNINSTALL', $_REQUEST['module']);
                    printJSRedirect('action.php?action=main.mshow');
                }
            } else {
                //Sprachpaket erst laden, weil ist ja deaktiviert
                $apx->lang->module_load($_REQUEST['module']);
                $apx->lang->dropall('modulename');

                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($apx->lang->get('MODULENAME_'.strtoupper($_REQUEST['module'])))]));
                $input['MODULE'] = $_REQUEST['module'];
                tmessageOverlay('muninstall', $input);
            }
        }
    }

    //***************************** Module aktualisieren *****************************
    public function mupdate()
    {
        global $set,$apx,$db;
        require_once BASEDIR.'lib/functions.setup.php';

        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $regmods = $this->get_modinfo();
                define('SETUPMODE', 'update');

                foreach ($_REQUEST['multiid'] as $module) {
                    if (!isset($regmods[$module])) {
                        continue;
                    } //Modul existiert nicht
                if (!$regmods[$module]['installed']) {
                    continue;
                } //Nicht installiert
                if (!$this->checkRequirement($regmods[$module], $regmods)) {
                    continue;
                } //Anforderungen nicht erfüllt
                if (!file_exists(BASEDIR.getmodulepath($module).'setup.php')) {
                    die('missing setup.php of module "'.$module.'"!');
                }
                    if ($regmods[$module]['installed_version'] == $regmods[$module]['current_version']) {
                        continue;
                    }
                    $modules[] = $module;
                }

                //Nix zu tun
                if (!count($modules)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: action.php?action=main.mshow');

                    return;
                }

                //Aktualisierung ausführen
                set_time_limit(600);
                foreach ($modules as $module) {
                    $installed_version = $regmods[$module]['installed_version'];
                    require BASEDIR.getmodulepath($module).'setup.php';

                    $db->query('UPDATE '.PRE."_modules SET version='".$regmods[$module]['current_version']."' WHERE module='".addslashes($module)."'");
                    logit('MAIN_MUPDATE', $module);
                }

                //Cache leeren!
                $apx->tmpl->clear_cache();

                //Navigation aktualisieren
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: action.php?action=main.mshow');
            }
        }

        //Einzeln
        else {
            if (!$_REQUEST['module']) {
                die('missing module!');
            }

            $regmods = $this->get_modinfo();
            $module = $regmods[$_REQUEST['module']];

            if (!is_array($module)) {
                die('unknown module!');
            }
            if (!$module['installed']) {
                die('can not update, module is not installed!');
            }
            if (!$this->checkRequirement($module, $regmods)) {
                die('requirements not complied!');
            }
            if ($module['installed_version'] == $module['current_version']) {
                die('module is already up to date!');
            }

            define('SETUPMODE', 'update');

            if (1 == $_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    if (!file_exists(BASEDIR.getmodulepath($_REQUEST['module']).'setup.php')) {
                        die('missing setup.php');
                    }

                    //Aktualisierung ausführen
                    set_time_limit(600);
                    $installed_version = $module['installed_version'];
                    require BASEDIR.getmodulepath($_REQUEST['module']).'setup.php';

                    //MYSQL aktualisieren
                    $db->query('UPDATE '.PRE."_modules SET version='".$module['current_version']."' WHERE module='".addslashes($_REQUEST['module'])."'");

                    logit('MAIN_MUPDATE', $_REQUEST['module']);
                    printJSRedirect('action.php?action=main.mshow');
                }
            } else {
                $apx->lang->dropall('modulename');
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($apx->lang->get('MODULENAME_'.strtoupper($_REQUEST['module'])))]));
                $input['MODULE'] = $_REQUEST['module'];
                tmessageOverlay('mupdate', $input);
            }
        }
    }

    //***************************** Module konfigurieren *****************************
    public function mconfig()
    {
        global $set,$apx,$db;
        if (!$_REQUEST['module']) {
            die('missing module!');
        }

        if ($_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } else {
                $info = [];
                $data = $db->fetch('SELECT varname,type,addnl FROM '.PRE."_config WHERE ( module='".addslashes($_REQUEST['module'])."' AND addnl!='BLOCK' ) ORDER BY ord ASC");
                foreach ($data as $res) {
                    $postvalue = $_POST[$res['varname']];

                    //Switch
                    if ('switch' == $res['type']) {
                        $thevalue = iif($postvalue, 1, 0);
                    }

                    //String
                    elseif ('string' == $res['type']) {
                        $thevalue = addslashes($postvalue);
                    }

                    //Multiline
                    elseif ('multiline' == $res['type']) {
                        $thevalue = addslashes($postvalue);
                    }

                    //Array
                    elseif ('array' == $res['type']) {
                        $thearray = [];

                        if (is_array($postvalue)) {
                            foreach ($postvalue as $element) {
                                if (!$element) {
                                    continue;
                                }
                                $thearray[] = $element;
                            }
                        }

                        $thevalue = serialize($thearray);
                    }

                    //Array mit Keys
                    elseif ('array_keys' == $res['type']) {
                        $thearray = [];

                        if (is_array($postvalue)) {
                            foreach ($postvalue as $element) {
                                if (!$element['key'] && !$element['value']) {
                                    continue;
                                }
                                $thearray[$element['key']] = $element['value'];
                            }
                        }

                        $thevalue = serialize($thearray);
                    }

                    //Integer
                    elseif ('int' == $res['type']) {
                        $thevalue = (int) $postvalue;
                    }

                    //Float
                    elseif ('float' == $res['type']) {
                        $thevalue = (float) $postvalue;
                    }

                    //Select
                    elseif ('select' == $res['type']) {
                        $possible = unserialize($res['addnl']);

                        foreach ($possible as $value => $descr) {
                            if ($value == $postvalue) {
                                $thevalue = $value;

                                break;
                            }
                        }
                    }

                    if (!isset($thevalue)) {
                        continue;
                    }
                    $db->query('UPDATE '.PRE."_config SET value='".$thevalue."',lastchange=".time()." WHERE ( module='".addslashes($_REQUEST['module'])."' AND varname='".$res['varname']."' )");
                    unset($thevalue);
                }

                logit('MAIN_MCONFIG', $_REQUEST['module']);
                printJSRedirect('action.php?action=main.mshow');
            }
        } else {
            $apx->lang->drop('config', $_REQUEST['module']);

            $buckets = [];
            $data = $db->fetch('SELECT * FROM '.PRE."_config WHERE ( module='".addslashes($_REQUEST['module'])."' AND addnl!='BLOCK' ) ORDER BY ord ASC");
            if (count($data)) {
                foreach ($data as $res) {
                    if (!$res['type']) {
                        continue;
                    }
                    $temp['NAME'] = $res['varname'];
                    $temp['DESCRIPTION'] = $apx->lang->get(strtoupper($res['varname']));
                    $temp['TYPE'] = $res['type'];
                    if ('MULTILINE' == $res['addnl']) {
                        $temp['MULTILINE'] = 1;
                    }
                    if ('password' == $res['addnl']) {
                        $temp['PASSWORD'] = 1;
                    }

                    //Switch
                    if ('switch' == $res['type']) {
                        $temp['VALUE'] = iif($res['value'], 1, 0);
                    }

                    //String
                    elseif ('string' == $res['type']) {
                        ++$i;
                        $temp['VALUE'] = compatible_hsc($res['value']);
                    }

                    //Multiline
                    elseif ('multiline' == $res['type']) {
                        ++$i;
                        $temp['VALUE'] = compatible_hsc($res['value']);
                    }

                    //Array
                    elseif ('array' == $res['type']) {
                        $array = unserialize($res['value']);
                        if (!is_array($array)) {
                            $array = [];
                        }
                        $temp['ELEMENT'] = [];

                        foreach ($array as $arrayvalue) {
                            ++$i;
                            $temp['ELEMENT'][$i]['VALUE'] = compatible_hsc($arrayvalue);
                            $temp['ELEMENT'][$i]['DISPLAY'] = 1;
                        }

                        for ($ai = 0; $ai <= 20; ++$ai) {
                            ++$i;
                            $temp['ELEMENT'][$i]['DISPLAY'] = 0;
                        }
                    }

                    //Array mit Keys
                    elseif ('array_keys' == $res['type']) {
                        $array = unserialize($res['value']);
                        if (!is_array($array)) {
                            $array = [];
                        }
                        $temp['ELEMENT'] = [];

                        foreach ($array as $arraykey => $arrayvalue) {
                            ++$i;
                            $temp['ELEMENT'][$i]['KEY'] = compatible_hsc($arraykey);
                            $temp['ELEMENT'][$i]['VALUE'] = compatible_hsc($arrayvalue);
                            $temp['ELEMENT'][$i]['DISPLAY'] = 1;
                        }

                        for ($ai = 0; $ai <= 20; ++$ai) {
                            ++$i;
                            $temp['ELEMENT'][$i]['DISPLAY'] = 0;
                        }
                    }

                    //Integer
                    elseif ('int' == $res['type']) {
                        $temp['VALUE'] = (int) $res['value'];
                    }

                    //Float
                    elseif ('float' == $res['type']) {
                        $temp['VALUE'] = (float) $res['value'];
                    }

                    //Select
                    elseif ('select' == $res['type']) {
                        $possible = unserialize($res['addnl']);

                        foreach ($possible as $value => $descr) {
                            $temp['OPTIONS'][] = [
                                'NAME' => $apx->lang->insertpack($descr),
                                'VALUE' => $value,
                                'SELECTED' => iif($value == $res['value'], true, false),
                            ];
                        }
                    }

                    $buckets[$res['tab']][] = $temp;
                    $temp = [];
                }
            }

            $vardata = [];
            foreach ($buckets as $tabname => $vars) {
                $vardata[] = [
                    'ID' => strtolower($tabname),
                    'NAME' => compatible_hsc($apx->lang->get($tabname)),
                    'VAR' => $vars,
                ];
            }

            $apx->tmpl->assign('TAB', $vardata);
            $apx->tmpl->assign('TAB_COUNT', count($vardata));
            $apx->tmpl->assign('MODULE', $_REQUEST['module']);
            $apx->tmpl->assign('MODULENAME', $apx->lang->get('MODULENAME_'.strtoupper($_REQUEST['module'])));
            $apx->tmpl->parse('config');
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Sektionen *****************************
    public function secshow()
    {
        global $set,$apx,$db,$html;

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->secshow_add();
        }
        if ('edit' == $_REQUEST['do']) {
            return $this->secshow_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->secshow_del();
        }
        if ('default' == $_REQUEST['do']) {
            return $this->secshow_default();
        }
        echo'<p class="slink">&raquo; <a href="action.php?action=main.secshow&amp;do=add">'.$apx->lang->get('ADDSECTION').'</a></p>';

        $col[] = ['', 1, 'align="center"'];
        $col[] = ['', 1, 'align="center"'];
        $col[] = ['COL_ID', 3, 'align="center"'];
        $col[] = ['COL_TITLE', 42, 'class="title"'];
        $col[] = ['COL_VIRTUAL', 28, 'align="center"'];
        $col[] = ['COL_THEME', 28, 'align="center"'];

        $data = $db->fetch('SELECT * FROM '.PRE.'_sections ORDER BY title ASC');
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;

                if ($res['default']) {
                    $tabledata[$i]['COL1'] = '<img src="design/default.gif" alt="'.$apx->lang->get('DEFAULT').'" title="'.$apx->lang->get('DEFAULT').'" />';
                } else {
                    $tabledata[$i]['COL1'] = '&nbsp;';
                }

                if ($res['active']) {
                    $tabledata[$i]['COL2'] = '<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
                } else {
                    $tabledata[$i]['COL2'] = '<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
                }

                $tabledata[$i]['COL3'] = $res['id'];
                $tabledata[$i]['COL4'] = '<a href="'.mklink('../index.php', 'index.html', $res['id']).'" target="_blank">'.replace($res['title']).'</a>';
                $tabledata[$i]['COL5'] = $res['virtual'];
                $tabledata[$i]['COL6'] = $res['theme'];

                //Optionen
                $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'main.secshow', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                if (!$res['default']) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.secshow', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }
                if (!$res['default'] && $res['active']) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('mkdefault.gif', 'main.secshow', 'do=default&id='.$res['id'], $apx->lang->get('MKDEFAULT'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        //SEO-URLs => Rewrite-Code ausgeben
        if ($set['main']['staticsites']) {
            $rwcode = '';
            foreach ($apx->sections as $section) {
                $rwcode .= 'RewriteRule ^'.$section['virtual'].'/(.*)$ $1?sec='.$section['id'].' [QSA]<br />';
            }
            $apx->tmpl->assign('RWCODE', $rwcode);
            $apx->tmpl->parse('rewritecode');
        }
    }

    //***************************** Sektion anlegen *****************************
    public function secshow_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'secadd');

        if (1 == $_POST['send']) {
            list($check) = $db->first('SELECT id FROM '.PRE."_sections WHERE LOWER(title)='".strtolower($_POST['title'])."' LIMIT 1");

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['virtual'] || !$_POST['theme'] || !$_POST['msg_noaccess']) {
                infoNotComplete();
            } elseif (!preg_match('#^[A-Za-z0-9_-]+$#', $_POST['virtual'])) {
                info($apx->lang->get('INFO_VIRTUALFORMAT'));
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } else {
                //Wenn es die erste Sektion ist
                if (!$apx->section_default) {
                    $_POST['default'] = 1;
                    $_POST['active'] = 1;
                }

                $db->dinsert(PRE.'_sections', 'title,virtual,theme,lang,active,msg_noaccess,default');
                logit('MAIN_SECADD', 'ID #'.$db->insert_id());
                printJSRedirect('action.php?action=main.secshow');
            }
        } else {
            $_POST['active'] = 1;

            //Themes auslesen
            $handle = opendir(BASEDIR.getpath('tmpldir'));
            while ($file = readdir($handle)) {
                if ('.' == $file || '..' == $file) {
                    continue;
                }
                if (!is_dir(BASEDIR.getpath('tmpldir').$file)) {
                    continue;
                }
                $designid = $file;
                $designlist .= '<option value="'.$designid.'"'.iif($designid == $_POST['theme'], ' selected="selected"').'>'.$file.'</option>';
            }
            closedir($handle);

            //Sprache
            $lang = '<option value="">'.$apx->lang->get('DEFAULT').'</option>';
            foreach ($apx->languages as $id => $name) {
                $lang .= '<option value="'.$id.'"'.iif($_POST['lang'] == $id, ' selected="selected"').'>'.$name.'</option>';
            }

            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('VIRTUAL', compatible_hsc($_POST['virtual']));
            $apx->tmpl->assign('THEME', $designlist);
            $apx->tmpl->assign('LANG', $lang);
            $apx->tmpl->assign('ACTIVE', (int) $_POST['active']);
            $apx->tmpl->assign('MSG_NOACCESS', compatible_hsc($_POST['msg_noaccess']));
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('secadd_secedit');
        }
    }

    //***************************** Sektion bearbeiten *****************************
    public function secshow_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'secedit');

        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            list($check) = $db->first('SELECT id FROM '.PRE."_sections WHERE ( LOWER(title)='".strtolower($_POST['title'])."' AND id!='".$_REQUEST['id']."' ) LIMIT 1");

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['virtual'] || !$_POST['theme'] || !$_POST['msg_noaccess']) {
                infoNotComplete();
            } elseif (!preg_match('#^[A-Za-z0-9_-]+$#', $_POST['virtual'])) {
                info($apx->lang->get('INFO_VIRTUALFORMAT'));
            } elseif ($_REQUEST['id'] == $apx->section_default && !$_POST['active']) {
                info($apx->lang->get('INFO_ISDEFAULT'));
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } else {
                $db->dupdate(PRE.'_sections', 'title,virtual,theme,lang,active,msg_noaccess', "WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('MAIN_SECEDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect('action.php?action=main.secshow');
            }
        } else {
            $res = $db->first('SELECT title,virtual,theme,lang,active,msg_noaccess FROM '.PRE."_sections WHERE id='".$_REQUEST['id']."' LIMIT 1", 1);
            foreach ($res as $key => $value) {
                $_POST[$key] = $value;
            }

            //Themes auslesen
            $handle = opendir(BASEDIR.getpath('tmpldir'));
            while ($file = readdir($handle)) {
                if ('.' == $file || '..' == $file) {
                    continue;
                }
                if (!is_dir(BASEDIR.getpath('tmpldir').$file)) {
                    continue;
                }
                $designid = $file;
                $designlist .= '<option value="'.$designid.'"'.iif($designid == $_POST['theme'], ' selected="selected"').'>'.$file.'</option>';
            }
            closedir($handle);

            //Sprache
            $lang = '<option value="">'.$apx->lang->get('DEFAULT').'</option>';
            foreach ($apx->languages as $id => $name) {
                $lang .= '<option value="'.$id.'"'.iif($_POST['lang'] == $id, ' selected="selected"').'>'.$name.'</option>';
            }

            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('VIRTUAL', compatible_hsc($_POST['virtual']));
            $apx->tmpl->assign('THEME', $designlist);
            $apx->tmpl->assign('LANG', $lang);
            $apx->tmpl->assign('ACTIVE', (int) $_POST['active']);
            $apx->tmpl->assign('MSG_NOACCESS', compatible_hsc($_POST['msg_noaccess']));
            $apx->tmpl->assign('ACTION', 'edit');
            $apx->tmpl->assign('ID', $_REQUEST['id']);

            $apx->tmpl->parse('secadd_secedit');
        }
    }

    //***************************** Sektion löschen *****************************
    public function secshow_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'secdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        if ($_REQUEST['id'] == $apx->section_default) {
            die('can not delete default section!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('DELETE FROM '.PRE."_sections WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('MAIN_SECDEL', 'ID #'.$_REQUEST['id']);
                printJSReload();
            }
        } else {
            list($title) = $db->first('SELECT title FROM '.PRE."_sections WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('secdel', ['ID' => $_REQUEST['id']]);
        }
    }

    //***************************** Standard-Sektion setzen *****************************
    public function secshow_default()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'secdefault');

        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        if (!$apx->section_is_active($_REQUEST['id'])) {
            die('section is not enabled!');
        }

        $db->query('UPDATE '.PRE.'_sections SET '.PRE.'_sections.default=0');
        $db->query('UPDATE '.PRE.'_sections SET '.PRE."_sections.default=1 WHERE id='".$_REQUEST['id']."' LIMIT 1");
        logit('MAIN_SECDEFAULT', 'ID #'.$_REQUEST['id']);
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: action.php?action=main.secshow');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Sprachpakete *****************************
    public function lshow()
    {
        global $set,$apx,$db,$html;
        $langinfo = $set['main']['languages'];

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->lshow_add();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->lshow_del();
        }
        if ('default' == $_REQUEST['do']) {
            return $this->lshow_default();
        }
        echo'<p class="slink">&raquo; <a href="action.php?action=main.lshow&amp;do=add">'.$apx->lang->get('ADDLANGPACK').'</a></p>';

        $col[] = ['', 1, 'align="center"'];
        $col[] = ['COL_DIR', 10, 'align="center"'];
        $col[] = ['COL_TITLE', 90, 'class="title"'];

        foreach ($langinfo as $dir => $res) {
            ++$i;

            if ($res['default']) {
                $tabledata[$i]['COL1'] = '<img src="design/default.gif" alt="'.$apx->lang->get('DEFAULT').'" title="'.$apx->lang->get('DEFAULT').'" />';
            } else {
                $tabledata[$i]['COL1'] = '&nbsp;';
            }

            $tabledata[$i]['COL2'] = $dir;
            $tabledata[$i]['COL3'] = replace($res['title']);

            //Optionen
            if (!$res['default']) {
                $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.lshow', 'do=del&id='.$dir, $apx->lang->get('CORE_DEL'));
            }
            if (!$res['default']) {
                $tabledata[$i]['OPTIONS'] .= optionHTML('mkdefault.gif', 'main.lshow', 'do=default&id='.$dir, $apx->lang->get('MKDEFAULT'));
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);
    }

    //***************************** Sprachpaket hinzufügen *****************************
    public function lshow_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'ladd');
        $langinfo = $set['main']['languages'];

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['dir'] || !$_POST['title']) {
                infoNotComplete();
            } elseif ($langinfo[$_POST['dir']]) {
                info($apx->lang->get('INFO_EXISTS'));
            } elseif (!is_dir(BASEDIR.'language/'.$_POST['dir'])) {
                info($apx->lang->get('INFO_MISSINGDIR'));
            } else {
                $langinfo[$_POST['dir']] = [
                    'title' => $_POST['title'],
                    'default' => false,
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($langinfo))."' WHERE ( module='main' AND varname='languages' ) LIMIT 1");
                logit('MAIN_LADD', $_POST['dir']);
                printJSRedirect('action.php?action=main.lshow');
            }
        } else {
            $apx->tmpl->assign('DIR', compatible_hsc($_POST['dir']));
            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));

            $apx->tmpl->parse('ladd');
        }
    }

    //***************************** Sprachpaket löschen *****************************
    public function lshow_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'ldel');
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $langinfo = $set['main']['languages'];
        if ($langinfo[$_REQUEST['id']]['default']) {
            die('can not delete default langpack!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                unset($langinfo[$_REQUEST['id']]);
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($langinfo))."' WHERE ( module='main' AND varname='languages' ) LIMIT 1");
                logit('MAIN_LDEL', $_REQUEST['id']);
                printJSReload();
            }
        } else {
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($langinfo[$_REQUEST['id']]['title'])]));
            tmessageOverlay('ldel', ['ID' => $_REQUEST['id']]);
        }
    }

    //***************************** Sprachpaket als Standard setzen *****************************
    public function lshow_default()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'ldefault');
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        foreach ($set['main']['languages'] as $key => $info) {
            $set['main']['languages'][$key]['default'] = false;
        }
        $set['main']['languages'][$_REQUEST['id']]['default'] = true;

        $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($set['main']['languages']))."' WHERE ( module='main' AND varname='languages' ) LIMIT 1");
        logit('MAIN_LDEFAULT', $_REQUEST['id']);
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: action.php?action=main.lshow');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Vorlagen *****************************
    public function tshow()
    {
        global $set,$apx,$db,$html;

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->tshow_add();
        }
        if ('edit' == $_REQUEST['do']) {
            return $this->tshow_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->tshow_del();
        }
        echo '<p class="slink">&raquo; <a href="action.php?action=main.tshow&amp;do=add">'.$apx->lang->get('ADDTEMPLATE').'</a></p>';
        echo '<p class="hint">'.$apx->lang->get('INFOTEXT').'</p>';

        $col[] = ['COL_TITLE', 100, 'class="title"'];

        $data = $db->fetch('SELECT id,title FROM '.PRE.'_templates ORDER BY title ASC');
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;
                $tabledata[$i]['COL1'] = replace($res['title']);

                //Optionen
                $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'main.tshow', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.tshow', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);
    }

    //***************************** Vorlagen hinzufügen *****************************
    public function tshow_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'tadd');

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['code']) {
                infoNotComplete();
            } else {
                $db->dinsert(PRE.'_templates', 'title,code');
                logit('MAIN_TADD', 'ID #'.$db->insert_id());
                message($apx->lang->get('MSG_OK_ADD'), 'action.php?action=main.tshow');

                return;
            }
        }

        mediamanager('main');

        $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
        $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
        $apx->tmpl->assign('ACTION', 'add');

        $apx->tmpl->parse('tadd_tedit');
    }

    //***************************** Vorlagen bearbeiten *****************************
    public function tshow_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'tedit');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['code']) {
                infoNotComplete();
            } else {
                $db->dupdate(PRE.'_templates', 'title,code', "WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('MAIN_TEDIT', 'ID #'.$_REQUEST['id']);
                message($apx->lang->get('MSG_OK_EDIT'), 'action.php?action=main.tshow');

                return;
            }
        } else {
            list($_POST['title'], $_POST['code']) = $db->first('SELECT title,code FROM '.PRE."_templates WHERE id='".$_REQUEST['id']."' LIMIT 1");
        }

        mediamanager('main');

        $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
        $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
        $apx->tmpl->assign('ACTION', 'edit');
        $apx->tmpl->assign('ID', $_REQUEST['id']);

        $apx->tmpl->parse('tadd_tedit');
    }

    //***************************** Vorlagen löschen *****************************
    public function tshow_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'tdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('DELETE FROM '.PRE."_templates WHERE id='".$_REQUEST['id']."'");
                logit('MAIN_TDEL', 'ID #'.$_REQUEST['id']);
                printJSReload();
            }
        } else {
            list($title) = $db->first('SELECT title FROM '.PRE."_templates WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('tdel', ['ID' => $_REQUEST['id']]);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** HTML-Codeschnipsel *****************************
    public function snippets()
    {
        global $set,$apx,$db,$html;

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->snippets_add();
        }
        if ('edit' == $_REQUEST['do']) {
            return $this->snippets_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->snippets_del();
        }
        echo '<p class="slink">&raquo; <a href="action.php?action=main.snippets&amp;do=add">'.$apx->lang->get('ADDSNIPPET').'</a></p>';
        echo '<p class="hint">'.$apx->lang->get('INFOTEXT').'</p>';

        $col[] = ['ID', 1, ''];
        $col[] = ['COL_TITLE', 100, 'class="title"'];

        $data = $db->fetch('SELECT id,title FROM '.PRE.'_snippets ORDER BY title ASC');
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;
                $tabledata[$i]['COL1'] = $res['id'];
                $tabledata[$i]['COL2'] = replace($res['title']);

                //Optionen
                $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'main.snippets', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.snippets', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);
    }

    //***************************** HTML-Codeschnipsel hinzufügen *****************************
    public function snippets_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'snippetadd');

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['code']) {
                infoNotComplete();
            } else {
                $db->dinsert(PRE.'_snippets', 'title,code');
                logit('MAIN_TADD', 'ID #'.$db->insert_id());
                printJSRedirect('action.php?action=main.snippets');
            }
        } else {
            mediamanager('main');

            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('snipadd_snipedit');
        }
    }

    //***************************** HTML-Codeschnipsel bearbeiten *****************************
    public function snippets_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'snippetedit');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['code']) {
                infoNotComplete();
            } else {
                $db->dupdate(PRE.'_snippets', 'title,code', "WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('MAIN_TEDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect('action.php?action=main.snippets');
            }
        } else {
            list($_POST['title'], $_POST['code']) = $db->first('SELECT title,code FROM '.PRE."_snippets WHERE id='".$_REQUEST['id']."' LIMIT 1");

            mediamanager('main');

            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('ACTION', 'edit');
            $apx->tmpl->assign('ID', $_REQUEST['id']);

            $apx->tmpl->parse('snipadd_snipedit');
        }
    }

    //***************************** HTML-Codeschnipsel löschen *****************************
    public function snippets_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'snippetdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('DELETE FROM '.PRE."_snippets WHERE id='".$_REQUEST['id']."'");
                logit('MAIN_SNIPPETDEL', 'ID #'.$_REQUEST['id']);
                printJSReload();
            }
        } else {
            list($title) = $db->first('SELECT title FROM '.PRE."_snippets WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('snipdel', ['ID' => $_REQUEST['id']]);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Tags *****************************
    public function tags()
    {
        global $set,$apx,$db,$html;

        //Aktionen
        if ('edit' == $_REQUEST['do']) {
            return $this->tags_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->tags_del();
        }
        //Suche durchführen
        if ($_REQUEST['item']) {
            $data = $db->fetch('SELECT tagid FROM '.PRE."_tags WHERE tag LIKE '%".addslashes_like($_REQUEST['item'])."%'");
            $ids = get_ids($data, 'tagid');
            $ids[] = -1;
            $searchid = saveSearchResult('admin_tags', $ids, $_REQUEST['item']);
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: action.php?action=main.tags&searchid='.$searchid);

            return;
        }

        //Suchergebnis?
        $resultFilter = '';
        if ($_REQUEST['searchid']) {
            $searchRes = getSearchResult('admin_tags', $_REQUEST['searchid']);
            if ($searchRes) {
                list($resultIds, $resultMeta) = $searchRes;
                $_REQUEST['item'] = $resultMeta;
                $resultFilter = ' AND tagid IN ('.implode(', ', $resultIds).')';
            } else {
                $_REQUEST['searchid'] = '';
            }
        }

        //Suchformular
        $apx->tmpl->assign('ITEM', compatible_hsc($_REQUEST['item']));
        $apx->tmpl->parse('tagssearch');

        $orderdef[0] = 'name';
        $orderdef['name'] = ['tag', 'ASC', 'COL_NAME'];

        $col[] = ['COL_NAME', 100, 'class="title"'];

        list($count) = $db->first('SELECT count(tagid) FROM '.PRE.'_tags WHERE 1 '.$resultFilter);
        pages('action.php?action=main.tags'.iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'], $count);
        $data = $db->fetch('SELECT tagid, tag FROM '.PRE.'_tags WHERE 1 '.$resultFilter.getorder($orderdef).getlimit());
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;
                $tabledata[$i]['COL1'] = replace($res['tag']);

                //Optionen
                $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('edit.gif', 'main.tags', 'do=edit&id='.$res['tagid'], $apx->lang->get('CORE_EDIT'));
                $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.tags', 'do=del&id='.$res['tagid'], $apx->lang->get('CORE_DEL'));
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);
    }

    //***************************** Tag bearbeiten *****************************
    public function tags_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'tagedit');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if ($_POST['send']) {
            if ($_POST['tag']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    $db->dupdate(PRE.'_tags', 'tag', "WHERE tagid='".$_REQUEST['id']."' LIMIT 1");
                    logit('MAIN_TAGSEDIT', 'ID #'.$_REQUEST['id']);
                    printJSReload();
                }

                return;
            }
        } else {
            list($_POST['tag']) = $db->first('SELECT tag FROM '.PRE."_tags WHERE tagid='".$_REQUEST['id']."' LIMIT 1");
        }

        tmessageOverlay('tagsedit', [
            'ID' => $_REQUEST['id'],
            'TAG' => compatible_hsc($_POST['tag']),
        ]);
    }

    //***************************** Tag löschen *****************************
    public function tags_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'tagdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('DELETE FROM '.PRE."_tags WHERE tagid='".$_REQUEST['id']."'");
                logit('MAIN_TAGSDEL', 'ID #'.$_REQUEST['id']);

                //Verknüpfungen löschen
                if ($apx->is_module('articles')) {
                    $db->query('DELETE FROM '.PRE."_articles_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('calendar')) {
                    $db->query('DELETE FROM '.PRE."_calendar_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('downloads')) {
                    $db->query('DELETE FROM '.PRE."_downloads_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('gallery')) {
                    $db->query('DELETE FROM '.PRE."_gallery_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('glossar')) {
                    $db->query('DELETE FROM '.PRE."_glossar_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('links')) {
                    $db->query('DELETE FROM '.PRE."_links_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('news')) {
                    $db->query('DELETE FROM '.PRE."_news_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('poll')) {
                    $db->query('DELETE FROM '.PRE."_poll_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('products')) {
                    $db->query('DELETE FROM '.PRE."_products_tags WHERE tagid='".$_REQUEST['id']."'");
                }
                if ($apx->is_module('videos')) {
                    $db->query('DELETE FROM '.PRE."_videos_tags WHERE tagid='".$_REQUEST['id']."'");
                }

                printJSReload();
            }
        } else {
            list($title) = $db->first('SELECT tag FROM '.PRE."_tags WHERE tagid='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('tagdel', ['ID' => $_REQUEST['id']]);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Smilies *****************************
    public function sshow()
    {
        global $set,$apx,$db,$html;
        $smilies = $set['main']['smilies'];
        if (!is_array($smilies)) {
            $smilies = [];
        }

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->sshow_add();
        }
        if ('edit' == $_REQUEST['do']) {
            return $this->sshow_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->sshow_del();
        }
        echo'<p class="slink">&raquo; <a href="action.php?action=main.sshow&amp;do=add">'.$apx->lang->get('ADDSMILIE').'</a></p>';
        echo'<p class="hint">'.$apx->lang->get('MAININFO').'</p>';

        $orderdef[0] = 'code';
        $orderdef['code'] = ['code', 'ASC', 'COL_CODE'];
        $orderdef['description'] = ['description', 'ASC', 'COL_DESCR'];
        $smilies = array_sort_def($smilies, $orderdef);

        $col[] = ['COL_SMILIE', 10, 'align="center"'];
        $col[] = ['COL_CODE', 25, 'align="center"'];
        $col[] = ['COL_DESCR', 65, ''];

        $count = count($smilies);
        pages('action.php?action=main.sshow&amp;sortby='.$_REQUEST['sortby'], $count);

        foreach ($smilies as $key => $info) {
            ++$i;
            if ($i <= ($_REQUEST['p'] - 1) * $set['main']['admin_epp']) {
                continue;
            }
            if ($i > $_REQUEST['p'] * $set['main']['admin_epp']) {
                break;
            }
            $tabledata[$i]['COL1'] = '<img src="'.iif('/' == substr($info['file'], 0, 1), $info['file'], ''.'../'.$info['file']).'" alt="" />';
            $tabledata[$i]['COL2'] = replace($info['code']);
            $tabledata[$i]['COL3'] = iif($info['description'], replace($info['description']), '&nbsp;');

            //Optionen
            $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'main.sshow', 'do=edit&id='.$key, $apx->lang->get('CORE_EDIT'));
            $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.sshow', 'do=del&id='.$key, $apx->lang->get('CORE_DEL'));
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        orderstr($orderdef, 'action.php?action=main.sshow');
        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Smilies hinzufügen *****************************
    public function sshow_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'sadd');
        $smilies = $set['main']['smilies'];

        if (1 == $_POST['send']) {
            foreach ($smilies as $info) {
                if ($info['code'] == $_POST['code']) {
                    $check = true;
                }
            }

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['code'] || !$_POST['file']) {
                infoNotComplete();
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } else {
                $nextkey = array_key_max($smilies) + 1;
                $smilies[$nextkey] = [
                    'code' => $_POST['code'],
                    'file' => $_POST['file'],
                    'description' => $_POST['description'],
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($smilies))."' WHERE ( module='main' AND varname='smilies' ) LIMIT 1");
                logit('MAIN_SADD', 'ID #'.$nextkey);
                printJSRedirect('action.php?action=main.sshow');
            }
        } else {
            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('FILE', compatible_hsc($_POST['file']));
            $apx->tmpl->assign('DESCRIPTION', compatible_hsc($_POST['description']));
            $apx->tmpl->assign('PREVIEW', iif($_POST['file'], iif('/' == substr($_POST['file'], 0, 1), $_POST['file'], '../'.$_POST['file']), 'design/nopic.gif'));
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('sadd_sedit');
        }
    }

    //***************************** Smilies bearbeiten *****************************
    public function sshow_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'sedit');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $smilies = $set['main']['smilies'];

        if (1 == $_POST['send']) {
            foreach ($smilies as $key => $info) {
                if ($key != $_REQUEST['id'] && $info['code'] == $_POST['code']) {
                    $check = true;
                }
            }

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['code'] || !$_POST['file']) {
                infoNotComplete();
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } else {
                $smilies[$_REQUEST['id']] = [
                    'code' => $_POST['code'],
                    'file' => $_POST['file'],
                    'description' => $_POST['description'],
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($smilies))."' WHERE ( module='main' AND varname='smilies' ) LIMIT 1");
                logit('MAIN_SEDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('main.sshow'));
            }
        } else {
            foreach ($smilies[$_REQUEST['id']] as $key => $val) {
                $_POST[$key] = $val;
            }

            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('FILE', compatible_hsc($_POST['file']));
            $apx->tmpl->assign('DESCRIPTION', compatible_hsc($_POST['description']));
            $apx->tmpl->assign('PREVIEW', iif($_POST['file'], iif('/' == substr($_POST['file'], 0, 1), $_POST['file'], '../'.$_POST['file']), 'design/nopic.gif'));
            $apx->tmpl->assign('ACTION', 'edit');
            $apx->tmpl->assign('ID', $_REQUEST['id']);

            $apx->tmpl->parse('sadd_sedit');
        }
    }

    //***************************** Smilies löschen *****************************
    public function sshow_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'sdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $smilies = $set['main']['smilies'];

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                unset($smilies[$_REQUEST['id']]);
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($smilies))."' WHERE ( module='main' AND varname='smilies' ) LIMIT 1");
                logit('MAIN_SDEL', 'ID #'.$_REQUEST['id']);
                printJSReload();
            }
        } else {
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($smilies[$_REQUEST['id']]['code'])]));
            tmessageOverlay('sdel', ['ID' => $_REQUEST['id']]);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Codes *****************************
    public function cshow()
    {
        global $set,$apx,$db,$html;
        $codes = $set['main']['codes'];
        if (!is_array($codes)) {
            $codes = [];
        }

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->cshow_add();
        }
        if ('edit' == $_REQUEST['do']) {
            return $this->cshow_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->cshow_del();
        }
        echo'<p class="slink">&raquo; <a href="action.php?action=main.cshow&amp;do=add">'.$apx->lang->get('ADDCODE').'</a></p>';
        echo'<p class="hint">'.$apx->lang->get('MAININFO').'</p>';

        $orderdef[0] = 'code';
        $orderdef['code'] = ['code', 'ASC', 'COL_CODE'];
        $codes = array_sort_def($codes, $orderdef);

        $col[] = ['COL_CODE', 10, 'align="center"'];
        $col[] = ['COL_REPLACE', 45, 'align="center"'];
        $col[] = ['COL_EXAMPLE', 45, 'align="center"'];

        $count = count($codes);
        pages('action.php?action=main.cshow&amp;sortby='.$_REQUEST['sortby'], $count);

        foreach ($codes as $key => $res) {
            ++$i;
            if ($i <= ($_REQUEST['p'] - 1) * $set['main']['admin_epp']) {
                continue;
            }
            if ($i > $_REQUEST['p'] * $set['main']['admin_epp']) {
                break;
            }
            $tabledata[$i]['COL1'] = strtoupper($res['code']);
            $tabledata[$i]['COL2'] = iif(strlen($res['replace']) > 45, replace(substr($res['replace'], 0, 42).' ...'), replace($res['replace']));
            $tabledata[$i]['COL3'] = iif($res['example'], replace($res['example']), '&nbsp;');

            //Optionen
            $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'main.cshow', 'do=edit&id='.$key, $apx->lang->get('CORE_EDIT'));
            $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.cshow', 'do=del&id='.$key, $apx->lang->get('CORE_DEL'));
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        orderstr($orderdef, 'action.php?action=main.cshow');
        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Codes hinzufügen *****************************
    public function cshow_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'cadd');
        $codes = $set['main']['codes'];

        if (1 == $_POST['send']) {
            $_POST['code'] = strtoupper($_POST['code']);
            foreach ($codes as $res) {
                if ($res['code'] == $_POST['code'] && $res['count'] == $_POST['count']) {
                    $check = true;
                }
            }

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['code'] || !$_POST['count'] || !$_POST['replace']) {
                infoNotComplete();
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } elseif ('PHP' == $_POST['code']) {
                info($apx->lang->get('INFO_BLOCK'));
            } else {
                $nextkey = array_key_max($codes) + 1;
                $codes[$nextkey] = [
                    'code' => $_POST['code'],
                    'count' => $_POST['count'],
                    'replace' => $_POST['replace'],
                    'example' => $_POST['example'],
                    'allowsig' => (int) $_POST['allowsig'],
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($codes))."' WHERE ( module='main' AND varname='codes' ) LIMIT 1");
                logit('MAIN_CADD', 'ID #'.$nextkey);
                printJSRedirect('action.php?action=main.cshow');
            }
        } else {
            $_POST['allowsig'] = $_POST['count'] = 1;

            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('REPLACE', compatible_hsc($_POST['replace']));
            $apx->tmpl->assign('EXAMPLE', compatible_hsc($_POST['example']));
            $apx->tmpl->assign('COUNT', (int) $_POST['count']);
            $apx->tmpl->assign('ALLOWSIG', (int) $_POST['allowsig']);
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('cadd_cedit');
        }
    }

    //***************************** Codes bearbeiten *****************************
    public function cshow_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'cedit');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $codes = $set['main']['codes'];

        if (1 == $_POST['send']) {
            $_POST['code'] = strtoupper($_POST['code']);
            foreach ($codes as $key => $res) {
                if ($key != $_REQUEST['id'] && $res['code'] == $_POST['code'] && $res['count'] == $_POST['count']) {
                    $check = true;
                }
            }

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['code'] || !$_POST['count'] || !$_POST['replace']) {
                infoNotComplete();
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } elseif ('PHP' == $_POST['code']) {
                info($apx->lang->get('INFO_BLOCK'));
            } else {
                $codes[$_REQUEST['id']] = [
                    'code' => $_POST['code'],
                    'count' => $_POST['count'],
                    'replace' => $_POST['replace'],
                    'example' => $_POST['example'],
                    'allowsig' => (int) $_POST['allowsig'],
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($codes))."' WHERE ( module='main' AND varname='codes' ) LIMIT 1");
                logit('MAIN_CEDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('main.cshow'));
            }
        } else {
            foreach ($codes[$_REQUEST['id']] as $key => $val) {
                $_POST[$key] = $val;
            }

            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('REPLACE', compatible_hsc($_POST['replace']));
            $apx->tmpl->assign('EXAMPLE', compatible_hsc($_POST['example']));
            $apx->tmpl->assign('COUNT', (int) $_POST['count']);
            $apx->tmpl->assign('ALLOWSIG', (int) $_POST['allowsig']);
            $apx->tmpl->assign('ACTION', 'edit');
            $apx->tmpl->assign('ID', $_REQUEST['id']);

            $apx->tmpl->parse('cadd_cedit');
        }
    }

    //***************************** Codes löschen *****************************
    public function cshow_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'cdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $codes = $set['main']['codes'];

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                unset($codes[$_REQUEST['id']]);
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($codes))."' WHERE ( module='main' AND varname='codes' ) LIMIT 1");
                logit('MAIN_CDEL', 'ID #'.$_REQUEST['id']);
                printJSReload();
            }
        } else {
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($codes[$_REQUEST['id']]['code'])]));
            tmessageOverlay('cdel', ['ID' => $_REQUEST['id']]);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Badwords *****************************

    public function bshow()
    {
        global $set,$apx,$db,$html;
        $badwords = $set['main']['badwords'];
        if (!is_array($badwords)) {
            $badwords = [];
        }

        //Aktionen
        if ('add' == $_REQUEST['do']) {
            return $this->bshow_add();
        }
        if ('edit' == $_REQUEST['do']) {
            return $this->bshow_edit();
        }
        if ('del' == $_REQUEST['do']) {
            return $this->bshow_del();
        }
        echo'<p class="slink">&raquo; <a href="action.php?action=main.bshow&amp;do=add">'.$apx->lang->get('ADDBADWORD').'</a></p>';
        echo'<p class="hint">'.$apx->lang->get('MAININFO').'</p>';

        $orderdef[0] = 'find';
        $orderdef['find'] = ['find', 'ASC', 'SORT_FIND'];
        $orderdef['replace'] = ['replace', 'ASC', 'SORT_REPLACE'];
        $badwords = array_sort_def($badwords, $orderdef);

        $col[] = ['COL_FIND', 50, ''];
        $col[] = ['COL_REPLACE', 50, ''];

        $count = count($badwords);
        pages('action.php?action=main.bshow&amp;sortby='.$_REQUEST['sortby'], $count);

        foreach ($badwords as $key => $res) {
            ++$i;
            if ($i <= ($_REQUEST['p'] - 1) * $set['main']['admin_epp']) {
                continue;
            }
            if ($i > $_REQUEST['p'] * $set['main']['admin_epp']) {
                break;
            }
            $tabledata[$i]['COL1'] = replace($res['find']);
            $tabledata[$i]['COL2'] = replace($res['replace']);

            //Optionen
            $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'main.bshow', 'do=edit&id='.$key, $apx->lang->get('CORE_EDIT'));
            $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'main.bshow', 'do=del&id='.$key, $apx->lang->get('CORE_DEL'));
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        orderstr($orderdef, 'action.php?action=main.bshow');
        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Badwords hinzufügen *****************************
    public function bshow_add()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'badd');
        $badwords = $set['main']['badwords'];

        if (1 == $_POST['send']) {
            $_POST['find'] = strtolower($_POST['find']);
            foreach ($badwords as $res) {
                if ($res['find'] == $_POST['find']) {
                    $check = true;
                }
            }

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['find'] || !$_POST['replace']) {
                infoNotComplete();
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } else {
                $nextkey = array_key_max($badwords) + 1;
                $badwords[$nextkey] = [
                    'find' => $_POST['find'],
                    'replace' => $_POST['replace'],
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($badwords))."' WHERE ( module='main' AND varname='badwords' ) LIMIT 1");
                logit('MAIN_BADD', 'ID #'.$nextkey);
                printJSRedirect('action.php?action=main.bshow');
            }
        } else {
            $apx->tmpl->assign('FIND', compatible_hsc($_POST['find']));
            $apx->tmpl->assign('REPLACE', compatible_hsc($_POST['replace']));
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('badd_bedit');
        }
    }

    //***************************** Badwords bearbeiten *****************************
    public function bshow_edit()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'bedit');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $badwords = $set['main']['badwords'];

        if (1 == $_POST['send']) {
            $_POST['find'] = strtolower($_POST['find']);
            foreach ($badwords as $key => $res) {
                if ($key != $_REQUEST['id'] && $res['find'] == $_POST['find']) {
                    $check = true;
                }
            }

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['find'] || !$_POST['replace']) {
                infoNotComplete();
            } elseif ($check) {
                info($apx->lang->get('INFO_EXISTS'));
            } else {
                $badwords[$_REQUEST['id']] = [
                    'find' => $_POST['find'],
                    'replace' => $_POST['replace'],
                ];

                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($badwords))."' WHERE ( module='main' AND varname='badwords' ) LIMIT 1");
                logit('MAIN_BEDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('main.bshow'));
            }
        }

        //Daten auslesen
        else {
            foreach ($badwords[$_REQUEST['id']] as $key => $val) {
                $_POST[$key] = $val;
            }

            $apx->tmpl->assign('FIND', compatible_hsc($_POST['find']));
            $apx->tmpl->assign('REPLACE', compatible_hsc($_POST['replace']));
            $apx->tmpl->assign('ACTION', 'edit');
            $apx->tmpl->assign('ID', $_REQUEST['id']);

            $apx->tmpl->parse('badd_bedit');
        }
    }

    //***************************** Badwords löschen *****************************
    public function bshow_del()
    {
        global $set,$apx,$db;
        $apx->lang->dropaction('main', 'bdel');
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        $badwords = $set['main']['badwords'];

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                unset($badwords[$_REQUEST['id']]);
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($badwords))."' WHERE ( module='main' AND varname='badwords' ) LIMIT 1");
                logit('MAIN_BDEL', 'ID #'.$_REQUEST['id']);
                printJSReload();
            }
        } else {
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($badwords[$_REQUEST['id']]['find'])]));
            tmessageOverlay('bdel', ['ID' => $_REQUEST['id']]);
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Protokoll *****************************
    public function log()
    {
        global $set,$apx,$db;

        logit('MAIN_LOG');
        $apx->lang->dropall('log');

        if ($apx->user->has_spright('main.log')) {
            if ($_REQUEST['clean']) {
                $this->log_clean();

                return;
            }
            if ($_REQUEST['download']) {
                $this->log_download();

                return;
            }

            echo'<p class="slink">';
            echo'&raquo; <a href="javascript:MessageOverlayManager.createLayer(\'action.php?action=main.log&amp;clean=1\');">'.$apx->lang->get('CLEANLOG').'</a><br />';
            echo'&raquo; <a href="action.php?action=main.log&amp;download=1&amp;sectoken='.$apx->session->get('sectoken').'">'.$apx->lang->get('DOWNLOADLOG').'</a>';
            echo'</p>';
        }

        $log = compatible_hsc($this->log_get(100));
        $log .= date('Y/m/d H:i:s', time() - TIMEDIFF).' &gt;&gt;&gt; LOG END';

        $apx->tmpl->assign('LOG', $log);
        $apx->tmpl->parse('log');
    }

    //PROTOKOLL GENERIEREN
    public function log_get($limit = null)
    {
        global $set,$apx,$db;

        if ($limit) {
            list($count) = $db->first('SELECT count(*) FROM '.PRE.'_log');
            $query = $db->query('SELECT a.*,b.username FROM '.PRE.'_log AS a LEFT JOIN '.PRE.'_user AS b USING(userid) ORDER BY time ASC LIMIT '.max([$count - $limit, 0]).','.$limit);
        } else {
            $query = $db->query('SELECT a.*,b.username FROM '.PRE.'_log AS a LEFT JOIN '.PRE.'_user AS b USING(userid) ORDER BY time ASC');
        }
        $strlen_ip = 3 + 1 + 3 + 1 + 3 + 1 + 3;
        $strlen_username = 30;

        //Log zusammensetzen
        while ($res = $query->fetch_array()) {
            $out .= $res['time'].'   ';
            $out .= str_pad($res['username'], $strlen_username).'   ';
            $out .= str_pad($res['ip'], $strlen_ip).'   ';
            $out .= $res['text'];
            if ('' != $res['affects']) {
                $out .= ' -> '.$res['affects'];
            }
            $out .= "\n";
        }
        $db->free_result($query);

        //Sprachplatzhalter
        $out = $apx->lang->insertpack($out);

        return $out;
    }

    //PROTOKOLL LEEREN
    public function log_clean()
    {
        global $set,$apx,$db;

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('DELETE FROM '.PRE.'_log');
                logit('MAIN_LOG_CLEAN');
                printJSReload();
            }
        } else {
            tmessageOverlay('logclean');
        }
    }

    //LOG HERUNTERLADEN
    public function log_download()
    {
        if (!checkToken()) {
            printInvalidToken();
        } else {
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename=apexx_'.date('Y-m-d_H-i-s', time() - TIMEDIFF).'.log');
            header('Accept-Ranges: bytes');

            echo strtr($this->log_get(), ['&gt;' => '>', '&lt;' => '<']);

            exit;
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Umgebungsvariablen *****************************
    public function env()
    {
        global $set,$apx,$db;

        list($count_user) = $db->first('SELECT count(userid) FROM '.PRE.'_user');
        list($count_group) = $db->first('SELECT count(groupid) FROM '.PRE.'_user_groups');

        $apx->tmpl->assign('SERVER', $_SERVER['SERVER_SOFTWARE']);
        $apx->tmpl->assign('PHP', phpversion());
        $apx->tmpl->assign('MYSQL', $db->server_info());
        $apx->tmpl->assign('VERSION', VERSION);
        $apx->tmpl->assign('C_USER', $count_user);
        $apx->tmpl->assign('C_GROUP', $count_group);
        $apx->tmpl->assign('PATH_BASEDIR', BASEDIR);
        $apx->tmpl->assign('PATH_MODULES', BASEDIR.getpath('moduledir'));
        $apx->tmpl->assign('PATH_UPLOADS', BASEDIR.getpath('uploads'));

        //Funktionen
        $tfuncs = $apx->functions;
        ksort($tfuncs);

        foreach ($tfuncs as $module => $funcs) {
            ksort($funcs);

            foreach ($funcs as $name => $info) {
                ++$i;
                $funcdata[$i]['FUNC'] = '{'.$name.'()}';
                $funcdata[$i]['FUNCNAME'] = $info[0].'()';
                $funcdata[$i]['PARAMS'] = iif(true == $info[1], $apx->lang->get('YES'), $apx->lang->get('NO'));
                $funcdata[$i]['MODULE'] = $module;
            }
        }

        $apx->tmpl->assign('TFUNC', $funcdata);
        $apx->tmpl->parse('env');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Gesuchte Begriffe *****************************
    public function searches()
    {
        global $set,$apx,$db;

        $data = $db->fetch('
		SELECT item,count(item) AS count
		FROM '.PRE."_search_item
		WHERE time>='".(time() - 30 * 24 * 3600)."'
		GROUP BY item
		ORDER by count DESC
	");
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;
                $tabledata[$i]['HITS'] = number_format($res['count'], 0, '', '.');
                $tabledata[$i]['STRING'] = htmlentities($res['item'], ENT_QUOTES, 'UTF-8');
            }
        }

        $apx->tmpl->assign('SEARCH', $tabledata);
        $apx->tmpl->parse('searches');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Seite schließen *****************************
    public function close()
    {
        global $set,$apx,$db;

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['message']) {
                infoNotComplete();
            } else {
                $db->query('UPDATE '.PRE."_config SET value=1 WHERE ( varname='closed' AND module='main' ) LIMIT 1");
                $db->query('UPDATE '.PRE."_config SET value='".addslashes($_POST['message'])."' WHERE ( varname='close_message' AND module='main' ) LIMIT 1");
                logit('MAIN_CLOSE');
                printJSRedirect('action.php?action=main.close');

                return;
            }
        } elseif (2 == $_POST['send']) {
            $db->query('UPDATE '.PRE."_config SET value=0 WHERE ( varname='closed' AND module='main' ) LIMIT 1");
            logit('MAIN_CLOSE_OPEN');
            printJSRedirect('action.php?action=main.close');
        } else {
            $apx->tmpl->assign('CLOSED', $set['main']['closed']);
            $apx->tmpl->assign('MESSAGE', compatible_hsc($set['main']['close_message']));
            $apx->tmpl->parse('close');
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //***************************** Cache leeren *****************************
    public function delcache()
    {
        global $set,$apx,$db;

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $apx->tmpl->clear_cache();
                logit('MAIN_DELCACHE');
                message($apx->lang->get('MSG_OK'), 'action.php?action=main.index');
            }
        } else {
            tmessage('delcache', $insert);
        }
    }
} //END CLASS
