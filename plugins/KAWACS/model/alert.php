<?php
class_load ('AlertCondition');

/**
* Class for managing alert definitions for monitoring profiles.
*
* Starting from version 3.0, alerts are defined as independent objects, 
* which means that the same alert can be assigned to multiple profiles.
* 
*/

class Alert extends Base
{
	/** The alert definition ID
	* @var int */
	var $id = null;
	
	/** The name of the alert 
	* @var string */
	var $name = '';
	
	/** The alert level which corresponds to this alert - see $GLOBALS['ALERT_NAMES']
	* @var int */
	var $level = 0;
	
	/** The event code associated with this alert 
	* @var int */
	var $event_code = 0;
	
	/** The monitoring item ID to which this alert belongs to
	* @var int */
	var $item_id;
	
	/** If true, alert of this type will be generated only if the computer made contact recently (e.g. is not shut down)
	* @var boolean */
	var $on_contact_only = false;
	
	/** On which days, if any, the alert is NOT raised. The value is equal
	* with the sum of the keys from $GLOBALS ['DAY_NAMES']
	* @var int */
	var $ignore_days = 0; 
	
	/** If the alert contains multiple conditions, this specifies how the conditions
	* will be joined (AND / OR). This applies only to alerts relating to items which are
	* arrays of non-structure fields. See $GLOBALS['JOIN_CONDITION_NAMES'] 
	* @var int */
	var $join_type = JOIN_CONDITION_AND;
	
	/** To whom to send the alert - see $GLOBALS['ALERT_SEND_TO']
	* @var int */
	var $send_to = ALERT_SEND_KEYSOURCE;
	
	/** A message subject if the notification is to be sent to the customer
	* @var string */
	var $subject = '';
	
	/** A message text if the notification is to be sent to the customer
	* @var text */
	var $message = '';
	
	/** A number of minutes with which to delay the generation of the notification 
	* e-mail after the alert has been raised
	* @var int */
	var $delay_email;
	
	
	/** Array of AlertCondition objects with the condition(s) on which this alert is raised
	* @var array */
	var $conditions = array();
	
	/** The monitoring item object definition
	* @var MonitorItem */
	var $itemdef = null;
	
	/** Associative array with the list of profiles using this alert, keys being profiles IDs and
	* the values being profiles names. NOTE: Loaded only on request, with load_profiles_list().
	* @var array () */
	var $profiles_list = array ();
	
	/** Array with ID of the users which are defined as specific recipients for this type of 
	* alert. This setting overrides the generic and customer-specific recipients. The list of recipients
	* is saved using the set_recipients() method, it is not saved in save_data()
	* @var array (int) */
	var $recipients_ids = array ();
	
	/** If recipients are specified in $recipients_ids, this will specify which of them is the default one.
	* Again, this is not saved with save_data(), but with set_recipients().
	* @var int */
	var $recipient_default = array ();
	
	/** Array with computer item fields IDs which will be included in the notifications subjects when
	* alerts are raised.
	* @var array */
	var $send_fields = array ();
	
	
	/** The name of the database table which stores data for this object 
	* @var string */
	var $table = TBL_ALERTS;

	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array ('id', 'name', 'level', 'event_code', 'item_id', 'on_contact_only', 'join_type', 'ignore_days', 'send_to', 'subject', 'message', 'delay_email');
	
	
	
