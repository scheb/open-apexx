<?php

class OrderedList
{
    public $table;
    public $primary;
    public $db;

    //Konstruktor
    public function OrderedList($table, $primary)
    {
        $this->table = $table;
        $this->primary = $primary;
    }

    //Ord eines Knotens auslesen
    public function getNodeOrd($id)
    {
        global $db;

        $res = $db->first('
			SELECT ord FROM '.$this->table.'
			WHERE '.$this->primary."='".$id."'
			LIMIT 1
		");
        if (empty($res['ord'])) {
            return null;
        }

        return $res['ord'];
    }

    //Vor einen Knoten verschieben
    public function moveBefore($id, $targetId)
    {
        global $db;

        if ($id == $targetId) {
            return;
        }
        $ord = $this->getNodeOrd($id);
        if (is_null($ord)) {
            return;
        }
        $targetOrd = $this->getNodeOrd($targetId);
        if (is_null($targetOrd)) {
            return;
        }
        //Sonderfall
        if ($ord < $targetOrd) {
            --$targetOrd;
        }

        $db->query('
			UPDATE '.$this->table.'
			SET ord=ord'.($ord < $targetOrd ? '-' : '+').'1
			WHERE ord BETWEEN '.min([$ord, $targetOrd]).' AND '.max([$ord, $targetOrd]).'
		');

        $db->query('
			UPDATE '.$this->table.'
			SET ord='.$targetOrd.'
			WHERE '.$this->primary."='".$id."'
			LIMIT 1
		");
    }

    //Nach einen Knoten verschieben
    public function moveAfter($id, $targetId)
    {
        global $db;

        if ($id == $targetId) {
            return;
        }
        $ord = $this->getNodeOrd($id);
        if (is_null($ord)) {
            return;
        }
        $targetOrd = $this->getNodeOrd($targetId);
        if (is_null($targetOrd)) {
            return;
        }
        //Sonderfall
        if ($ord > $targetOrd) {
            ++$targetOrd;
        }

        $db->query('
			UPDATE '.$this->table.'
			SET ord=ord'.($ord < $targetOrd ? '-' : '+').'1
			WHERE ord BETWEEN '.min([$ord, $targetOrd]).' AND '.max([$ord, $targetOrd]).'
		');

        $db->query('
			UPDATE '.$this->table.'
			SET ord='.$targetOrd.'
			WHERE '.$this->primary."='".$id."'
			LIMIT 1
		");
    }

    //Nach oben verschieben
    public function moveUp($id)
    {
        global $db;

        $ord1 = $this->getNodeOrd($id);
        if (is_null($ord1)) {
            return;
        }
        list($brother, $ord2) = $db->first('
			SELECT '.$this->primary.', ord
			FROM '.$this->table."
			WHERE ord<'".$ord1."'
			ORDER BY ord DESC
			LIMIT 1
		");
        if (!$brother) {
            return;
        }
        $db->query('
			UPDATE '.$this->table.'
			SET ord='.($ord1 + $ord2).'-ord
			WHERE '.$this->primary." IN ('".$id."','".$brother."')
		");
    }

    //Nach unten verschieben
    public function moveDown($id)
    {
        global $db;

        $ord1 = $this->getNodeOrd($id);
        if (is_null($ord1)) {
            return;
        }
        list($brother, $ord2) = $db->first('
			SELECT '.$this->primary.', ord
			FROM '.$this->table."
			WHERE ord>'".$ord1."'
			ORDER BY ord ASC
			LIMIT 1
		");
        if (!$brother) {
            return;
        }
        $db->query('
			UPDATE '.$this->table.'
			SET ord='.($ord1 + $ord2).'-ord
			WHERE '.$this->primary." IN ('".$id."','".$brother."')
		");
    }
}
