<?php
class_load ('MonitorItemAbstraction');

/**
* Class for representing AD users
*/

class AD_User extends MonitorItemAbstraction
{
	var $item_id = 1028;
	var $item_id_info = 1045;
	var $item_id_monitoring = 1029;
	var $computer_id = null;
	var $nrc = 0;

	
	/** Constructor, also loads the object data if the computer ID and $nrc are passed */
	function AD_User ($computer_id = null, $nrc = 0)
	{
		if ($computer_id)
		{
			$this->computer_id = $computer_id;
			$this->nrc = $nrc;
			$this->load_data ();
		}
	}
	
	
	/** Loads the user data from the Kawacs database. 
	* After loading the Kawacs data for the AD_User item (1028), it also attempts
	* to locate corresponding data for AD_Users_monitoring item (1029).
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
				$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id_info.' AND ';
				$q.= 'field_id=214 AND value="'.$this->sam_account_name.'" ';
				$nrc_info = db::db_fetch_field ($q, 'nrc');
				
				if (is_numeric($nrc_info) and $nrc_info>=0)
				{
					$q = 'SELECT * FROM '.TBL_COMPUTERS_ITEMS.' ci LEFT JOIN '.TBL_MONITOR_ITEMS.' i ';
					$q.= 'ON ci.field_id = i.id ';
					$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id_info.' AND nrc = '.$nrc_info.' ';
					$q.= 'ORDER BY ci.field_id ';
					
					$vals = db::db_fetch_array ($q);
					
					for ($i=0; $i<count($vals); $i++)
					{
						$field = $vals[$i]->short_name;
						if (!isset($this->$field)) $this->$field = $vals[$i]->value;
					}
				}
				
				$q = 'SELECT nrc FROM '.TBL_COMPUTERS_ITEMS.' ci LEFT JOIN '.TBL_MONITOR_ITEMS.' i ';
				$q.= 'ON ci.field_id = i.id ';
				$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id_monitoring.' AND ';
				$q.= 'field_id=61 AND value="'.db::db_escape($this->distinguished_name).'" ';
				$nrc_monitoring = db::db_fetch_field ($q, 'nrc');
				
				if (is_numeric($nrc_monitoring) and $nrc_monitoring>=0)
				{
					$q = 'SELECT * FROM '.TBL_COMPUTERS_ITEMS.' ci LEFT JOIN '.TBL_MONITOR_ITEMS.' i ';
					$q.= 'ON ci.field_id = i.id ';
					$q.= 'WHERE ci.computer_id='.$this->computer_id.' AND ci.item_id='.$this->item_id_monitoring.' AND nrc = '.$nrc_monitoring.' ';
					$q.= 'ORDER BY ci.field_id ';
					
					$vals = db::db_fetch_array ($q);
	
					for ($i=0; $i<count($vals); $i++)
					{
						$field = $vals[$i]->short_name;
						if (!isset($this->$field)) $this->$field = $vals[$i]->value;
					}
				}
			}
		}
	}
	
	/** Returns an array with the computers on which this user has logged on, going up 2 months back 
	* @param	int			$months_cnt	The number of months back for which to check the logins
	* @return	array					Associative array, the keys being the computer IDs on which
	*							the user has logged on and the values being the most recent
	*							dates when those computers were used.
	*/
	function get_used_computers ($months_cnt = 1)
	{
		$ret = array ();
		if ($this->computer_id and $this->item_id)
		{
			// Build the list of month for which computers items logs will be checked
			$tbl_months = array (TBL_COMPUTERS_ITEMS);
			for ($i=0; $i<$months_cnt; $i++) $tbl_months[] = TBL_COMPUTERS_ITEMS_LOG.'_'.date('Y_m', strtotime($i.' months ago'));
			
			// Get the ID of the user's customer, so we can be sure that we don't return computers for other customers by accident
			$q = 'SELECT customer_id FROM '.TBL_COMPUTERS.' WHERE id='.$this->computer_id;
			$customer_id = db::db_fetch_field ($q, 'customer_id');
			
			// Get the logged in users from the current computers data and from the computers items logs
			foreach ($tbl_months as $tbl)
			{
                $ids = array();
                $tblExists = db::db_fetch_vector("show tables like '" . $tbl . "'");
                if(! empty($tblExists))
                {
                    $q = 'SELECT i.computer_id, max(i.reported) FROM '.$tbl.' i INNER JOIN '.TBL_COMPUTERS.' c ';
                    $q.= 'ON i.computer_id=c.id AND c.customer_id='.$customer_id.' ';
                    $q.= 'WHERE item_id='.CURRENT_USER_ITEM_ID.' AND ';
                    $q.= '(value="'.db::db_escape($this->sam_account_name).'" OR ';
                    $q.= 'value like "%\\\\'.db::db_escape($this->sam_account_name).'" OR ';
                    $q.= 'value like "'.db::db_escape($this->sam_account_name).'/%") GROUP BY 1 ORDER BY 2 DESC';
                    $ids = db::db_fetch_list ($q);
                }
				
				foreach ($ids as $computer_id => $timestamp) if (!isset($ret[$computer_id])) $ret[$computer_id] = $timestamp;
			}
		}
		return $ret;
	}

