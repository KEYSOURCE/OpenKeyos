<?php
class_load ('Computer');
class_load ('Customer');

/**
* Classes for defining computers/customers blackouts, meaning periods when no
* contact is to be expected from those computers and no notifications should be 
* raised.
*
* Note that there will be a crontab activity which will check for "expired" blackouts
* and will automatically remove them from database.
*/


class ComputerBlackout extends Base
{
	/** The computer ID
	* @var int */
	var $computer_id = null;
	
	/** The start date of the blackout
	* @var timestamp */
	var $start_date = 0;
	
	/** The end date of the blackout. Use 0 if the blackout has an undefined length
	* @var int */
	var $end_date = 0;
	
	/** Comments about the blackout
	* @var string */
	var $comments = '';
	
	
	/** The associated Computer object. Note that the Computer object is not loaded
	* wherenever this object is loaded. Instead, it is only loaded on request
	* @var Computer */
	var $computer = null;
	
	
	/** The databasr table storing blackouts data 
	* @var string */
	var $table = TBL_COMPUTERS_BLACKOUTS;
	
	/** The primary key field 
	* @var string */
	var $primary_key = array('computer_id');
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('computer_id', 'start_date', 'end_date', 'comments');

	
	/**
	* Constructor, also loads the data from the database if an ID is specified
	* @param	int	$computer_id		A computer ID
	* @param	bool	$load_computer		If TRUE and a valid computer ID is specified, 
	*						this will load the computer object too.
	*/
	function __construct($computer_id = null, $load_computer = false)
	{
		if ($computer_id)
		{
			$this->computer_id = $computer_id;
			$this->load_data();
			
			if ($load_computer) $this->load_computer ();
		}
	}
	
	
	/** Loads the Computer object associated with this object */
	function load_computer ()
	{
		if ($this->computer_id)
		{
			$this->computer = new Computer ($this->computer_id);
		}
	}
	
	
	/** Checks if this blackout is in effect (comparing the dates) */
	function is_active ()
	{
		$ret = true;
		
		if ($this->start_date)
		{
			$ret = ($this->start_date <= time());
		}
		
		if ($ret and $this->end_date)
		{
			$ret = ($this->end_date >= time());
		}
		
		return $ret;
	}
	
	/** [Class Method] Returns the number of active blackouts */
	public static function get_active_blackouts ()
	{
		$ret = 0;
		$now = time();
		$q = 'SELECT count(b.computer_id) as cnt FROM '.TBL_COMPUTERS_BLACKOUTS.' b ';
		$q.= 'WHERE ((b.start_date=0 OR b.start_date<='.$now.') AND ((b.end_date=0 OR b.end_date>='.$now.'))) ';
		
		$ret = db::db_fetch_field ($q, 'cnt');
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns the current blacked out computers.
	* @param	array	$filter			Filtering criteria. Can contain:
	*						- customer_id : Return only computers for this customer_id
	*						- load_computers : If true, it will also load the associated
	*						  Computers objects
	* @result	array(ComputerBlackout)		Array with the matched ComputerBlackout objects, optionally
	*						having the related Computer objects loaded (if 'load_computers' was
	*						specified in $filter)
	*/
	public static function get_computers ($filter = array())
	{
		$ret = array ();
		
		$filter['load_computers'] = ($filter['load_computers'] ? true : false);
		
		$q = 'SELECT DISTINCT b.computer_id FROM '.TBL_COMPUTERS_BLACKOUTS.' b ';
		$q.= 'INNER JOIN '.TBL_COMPUTERS.' c ON b.computer_id=c.id ';

        $current_user = $GLOBALS['CURRENT_USER'];
		if(isset($current_user) and $current_user->is_customer_user() and $current_user->administrator and $current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $current_user->get_assigned_customers_list();
			$q.= ' AND c.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));				
			$q.=") ";
		}
		
		if ($filter['customer_id'])
		{
			$q.= 'AND c.customer_id='.$filter['customer_id'].' ';
			$q.= 'ORDER BY b.computer_id ';
		}
		else
		{
			// Use the customers table to sort by customers name
			$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
			$q.= 'ORDER BY cust.name, c.id ';
		}
		
		$ids = db::db_fetch_vector ($q);
		
		foreach ($ids as $id) $ret[] = new ComputerBlackout($id, $filter['load_computers']);
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Checks all blackouts for expiration dates and remove all notifications for blacked out computers,
	* either for all computers or for a specific computer.
	* Can be called as object method too, in which case the check is done only for this computer
	*/
	function check_blackouts ($computer_id = null)
	{
		$now = time ();
		
		$object_call = false;
		if (get_class ($this) == 'computerblackout' and $this->computer_id)
		{
			// This was called as object method
			$computer_id = $this->computer_id;
			$object_call = true;
		}
		
		// Delete expired blackouts
		$q = 'DELETE FROM '.TBL_COMPUTERS_BLACKOUTS.' WHERE end_date<>0 AND end_date<'.$now.' ';
		
		if ($computer_id) $q.= 'AND computer_id = '.$computer_id;
		db::db_query ($q);
		
		// Delete notifications
		if (!$computer_id and !$object_call)
		{
			// Check for all blacked out computers
			
			// Fetch the list of blacked out computers that still have notifications
			$q = 'SELECT DISTINCT b.computer_id FROM '.TBL_COMPUTERS_BLACKOUTS.' b ';
			$q.= 'INNER JOIN '.TBL_NOTIFICATIONS.' n ON n.object_class='.NOTIF_OBJ_CLASS_COMPUTER.' AND b.computer_id=n.object_id ';
			$q.= 'WHERE ((b.start_date=0 OR b.start_date<='.$now.') AND ((b.end_date=0 OR b.end_date>='.$now.'))) ';
			
			$blackout_ids = db::db_fetch_vector ($q);
			
			foreach ($blackout_ids as $id)
			{
				$q = 'DELETE FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_COMPUTER.' ';
				$q.= 'AND object_id='.$id;
				db::db_query ($q);
			}
		}
		elseif ($computer_id)
		{
			// Check only for a certain computer
			$blackout = new ComputerBlackout ($computer_id);
			
			// Make sure the blackout really exists
			if ($blackout->computer_id)
			{
				// If end date has passed, delete the blackout
				if ($blackout->end_date and $blackout->end_date < $now)
				{
					$blackout->delete ();
				}
				else
				{
					if ($blackout->is_active ())
					{
						$q = 'DELETE FROM '.TBL_NOTIFICATIONS.' WHERE object_class='.NOTIF_OBJ_CLASS_COMPUTER.' ';
						$q.= 'AND object_id='.$blackout->computer_id;
						db::db_query ($q);
					}
				}
			}
		}
	}
}

?>