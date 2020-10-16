<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class language
{
    public $langpack = [];
    public $cached = [];
    public $dropped = [];
    public $loaded = [];
    public $insertcache = [];

    public $langdir;
    public $lang;
    public $langid;

    ////////////////////////////////////////////////////////////////////////////////// -> STARTUP

    public function init()
    {
        if (!$this->langid) {
            die('can not load langpack, no langid defined!');
        }

        $this->core_load();     //Core Sprachpakete in den Cache laden und ablegen

        if (MODE == 'admin') {
            $this->dropall('modulename'); //Diesen Teil aller Modul-Sprachpakete ablegen (Modul-Name -> für Navi)
        $this->dropall('navi');       //Diesen Teil aller Modul-Sprachpakete ablegen (Navigation)
        $this->dropall('titles');     //Diesen Teil aller Modul-Sprachpakete ablegen (Titel)
        }
    }

    ////////////////////////////////////////////////////////////////////////////////// -> ALLGEMEINE FUNKTIONEN

    //Sprachpaket wählen
    public function langid($id = false)
    {
        global $apx;
        if (false === $id) {
            return $this->langid;
        }
        if (isset($apx->languages[$id])) {
            $this->langid = $id;
        }
    }

    //Pfad holen
    public function langpath($module)
    {
        global $apx;

        if ('/' == $module) {
            $this->langdir = getpath('lang_base', ['MODULE' => $module, 'LANGID' => $this->langid()]);
        } else {
            $this->langdir = getpath('lang_modules', ['MODULE' => $module, 'LANGID' => $this->langid()]);
        }
    }

    //Daten von Sprachpaket holen
    public function getpack($pack)
    {
        global $apx,$user;

        $langdir = $this->langdir;

        $langSystem = [];
        $langUser = [];

        //System-Sprachpaket laden
        if (file_exists(BASEDIR.$langdir.$pack.'.php')) {
            $lang = [];
            include_once BASEDIR.$langdir.$pack.'.php';
            if (is_array($lang)) {
                $langSystem = $lang;
            }
        } else {
            error('Sprachpaket '.$langdir.$pack.'.php nicht vorhanden!');
        }

        //User-Sprachpaket laden
        if (file_exists(BASEDIR.$langdir.$pack.'_byuser.php')) {
            $lang = [];
            include_once BASEDIR.$langdir.$pack.'_byuser.php';
            if (is_array($lang)) {
                $langUser = $lang;
            }
        }

        //Merge
        $lang = array_replace_recursive($langSystem, $langUser);

        /*else {
            //Paket der Standard-Sprache laden
            if ( file_exists(BASEDIR.$langdir.$apx->language_default.'/'.$pack.'.php') ) {
                include_once(BASEDIR.$langdir.$apx->language_default.'/'.$pack.'.php');
            }
            else error('Sprachpaket '.$pack.'.php nicht vorhanden!');
        }*/

        $this->lang = $lang;

        return $this->lang;
    }

    //Sprachpaket speichern
    public function cache($module)
    {
        $this->langpath($module);
        $this->getpack(MODE);
        $this->cached[$module] = $this->lang;
    }

    ////////////////////////////////////////////////////////////////////////////////// -> PAKETE LADEN

    //Core Sprachpaket laden
    public function core_load()
    {
        $this->langpath('/');

        $this->getpack('global');
        $this->mergepack();

        $this->getpack(MODE);
        $this->mergepack();
    }

    //Sprachpakete aller Module in den Speicher laden
    public function module_load($modulename)
    {
        global $apx;

        if (!$this->is_loaded($modulename)) {
            $this->cache($modulename);
            $this->set_loaded($modulename);
        }
    }

    //Sprachpaket wurde geladen
    public function set_loaded($modulename)
    {
        $this->loaded[] = $modulename;
    }

    //Ist ein Sprachpaket geladen?
    public function is_loaded($modulename)
    {
        return in_array($modulename, $this->loaded);
    }

    ////////////////////////////////////////////////////////////////////////////////// -> PAKET ABLEGEN

    //Daten dem Sprachpaket hinzufügen
    public function mergepack($lang = false)
    {
        if (false === $lang) {
            $lang = $this->lang;
        }
        $this->langpack = array_merge($this->langpack, $lang);
    }

    //Von einem bestimmten Modul den Teil mit Namen $type ablegen
    public function drop($type, $module = false)
    {
        global $apx;

        if (false === $module) {
            $module = $apx->module();
        }
        $this->module_load($module);

        if (!is_array($this->cached[$module][$type])) {
            return;
        }
        if ($this->is_dropped($module.'-'.$type)) {
            return;
        }
        $this->mergepack($this->cached[$module][$type]);
        $this->dropped($module.'-'.$type);
    }

    //Von allen Modulen den Teil mit Namen $type ablegen
    public function dropall($type)
    {
        global $apx;

        foreach ($this->cached as $module => $langpack) {
            $this->drop($type, $module);
        }
        foreach ($apx->modules as $module => $trash) {
            $this->drop($type, $module);
        }
    }

    //Von einem bestimmten Modul aus dem Teil "actions" das Sprachpaket der Aktion $action ablegen
    public function dropaction($module = false, $action = false)
    {
        global $apx;

        if (false === $module) {
            $module = $apx->module();
        }
        $this->module_load($module);

        if (false === $action) {
            $action = $apx->action();
        }
        if (!is_array($this->cached[$module]['actions'][$action])) {
            return;
        }
        if ($this->is_dropped($module.'-action-'.$action)) {
            return;
        }
        $this->mergepack($this->cached[$module]['actions'][$action]);
        $this->dropped($module.'-action-'.$action);
    }

    //Als abgelegt markieren
    public function dropped($id)
    {
        $this->dropped[] = $id;
    }

    //Ist etwas abgelegt?
    public function is_dropped($id)
    {
        if (in_array($id, $this->dropped)) {
            return true;
        }

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////// -> SPRACHE EINFÜGEN

    //Platzhalter in den Sprach-Strings ersetzen
    public function insert($text, $input)
    {
        if (!is_array($input) || !count($input)) {
            return $text;
        }
        foreach ($input as $find => $replace) {
            $text = str_replace('{'.$find.'}', $replace, $text);
        }

        return $text;
    }

    //Sprachpaket einfügen
    public function insertpack($text)
    {
        return $this->insert($text, $this->langpack);
    }

    //Sprach-Platzhalter
    public function get($id, $input = [])
    {
        $lang = $this->langpack[$id];
        if (!is_array($input) || !count($input)) {
            return $lang;
        }

        return $this->insert($lang, $input);
    }

    //Langpack ausgeben
    public function get_langpack()
    {
        return $this->langpack;
    }
} //END CLASS