	/** [Class Method] Returns an associative array of AD Users */
	public static function get_ad_users_list ($filter = array())
	{
		return parent::get_list (57, $filter);
	}
	
	
	/** [Class Method] Returns and array of AD_Users objects according to the specified criteria */
    public static function get_ad_users ($filter = array())
	{
		$ret = array ();
		
		$ids = parent::get_list (57, $filter);

		foreach ($ids as $key => $val)
		{
			list ($computer_id, $item_id, $nrc) = preg_split('/_/', $key);
			$ret[] = new AD_User ($computer_id, $nrc);
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Given a login name, return the corresponding AD_User object
	* @param	string		$login		The login name as reported by a computer ('<Domain>\<LoginName>')
	* @param	int		$customer_id	The ID of the customer to which this user belongs
	* @return	AD_User				The matched user, or Null if none was found
	*/
    public static function get_by_login ($login, $customer_id)
	{
		$ret = null;
		$login = preg_replace('/^.*\\\/', '', $login);
	
		$q = 'SELECT ci.computer_id, ci.nrc FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_COMPUTERS_ITEMS.' ci ';
		$q.= 'ON c.id=ci.computer_id AND ci.item_id=1028 AND ci.field_id=57 ';
		$q.= 'WHERE c.customer_id='.$customer_id.' AND ci.value="'.$login.'"';
		
		$ids = db::db_fetch_array ($q);
		if (count($ids)>0)
		{
			$ret = new AD_User ($ids[0]->computer_id, $ids[0]->nrc);
		}
		return $ret;
	}
	
	/**
	 * [Class Method] gets the users that are currently logged and the computers on which they are logged
	 *
	 * @param array $filter
	 */
    public static function get_logged_users($customer_id)
	{
		$ret = array();
		$query = "select ci.computer_id as cid, c.netbios_name as netbios, ci.value as logged_user, ci.reported as last_rep from ".TBL_COMPUTERS_ITEMS." ci inner join ".TBL_COMPUTERS." c on ci.computer_id=c.id where ci.item_id=1000 and ci.computer_id in (select c.id from ".TBL_COMPUTERS." c where c.customer_id=".$customer_id." and c.profile_id in (2,3)) order by ci.computer_id asc;";
		$rr = db::db_fetch_array($query);
		$i=0;
		foreach($rr as $reg)
		{
			$adu = preg_split('/\\\\/', $reg->logged_user);			
			$user_name = $adu[count($adu)-1];
			//now get the ad_user object associated with this user_name
			$q = "select ci.computer_id, ci.nrc from ".TBL_COMPUTERS_ITEMS." ci INNER JOIN ".TBL_COMPUTERS." c on ci.computer_id=c.id where c.customer_id=".$customer_id." and ci.item_id=1028 and ci.field_id=57 and ci.value='".$user_name."'";			
			$ids = db::db_fetch_array($q);
			$ad_user = false;
			if(count($ids)>0)
			{
				$ad_user = new AD_User($ids[0]->computer_id, $ids[0]->nrc);
			}
			$ret[] = array('computer_id'=>$reg->cid,
						   'netbios'=>$reg->netbios,
						   'reported' => $reg->last_rep,
						   'ad_user' => $ad_user,
						   'logged_user' => $reg->logged_user);
		}
		return $ret;
	}

    public static function get_adusers_stats($customer_id){
            //now we need to get the date when this user was created
            $ret = array();
            //1. get the total number of adusers
            $query = "select count(distinct(nrc)) as cxx from ".TBL_COMPUTERS_ITEMS." where computer_id in (select id from ".TBL_COMPUTERS." where customer_id=".$customer_id.") and item_id=1029 and field_id=61";
            $tot_cnt = db::db_fetch_field($query, 'cxx');
            $ret['total_users'] = $tot_cnt;
            //2. now get a list of all the creation dates
            $query = "select nrc, min(value) as md from ".TBL_COMPUTERS_ITEMS." where computer_id in (select id from ".TBL_COMPUTERS." where customer_id=".$customer_id.") and item_id=1029 and field_id in (69, 82, 87) and value>0 group by nrc";
            $dates_list = db::db_fetch_list($query);
            $dtl = array();
            $cnt = 0;
            foreach($dates_list as $dt){
                //debug($dt);
                $date_pp = getdate($dt);
                $dxx = mktime(0,0,0, $date_pp['mon'], 10,$date_pp['year']);
                $dtl[$dxx][] = $dt;
                $cnt+=1;
            }
            ksort($dtl);
            $min_key = time();
            foreach($dtl as $k=>$of){
                $min_key = min($min_key, $k);
            }
            if($tot_cnt - $cnt > 0){
                for($i=0; $i< ($tot_cnt-$cnt); $i++){
                    $dtl[$min_key][] = $min_key;
                }
            }
            //debug($dtl);
            $ret['list_by_date'] = $dtl;
            return $ret;
        }
}

?>