	/** Contructor. Loads an alert item's values if an ID is specified */
	function Alert ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/**
	* Loads the definition for this alert, including the alert conditions
	*/
	function load_data ()
	{
		if ($this->id)
		{
			parent::load_data();
			if ($this->id)
			{
				// Load also the alert condition(s)
				$q = 'SELECT id FROM '.TBL_ALERTS_CONDITIONS.' WHERE alert_id='.$this->id.' ORDER BY field_id, id ';
				$ids = $this->db_fetch_vector ($q);
				foreach ($ids as $id) $this->conditions[] = new AlertCondition ($id);
			}
			
			// Load the definition of the linked monitor item
			if ($this->item_id) $this->itemdef = new MonitorItem ($this->item_id);
			
			// Load the list of assigned recipients, if any
			$q = 'SELECT ar.user_id FROM '.TBL_ALERTS_RECIPIENTS.' ar INNER JOIN '.TBL_USERS.' u ';
			$q.= 'ON ar.user_id=u.id WHERE alert_id='.$this->id.' ORDER BY u.fname, u.lname, u.id ';
			$this->recipients_ids = $this->db_fetch_vector ($q);
			
			if (count($this->recipients_ids) > 0)
			{
				$q = 'SELECT user_id FROM '.TBL_ALERTS_RECIPIENTS.' WHERE alert_id='.$this->id.' AND is_default=1';
				$this->recipient_default = $this->db_fetch_field ($q, 'user_id');
				if (!$this->recipient_default) $this->recipient_default = $this->recipients_ids[0];
			}
			
			// Load the list of fields who's values to include in notifications subjects
			$this->send_fields = $this->db_fetch_vector('SELECT field_id FROM alerts_send_fields WHERE alert_id='.$this->id);
		}
	}
	
	
	/**
	* Checks if the data stored by this object is valid
	*/
	function is_valid_data ()
	{
		$valid = true;
		
		if (!$this->name) {error_msg ('Please specify a name for this alert.'); $valid = false;}
		if (!$this->item_id) {error_msg ('This alert is not associated with any item'); $valid = false;}
		if (!isset($this->level)) {error_msg ('Please specify the severity level for this alert'); $valid = false;}
		
		return $valid;
	}
	
	
	/**
	* Save the list of recipients assigned to this alert
	* @param	array			$recipients	Array with the user IDs of the assigned recipients
	* @param	int			$default	The ID of the default recipient.
	*/
	function set_recipients ($recipients, $default = 0)
	{
		if ($this->id)
		{
			// Delete first the existing recipients
			$q = 'DELETE FROM '.TBL_ALERTS_RECIPIENTS.' WHERE alert_id='.$this->id;
			$this->db_query($q);
			
			if (count($recipients) > 0)
			{
				// Set the new recipients
				$q = 'INSERT INTO '.TBL_ALERTS_RECIPIENTS.' (alert_id, user_id) VALUES ';
				foreach ($recipients as $id) $q.= '('.$this->id.','.$id.'), ';
				$q = preg_replace ('/,\s*$/', '', $q);
				$this->db_query($q);
				
				// Make sure we have a default, just select the first recipient
				if (!$default) $default = $recipients[0];
				
				$q = 'UPDATE '.TBL_ALERTS_RECIPIENTS.' SET is_default=1 WHERE ';
				$q.= 'alert_id='.$this->id.' AND user_id='.$default;
				$this->db_query ($q);
			}
		}
	}
	
	
	/** 
	* Saves the list of fields IDs for using in notifications subjects
	* @param	array			$fields		Array with the IDs of those fields.
	*/
	function set_send_fields ($fields = array ())
	{
		if ($this->id)
		{
			// Delete first the existing fields
			$this->db_query ('DELETE FROM '.TBL_ALERTS_SEND_FIELDS.' WHERE alert_id='.$this->id);
			
			if (is_array($fields) and count($fields)>0)
			{
				$q = 'INSERT INTO '.TBL_ALERTS_SEND_FIELDS.' (alert_id, field_id) VALUES ';
				foreach ($fields as $field_id) $this->db_query ($q. '('.$this->id.','.$field_id.')');
			}
		}
	}
	
	
	/**
	* Deletes an alert definition
	*/
	function delete ()
	{
		if ($this->id)
		{
			// Delete the associated conditions too
			if (is_array ($this->conditions)) foreach ($this->conditions as $cond) $cond->delete();
			
			// Delete notifications of this type, if they exist
			class_load ('Notification');
			$q = 'SELECT id FROM '.TBL_NOTIFICATIONS.' WHERE ';
			if ($this->itemdef->is_peripheral_item()) $q.= '(object_class='.NOTIF_OBJ_CLASS_PERIPHERAL.' OR object_class='.NOTIF_OBJ_CLASS_AD_PRINTER.') ';
			else $q.= 'object_class='.NOTIF_OBJ_CLASS_COMPUTER.' ';
			$q.= 'AND object_event_code = '.$this->id;
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id)
			{
				$notification = new Notification ($id);
				$notification->delete ();
			}
			
			// Delete the list of assigned recipients, if any
			$q = 'DELETE FROM '.TBL_ALERTS_RECIPIENTS.' WHERE alert_id='.$this->id;
			$this->db_query ($q);
		
			// Delete the alert definition itself
			parent::delete ();
		}
	}
	
	
	/** Loads, on request, the list of monitor profiles which are using this alert */
	function load_profiles_list ()
	{
		if ($this->id)
		{
			$is_periph = $this->itemdef->is_peripheral_item();
			$q = 'SELECT p.id, p.name FROM '.($is_periph ? TBL_PROFILES_PERIPH_ALERTS : TBL_PROFILES_ALERTS).' pa ';
			$q.= 'INNER JOIN '.($is_periph ? TBL_MONITOR_PROFILES_PERIPH : TBL_MONITOR_PROFILES).' p ';
			$q.= ' ON pa.profile_id=p.id AND pa.alert_id='.$this->id.' ORDER BY p.id ';
			
			$this->profiles_list = $this->db_fetch_list ($q);
		}
	}
	
	
	/** Sets (and saves to database) the list of profiles to which this alert is assigned 
	* @param	array		$profiles	Array with the IDs of the assigned profiles
	*/
	function set_profiles ($profiles)
	{
		if ($this->id and is_array($profiles))
		{
			$this->load_profiles_list ();
			$tbl = ($this->itemdef->is_peripheral_item() ? TBL_PROFILES_PERIPH_ALERTS : TBL_PROFILES_ALERTS);
			
			// First, delete all assignments no longer valid
			foreach ($this->profiles_list as $profile_id => $profile_name)
			{
				if (!in_array($profile_id, $profiles))
				{
					$this->db_query ('DELETE FROM '.$tbl.' WHERE alert_id='.$this->id.' AND profile_id='.$profile_id);
				}
			}
			
			// Then add the profiles which where not set before
			foreach ($profiles as $profile_id)
			{
				if (!isset($this->profiles_list[$profile_id]))
				{
					$this->db_query ('REPLACE INTO '.$tbl.'(profile_id,alert_id) VALUES ('.$profile_id.','.$this->id.')');
				}
			}
		}
	}
	
	
	/** [Class Method] Returns a list of monitor alerts according to the specified criteria 
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- profile_id: Return only alerts assigned to the specified profile
	*							- is_periph_profile: (Requires 'profile_id') True if the profile is a peripherals profile
	*							- item_id: Return only alerts for the specified item
	*							- computers_only: If True, return only alerts related to computers items
	*							- peripherals_only: If True, return only alerts related to peripherals items
	* @return	array(Alert)				Array with the matched alerts, sorted by severity and name
	*/
    public static function get_alerts ($filter = array())
	{
		$ret = array();
		
		$q = 'SELECT a.id FROM '.TBL_ALERTS.' a ';
		if ($filter['profile_id'])
		{
			$q.= 'INNER JOIN '.($filter['is_periph_profile'] ? TBL_PROFILES_PERIPH_ALERTS : TBL_PROFILES_ALERTS).' pa ON a.id=pa.alert_id ';
		}
		$q.= 'WHERE ';
		
		if ($filter['profile_id']) $q.= 'pa.profile_id='.$filter['profile_id'].' AND ';
		if ($filter['item_id']) $q.= 'a.item_id='.$filter['item_id'].' AND ';
		if ($filter['computers_only']) $q.= '(a.item_id<'.ITEM_ID_PERIPHERAL_SNMP_MIN.' OR a.item_id>'.ITEM_ID_PERIPHERAL_SNMP_MAX.') AND ';
		if ($filter['peripherals_only']) $q.= '(a.item_id>='.ITEM_ID_PERIPHERAL_SNMP_MIN.' AND a.item_id<='.ITEM_ID_PERIPHERAL_SNMP_MAX.') AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		$q.= 'ORDER BY a.level DESC, a.name ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new Alert ($id);
		
		return $ret;
	}
	
	
	/** [Class Method] Returns a list with all item IDs for which alerts are defined
	* @return	array					Associative array, keys being item IDs and the values being item names
	*/
    public static function get_used_item_ids ()
	{
		$ret = array ();
		
		$q = 'SELECT a.item_id, i.name FROM '.TBL_ALERTS.' a INNER JOIN '.TBL_MONITOR_ITEMS.' i ON a.item_id=i.id ';
		$q.= 'ORDER BY i.id ';
		
		$ret = DB::db_fetch_list ($q);
		return $ret;
	}
	
	
	/** [Class Method] Get a list of alerts for which a specified user is designated as recipient 
	* @param	int			$user_id	The ID of the checked user
	* @return	array					Associative array, the keys being alert IDs and the values being alert names
	*/
    public static function get_user_assigned_alerts ($user_id)
	{
		$ret = array ();
		
		if ($user_id and is_numeric($user_id))
		{
			$q = 'SELECT a.id, a.name FROM '.TBL_ALERTS_RECIPIENTS.' ar INNER JOIN '.TBL_ALERTS.' a ';
			$q.= 'ON ar.alert_id=a.id WHERE ar.user_id='.$user_id.' ORDER BY a.name, a.id ';
			$ret = DB::db_fetch_list ($q);
		}
		
		return $ret;
	}
	
}

?>