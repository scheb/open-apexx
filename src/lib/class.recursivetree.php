<?php


class RecursiveTree {

	var $table;
	var $primary;
	var $db;



	//Konstruktor
	function __construct($table, $primary) {
		$this->table = $table;
		$this->primary = $primary;
	}



	//Prüft, ob ein Knoten existiert
	function nodeExists($id) {
		global $db;

		$res = $db->first("
			SELECT ".$this->primary." AS id
			FROM ".$this->table."
			WHERE ".$this->primary."=".$id."
			LIMIT 1
		");
		if ( $res['id'] ) {
			return true;
		}
		else {
			return false;
		}
	}



	//Liste der Payload-Felder erzeugen
	function getPayloadFields($payload) {
		if ( in_array('*', $payload) ) {
			$payloadFields = '* ,';
		}
		elseif ( $payload ) {
			$payloadFields = '`'.implode('`, `', $payload).'` ,';
		}
		return $payloadFields;
	}



	//Ergebnis verarbeiten
	function processResult($res) {
		$res['children'] = dash_unserialize($res['children']);
		$res['parents'] = dash_unserialize($res['parents']);
		return $res;
	}



	//Knoten-Info auslesen
	function getNode($id, $payload=array()) {
		global $db;

		$payloadFields = $this->getPayloadFields($payload);;

		$res = $db->first("
			SELECT ".$payloadFields." ".$this->primary.", parents, children, ord
			FROM ".$this->table."
			WHERE ".$this->primary."=".$id."
			LIMIT 1
		");
		if ( !$res ) return null;
		$res = $this->processResult($res);
		return $res;
	}



	//Kategorien auslesen
	function getTree($payload=array(), $nodeId=null, $where=null) {
		$result = array();

		$payloadFields = $this->getPayloadFields($payload);;

		if ( $nodeId ) {
			$nodeInfo = $this->getNode($nodeId);
			if ( $nodeInfo ) {
				$result = $this->getTreeRec(dash_serialize(array_merge($nodeInfo['parents'], array($nodeId))), $payloadFields, $where);
			}
		}
		else {
			$result = $this->getTreeRec('|', $payloadFields, $where);
		}

		return $result;
	}



	//Rekursives Auslesen der Kategorien
	function getTreeRec($parents, $payload, $where, $level = 1) {
		global $db;
		$result = array();

		$query = $db->query("
			SELECT ".$payload." `".$this->primary."`, children, parents
			FROM ".$this->table."
			WHERE parents='".addslashes($parents)."' ".($where ? ' AND '.$where : '')."
			ORDER BY ord ASC
		");
		while ( $res = $query->fetch_array() ) {
			$res = $this->processResult($res);
			$res['level'] = $level;
			$result[] = $res;
			if ( $res['children'] ) {
				$result = array_merge($result, $this->getTreeRec($parents.$res[$this->primary].'|', $payload, $where, $level+1));
			}
		}

		return $result;
	}



	//Eine Ebene im Baum auslesen
	function getLevel($payload, $id=0, $where='') {
		global $db;

		$payloadFields = $this->getPayloadFields($payload);

		if ( $id ) {
			$search = "parents LIKE '%|".$id."|'";
		}
		else {
			$search = "parents='|'";
		}

		$result = array();
		$query = $db->query("
			SELECT ".$payloadFields." `".$this->primary."`, children, parents
			FROM ".$this->table."
			WHERE ".$search." ".($where ? ' AND '.$where : '')."
			ORDER BY ord ASC
		");
		$level = 0;
		while ( $res = $query->fetch_array() ) {
			++$level;
			$res = $this->processResult($res);
			$res['level'] = $level;
			$result[] = $res;
		}

		return $result;
	}



	//Pfad zu einem Knoten auslesen
	function getPathTo($payload, $id) {
		global $db;

		$payloadFields = $this->getPayloadFields($payload);;

		$result = array();
		$query = $db->query("
			SELECT ".$payloadFields." `".$this->primary."`, children, parents
			FROM ".$this->table."
			WHERE children LIKE '%|".$id."|%' OR ".$this->primary."=".$id."
			ORDER BY parents ASC
		");
		$level = 0;
		while ( $res = $query->fetch_array() ) {
			++$level;
			$res = $this->processResult($res);
			$res['level'] = $level;
			$result[] = $res;
		}

		return $result;
	}



	//Maximalen Ord-Wert der Kindkonten auslsen
	function getChildrenNextOrd($id) {
		global $db;

		if ( $id ) {
			$where = "parents LIKE '%|".$id."|'";
		}
		else {
			$where = "parents='|'";
		}
		$res = $db->first("
			SELECT max(ord) AS ord
			FROM ".$this->table."
			WHERE ".$where."
		");
		if ( $res ) {
			return $res['ord']+1;
		}
		else {
			return 0;
		}
	}



	//Ids der Kindknoten eines Knoten auslesen
	function getChildrenIds($id) {
		global $db;
		$res = $db->first("
			SELECT children
			FROM ".$this->table."
			WHERE ".$this->primary."=".$id."
			LIMIT 1
		");
		if ( !$res ) return array();
		return dash_unserialize($res['children']);
	}



	//Kinder bei Knoten entfernen
	function removeChildren($nodeIds, $childIds) {
		global $db;
		if ( !$childIds || !$nodeIds ) return;

		$query = $db->query("
			SELECT ".$this->primary.", children
			FROM ".$this->table."
			WHERE ".$this->primary." IN (".implode(', ', $nodeIds).")
		");

		while ( $res = $query->fetch_array() ) {
			$children = dash_unserialize($res['children']);
			$children = array_diff($children, $childIds);
			$db->query("
				UPDATE ".$this->table."
				SET children='".dash_serialize($children)."'
				WHERE ".$this->primary."=".$res[$this->primary]."
				LIMIT 1
			");
		}
	}



	//Kinder bei Knoten hinzufügen
	function addChildren($nodeIds, $childIds) {
		global $db;
		if ( !$childIds || !$nodeIds ) return;
		$db->query("
			UPDATE ".$this->table."
			SET children=CONCAT(children, '".implode('|', $childIds)."|')
			WHERE ".$this->primary." IN (".implode(', ', $nodeIds).")
		");
	}



	//Knoten erzeugen
	function createNode($parentId, $payload = null) {
		global $db;

		//Parent-Info auslesen
		if ( $parentId ) {
			$parentInfo = $this->getNode($parentId);
			if ( !$parentInfo ) return false;
			$parentIds = array_merge($parentInfo['parents'], array($parentId));
		}

		//Parent ist Root
		else {
			$parentIds = array();
		}

		//Daten für DB vorbereiten
		if ( is_array($payload) ) {
			$sqldata = $payload;
		}
		else {
			$sqldata = array();
		}

		//In einen bestehenden Knoten einfügen
		$sqldata['parents'] = dash_serialize($parentIds);
		$sqldata['children'] = '|';
		$sqldata['ord'] = $this->getChildrenNextOrd($parentId);


		//In DB eintragen
		$db->insert($this->table, $sqldata);
		$nodeid = $db->insert_id();

		//Bei den Parents den Knoten in die Children eintragen
		if ( $parentIds ) {
			$this->addChildren($parentIds, array($nodeid));
		}

		return $nodeid;
	}



	//Knoten verschieben
	function moveNode($id, $parentId, $payload = null) {
		global $db;

		//Kann nicht Child von sich selbst werden
		if ( $id==$parentId ) return false;

		//Knoten-Info auslesen
		$nodeInfo = $this->getNode($id);
		if ( !$nodeInfo ) return false;
		$oldOrd = $nodeInfo['ord'];
		if ( $nodeInfo['parents'] ) {
			$oldParentIds = $nodeInfo['parents'];
			$currentParentId = $oldParentIds[count($nodeInfo['parents'])-1];
		}
		else {
			$oldParentIds = array();
			$currentParentId = 0;
		}

		//Ist der neue Knoten erlaubt?
		//Parent darf kein Kindknoten des Knotens sein
		if ( in_array($parentId, $nodeInfo['children']) ) return false;

		//Daten für DB vorbereiten
		if ( is_array($payload) ) {
			$sqldata = $payload;
		}
		else {
			$sqldata = array();
		}

		//Es gibt nur etwas zu tun, wenn Parent sicht ändert
		if ( $currentParentId!=$parentId ) {
			$childrenOldParents = dash_serialize(array_merge($nodeInfo['parents'], array($id)));

			//Neuer Parent ist Root
			if ( !$parentId ) {
				$newParentIds = array();
				$nodeNewParents = '|';
				$childrenNewParents = '|'.$id.'|';
			}

			//Neuer Parent ist ein normaler Knoten
			else {
				$newParentInfo = $this->getNode($parentId);
				$newParentIds = array_merge($newParentInfo['parents'], array($parentId));
				$nodeNewParents = dash_serialize($newParentIds);
				$childrenNewParents = dash_serialize(array_merge($newParentIds, array($id)));
			}

			//Ord-Wert bei den ehemaligen Nachfolgern anpassen
			$db->query("
				UPDATE ".$this->table."
				SET ord=ord-1
				WHERE parents='".dash_serialize($oldParentIds)."' AND ord>".$oldOrd."
			");

			//Kindknoten auslesen
			$childIds = $this->getChildrenIds($id);

			//Bei den Kindknoten neue Parents eintragen
			if ( $childIds ) {
				$replData = $db->fetch("
					SELECT ".$this->primary." AS id, parents
					FROM ".$this->table."
					WHERE ".$this->primary." IN (".implode(', ', $childIds).")
				");
				foreach ( $replData AS $replRes ) {
					$db->query("
						UPDATE ".$this->table."
						SET parents='".str_replace($childrenOldParents, $childrenNewParents, $replRes['parents'])."'
						WHERE ".$this->primary." = ".$replRes['id']."
						LIMIT 1
					");
				}
			}

			//Bei den alten Parents die Kinder und den Knoten selbst entfernen
			if ( $oldParentIds ) {
				$this->removeChildren($oldParentIds, array_merge(array($id), $childIds));
			}

			//Bei den neuen Parents die Kinder und den Knoten selbst eintragen
			if ( $newParentIds ) {
				$this->addChildren($newParentIds, array_merge(array($id), $childIds));
			}

			//Den Knoten selbst aktualisieren
			$sqldata['parents'] = $nodeNewParents;

			//Ord-Wert
			$sqldata['ord'] = $this->getChildrenNextOrd($parentId);
		}

		//Knoten selbst aktualisieren
		if ( $sqldata ) {
			$db->update($this->table, $sqldata, "WHERE ".$this->primary."=".$id." LIMIT 1");
		}
		return true;
	}



	//Vor einem bestimmten Knoten einfügen
	function moveNodeBefore($id, $parentId, $refId, $payload = null) {
		$refNode = $this->getNode($refId);
		if ( $id==$refId ) return false;
		if ( !$refNode ) return false;
		if ( $refNode['parents'][count($refNode['parents'])-1]!=$parentId ) return false;

		//Knoten einfügen
		$feedback = $this->moveNode($id, $parentId, $payload);

		//Position anpassen
		if ( $feedback ) {
			$this->moveBefore($id, $refNode);
		}

		return $feedback;
	}



	//Nach einem bestimmten Knoten einfügen
	function moveNodeAfter($id, $parentId, $refId, $payload = null) {
		$refNode = $this->getNode($refId);
		if ( $id==$refId ) return false;
		if ( !$refNode ) return false;
		if ( $refNode['parents'][count($refNode['parents'])-1]!=$parentId ) return false;

		//Knoten einfügen
		$feedback = $this->moveNode($id, $parentId, $payload);

		//Position anpassen
		if ( $feedback ) {
			$this->moveAfter($id, $refNode);
		}

		return $feedback;
	}



	//Vor einen Knoten verschieben
	function moveBefore($id, $targetNode) {
		global $db;

		$nodeInfo = $this->getNode($id);
		$ord = $nodeInfo['ord'];

		$targetId = $targetNode[$this->primary];
		$targetOrd = $targetNode['ord'];

		//Sonderfall
		if ( $ord<$targetOrd ) {
			$targetOrd -= 1;
		}

		$db->query("
			UPDATE ".$this->table."
			SET ord=ord".($ord<$targetOrd ? '-' : '+')."1
			WHERE ord BETWEEN ".min(array($ord, $targetOrd))." AND ".max(array($ord, $targetOrd))." AND parents='".dash_serialize($nodeInfo['parents'])."'
		");

		$db->query("
			UPDATE ".$this->table."
			SET ord=".$targetOrd."
			WHERE ".$this->primary."='".$id."'
			LIMIT 1
		");
	}



	//Nach einen Knoten verschieben
	function moveAfter($id, $targetNode) {
		global $db;

		$nodeInfo = $this->getNode($id);
		$ord = $nodeInfo['ord'];

		$targetId = $targetNode[$this->primary];
		$targetOrd = $targetNode['ord'];

		//Sonderfall
		if ( $ord>$targetOrd ) {
			$targetOrd += 1;
		}

		$db->query("
			UPDATE ".$this->table."
			SET ord=ord".($ord<$targetOrd ? '-' : '+')."1
			WHERE ord BETWEEN ".min(array($ord, $targetOrd))." AND ".max(array($ord, $targetOrd))." AND parents='".dash_serialize($nodeInfo['parents'])."'
		");

		$db->query("
			UPDATE ".$this->table."
			SET ord=".$targetOrd."
			WHERE ".$this->primary."='".$id."'
			LIMIT 1
		");
	}



	//Knoten verschieben
	function swapNode($id, $direction) {
		global $db;
		$nodeInfo = $this->getNode($id);
		if ( !$nodeInfo ) return;

		//Nach unten schieben
		if ( $direction=='down' ) {
			$brother = $db->first("
				SELECT ".$this->primary.", ord
				FROM ".$this->table."
				WHERE parents='".dash_serialize($nodeInfo['parents'])."' AND ord>".$nodeInfo['ord']."
				ORDER BY ord ASC
				LIMIT 1
			");
		}

		//Nach oben schieben
		else {
			$brother = $db->first("
				SELECT ".$this->primary.", ord
				FROM ".$this->table."
				WHERE parents='".dash_serialize($nodeInfo['parents'])."' AND ord<".$nodeInfo['ord']."
				ORDER BY ord DESC
				LIMIT 1
			");
		}

		if ( $brother ) {
			$db->query("
				UPDATE ".$this->table."
				SET ord=".($nodeInfo['ord']+$brother['ord'])."-ord
				WHERE ".$this->primary." IN (".$nodeInfo[$this->primary].", ".$brother[$this->primary].")
			");
		}
	}



	//Kann ein Knoten gelöscht werden?
	function canDeleteNode($id) {
		$nodeInfo = $this->getNode($id);
		return ( $nodeInfo && !$nodeInfo['children'] );
	}



	//Knoten löschen
	function deleteNode($id) {
		global $db;

		//Kann der Knoten gelöscht werden?
		$nodeInfo = $this->getNode($id);
		if ( !$nodeInfo || $nodeInfo['children'] ) {
			return false;
		}

		//Knoten löschen
		$db->query("
			DELETE FROM ".$this->table."
			WHERE ".$this->primary."=".$id."
			LIMIT 1
		");

		//Nachfolgende Knoten nachrücken lassen
		$db->query("
			UPDATE ".$this->table."
			SET ord=ord-1
			WHERE parents='".dash_serialize($nodeInfo['parents'])."' AND ord>".$nodeInfo['ord']."
		");

		return true;
	}



	//Subtree löschen
	function deleteSubtree($id) {
		global $db;

		$nodeInfo = $this->getNode($id);
		if ( !$nodeInfo ) {
			return false;
		}

		//Kinder löschen
		if ( $nodeInfo['children'] ) {
			$db->query("
				DELETE FROM ".$this->table."
				WHERE ".$this->primary." IN (".implode(', ', $nodeInfo['children']).")
			");
		}


		//Kinder bei den Parents entfernen
		if ( $nodeInfo['parents'] ) {
			$deleteChildren = array_merge(array($id), $nodeInfo['children']);
			$replData = $db->fetch("
				SELECT ".$this->primary." AS id, children
				FROM ".$this->table."
				WHERE ".$this->primary." IN (".implode(',', $nodeInfo['parents']).")
			");
			foreach ( $replData AS $replRes ) {
				$tempChildren = $replRes['children'];
				foreach ( $deleteChildren AS $deleteChild ) {
					$tempChildren = str_replace('|'.$deleteChild.'|', '|', $tempChildren);
				}
				$db->query("
					UPDATE ".$this->table."
					SET children=".$tempChildren."
					WHERE ".$this->primary." = ".$replRes['id']."
					LIMIT 1
				");
			}
		}

		//Knoten selbst löschen
		$db->query("
			DELETE FROM ".$this->table."
			WHERE ".$this->primary."=".$id."
			LIMIT 1
		");

		//Nachfolgende Knoten nachrücken lassen
		$db->query("
			UPDATE ".$this->table."
			SET ord=ord-1
			WHERE parents='".dash_serialize($nodeInfo['parents'])."' AND ord>".$nodeInfo['ord']."
		");

		return true;
	}



	//Struktur reparieren
	function repair() {
		global $db;

		$lastlevel = 0;
		$ord = array();
		$parents = array();

		//Funktionsfähigen Teil auslesen
		$nodes = $this->getTree();
		$nodeCount = count($nodes);
		$nodeIds = get_ids($nodes, $this->primary);
		$nodeIds[] = -1;

		//Parents, Childrend und Ord zurücksetzen
		$db->query("
			UPDATE ".$this->table."
			SET parents='|', children='|', ord='0'
		");

		//Struktur neu setzen
		foreach ( $nodes AS $res ) {
			while ( count($parents)>$res['level']-1 ) {
				array_pop($parents);
			}
			if ( $lastlevel<$res['level'] ) {
				$ord[$res['level']] = 0;
			}
			else {
				++$ord[$res['level']];
			}

			//Eltern definieren
			$db->query("
				UPDATE ".$this->table."
				SET parents='".dash_serialize($parents)."', children='|', ord='".$ord[$res['level']]."'
				WHERE ".$this->primary."='".$res[$this->primary]."'
			");

			//Knoten bei Eltern als Kindknoten hinzufügen
			if ( $parents ) {
				$db->query("
					UPDATE ".$this->table."
					SET children=CONCAT(children, '".$res[$this->primary]."|')
					WHERE ".$this->primary." IN (".implode(',', $parents).")
				");
			}

			$parents[] = $res[$this->primary];
			$lastlevel = $res['level'];
		}

		//Restliche Knoten flachklopfen
		if ( isset($ord[1]) ) {
			$ord = $ord[1]+1;
		}
		else {
			$ord = 0;
		}
		if ( $levelOrd[1] ) $ord = $levelOrd[1];
		$data = $db->fetch("
			SELECT ".$this->primary."
			FROM ".$this->table."
			WHERE ".$this->primary." NOT IN (".implode(', ', $nodeIds).")
			ORDER BY parents ASC, ord ASC
		");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$db->query("
					UPDATE ".$this->table."
					SET parents='|', children='|', ord='".$ord."'
					WHERE ".$this->primary."='".$res[$this->primary]."'
					LIMIT 1
				");
				$ord++;
			}
		}
	}


}


?>
