<?php 

class session {
	
	var $varname;
	var $now;
	var $sessionId = '';
	var $ownerId = '';
	var $data = array();
	var $expires = 12;
	var $modified = false;
	
	
	
	//Session erzeugen
	function session($varname = 'sid') {
		$this->varname = $varname;
		$this->now = time();
		$this->ownerId = $this->getOwnerId();
		
		//Versuch aktuelle Session zu übernehmen
		if ( $sid = $this->getRequestSid() ) {
			$this->sessionId = $sid;
			$this->resumeSession();
		}
		
		//Neue Session erzeugen wenn Übernahme gescheitert oder keine Sid
		if ( !$this->sessionId ) {
			$this->createSession();
		}
	}
	
	
	
	//Neue Session erzeugen
	function createSession() {
		global $db,$set;
		
		/*
		Keine Ahnung wozu das gut sein sollte... O_o
		do {
			$this->sessionId = md5(uniqid('session'));
			list($check) = $this->reg->db->fetchFirst("
				SELECT id FROM ".PRE."_sessions
				WHERE id='".addslashes($this->sessionId)."' AND ownerid='".addslashes($this->ownerId)."' AND starttime>'".($this->now-$this->expires*3600)."'
				LIMIT 1
			");
		}
		while( $check );
		*/
		
		//Session in DB erzeugen
		$this->data = array();
		do {
			$this->sessionId = md5(uniqid('session').microtime());
			$db->query("
				INSERT IGNORE INTO ".PRE."_sessions
				VALUES ('".addslashes($this->sessionId)."','".addslashes($this->ownerId)."','".$this->now."','".addslashes(serialize($this->data))."')
			");
		}
		while ( $db->affected_rows()==0 );
		
		$cookiename = $set['main']['cookie_pre'].'_'.$this->varname;
		setcookie($cookiename,$this->sessionId,0,'/');
	}
	
	
	
	//Session wiederaufnehmen
	function resumeSession() {
		global $db;
		
		//Session-Daten auslesen
		$res = $db->first("
			SELECT *
			FROM ".PRE."_sessions
			WHERE id='".addslashes($this->sessionId)."' AND ownerid='".addslashes($this->ownerId)."' AND starttime>'".($this->now-$this->expires*3600)."'
			LIMIT 1
		");
		
		//Session existiert
		if ( $res['id'] ) {
			$this->sessionId = $res['id'];
			$this->data = unserialize($res['data']);
		}
		
		//Session existiert nicht => löschen
		else {
			$this->sessionId = '';
		}
	}
	
	
	
	//Session-ID zurückgeben
	function getSid() {
		return $this->sessionId;
	}
	
	
	
	//Session-Variable setzen
	function set($varname, $value, $forcesave = false) {
		$this->data[$varname] = $value;
		$this->modified = true;
		if ( $forcesave ) {
			$this->save();
		}
	}
	
	
	
	//Session-Variable auslesen
	function get($varname) {
		if ( isset($this->data[$varname]) ) return $this->data[$varname];
		else return null;
	}
	
	
	
	
	//Session-Variable löschen
	function clear($varname, $forcesave = false) {
		unset($this->data[$varname]);
		$this->modified = true;
		if ( $forcesave ) {
			$this->save();
		}
	}
	
	
	
	//Session-Daten speichern
	function save() {
		global $db;
		if ( !$this->modified ) return;
		$db->query("
			UPDATE ".PRE."_sessions
			SET 
				data='".addslashes(serialize($this->data))."',
				starttime='".time()."'
			WHERE id='".addslashes($this->sessionId)."' AND ownerid='".addslashes($this->ownerId)."'
			LIMIT 1
		");
		$this->modified = false;
	}
	
	
	
	//Session beenden
	function destroy() {
		global $db;
		$db->query("
			DELETE FROM ".PRE."_sessions
			WHERE id='".addslashes($this->sessionId)."' AND ownerid='".addslashes($this->ownerId)."'
		");
	}
	
	
	
	//Owner-ID erzeugen
	function getOwnerId() {
		$ip = implode('.', array_slice(explode('.', get_remoteaddr()), 0, 3));
		return md5(getenv('HTTP_USER_AGENT').$ip);
	}
	
	
	
	//Session-ID aus Cookie auslesen
	function getRequestSid() {
		global $set;
		$cookiename = $set['main']['cookie_pre'].'_'.$this->varname;
		return $_COOKIE[$cookiename];
	}

}

?>