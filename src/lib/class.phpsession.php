<?php

class session
{
    public $varname;
    public $now;
    public $sessionId = '';
    public $ownerId = '';
    public $data = [];
    public $expires = 12;
    public $modified = false;

    //Session erzeugen
    public function session($varname = 'sid')
    {
        $this->varname = $varname;
        session_name($this->varname);
        $this->now = time();
        $this->ownerId = $this->getOwnerId();

        //URL-Rewriter deaktivieren
        @ini_set('url_rewriter.tags', false);

        //Automatisches setzen von Cookie deaktivieren
        @ini_set('session.use_cookies', false);

        //Versuch aktuelle Session zu übernehmen
        if ($sid = $this->getCookieSid()) {
            $this->sessionId = $sid;
            $this->resumeSession();
        }

        //Neue Session erzeugen wenn Übernahme gescheitert oder keine Sid
        if (!$this->sessionId) {
            $this->createSession();
        }
    }

    //Neue Session erzeugen
    public function createSession()
    {
        session_start();
        $this->sessionId = session_id();
        $_SESSION['__ownerid'] = $this->getOwnerId();

        //Cookie setzen
        $this->setCookieSid();
    }

    //Session wiederaufnehmen
    public function resumeSession()
    {
        session_id($this->sessionId);
        session_start();

        //Anscheinend eine neue Session
        if (!isset($_SESSION['__ownerid'])) {
            $_SESSION['__ownerid'] = $this->getOwnerId();
        }

        //Session kann nicht aufgenommen werden => Neu erzeugen
        while (isset($_SESSION['__ownerid']) && $_SESSION['__ownerid'] != $this->getOwnerId()) {
            session_write_close();

            //Neue Session starten
            $this->sessionId = md5(uniqid('newsession').microtime());
            session_id($this->sessionId);
            session_start();

            //Cookie überschreiben
            $this->setCookieSid();
        }
    }

    //Session-ID zurückgeben
    public function getSid()
    {
        return $this->sessionId;
    }

    //Session-Variable setzen
    public function set($varname, $value)
    {
        $_SESSION['_apxses_'.$varname] = $value;
    }

    //Session-Variable auslesen
    public function get($varname)
    {
        if (isset($_SESSION['_apxses_'.$varname])) {
            return $_SESSION['_apxses_'.$varname];
        }

        return null;
    }

    //Session-Variable löschen
    public function clear($varname, $value)
    {
        unset($_SESSION['_apxses_'.$varname]);
    }

    //Session-Daten speichern
    public function save()
    {
    }

    //Session beenden
    public function destroy()
    {
        @session_destroy();

        //Cookie löschen
        $this->unsetCookieSid();
    }

    //Owner-ID erzeugen
    public function getOwnerId()
    {
        $ip = implode('.', array_slice(explode('.', get_remoteaddr()), 0, 3));

        return md5(getenv('HTTP_USER_AGENT').$ip);
    }

    //Session-ID aus Cookie auslesen
    public function getCookieSid()
    {
        global $set;

        return $_COOKIE[$set['main']['cookie_pre'].'_admin_sid'];
    }

    //Session-ID in Cookie setzen
    public function setCookieSid()
    {
        global $set;
        setcookie($set['main']['cookie_pre'].'_admin_sid', $this->sessionId);
        $_COOKIE[$set['main']['cookie_pre'].'_admin_sid'] = $this->sessionId;
    }

    //Session-ID Cookie entfernen
    public function unsetCookieSid()
    {
        setcookie($set['main']['cookie_pre'].'_admin_sid', '', time() - 999999);
        $_COOKIE[$set['main']['cookie_pre'].'_admin_sid'] = '';
    }
}
