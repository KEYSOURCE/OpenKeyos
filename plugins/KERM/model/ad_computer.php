<?php
class_load ('MonitorItemAbstraction');

/**
* Class for representing AD computers
*/

class AD_Computer extends MonitorItemAbstraction
{
	//var $item_id = 1030;
	var $item_id = 1046;
	var $item_id_monitoring = 1047;
	var $computer_id = null;
	var $nrc = 0;

	/** Joining between 1046 and 1047 is done on the CN field (1046:228 = 1047:235). With
	* the old item the field was 1030:117 */
	
	/** Constructor, also loads the object data if the computer ID and $nrc are passed */
	function AD_Computer ($computer_id = null, $nrc = 0)
	{
		if ($computer_id)
		{
			$this->computer_id = $computer_id;
			$this->nrc = $nrc;
			$this->load_data ();
		}
	}
	
	
	/** Loads the computers data from the Kawacs database. 
	* After loading the Kawacs data for the AD_Computer item (1046), it also attempts
	* to locate corresponding data for AD_Computer_monitoring item (1047).
	*/
	function load_data ()
	{
		if ($this->computer_id and $this->item_id)
		{
			parent::load_data ();
			
			if ($this->distinguished_name)
			{
				$q = 'SELECT nrc FROM '.TBL_COMPUTERS_ITEMS.' ci LEFT JOIN '.TBL_MONITOR_ITEMS.' i ';
				$q.= 'ON ci.field_id = i.id ';
				$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id_monitoring.' AND ';
				$q.= 'field_id=235 AND value="'.db::db_escape($this->cn).'" ';
				$nrc_monitoring = db::db_fetch_field ($q, 'nrc');
				
				if (is_numeric($nrc_monitoring) and $nrc_monitoring >= 0)
				{
					$q = 'SELECT * FROM '.TBL_COMPUTERS_ITEMS.' ci LEFT JOIN '.TBL_MONITOR_ITEMS.' i ';
					$q.= 'ON ci.field_id = i.id ';
					$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id_monitoring.' AND nrc='.$nrc_monitoring.' ';
					$q.= 'ORDER BY ci.field_id ';
					
					$vals = db::db_fetch_array ($q);
					for ($i=0; $i<count($vals); $i++)
					{
						$field = $vals[$i]->short_name;
						//if (!isset($this->$field)) $this->$field = utf8_decode($vals[$i]->value);
						if (!isset($this->$field)) $this->$field = $vals[$i]->value;
						
					}
				}
			}
		}
	}

	/** [Class Method] Returns an associative array of AD Computers */
    public static function get_ad_computers_list ($filter = array())
	{
		return parent::get_list (228, $filter);
	}
	
	
	/** [Class Method] Returns and array of AD_Computer objected according to the specified criteria */
    public static function get_ad_computers ($filter = array())
	{
		$ret = array ();
		
		$ids = parent::get_list (228, $filter); 

		foreach ($ids as $key => $val)
		{
			list ($computer_id, $item_id, $nrc) = preg_split('/_/', $key);
			$ret[] = new AD_Computer ($computer_id, $nrc);
		}
		
		return $ret;
	}
}

?>