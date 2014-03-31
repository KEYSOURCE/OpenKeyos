<?php

class_load ('AD_Printer');
class_load ('RemovedAD_SNMP_Item');

/** Class for representing removed (obsolete) AD Printers 
*
* These are AD Printers which don't exist anymore in the computer items
* (and therefore not in the Active Directory server either). The ID of
* these objects are the same as the synthetic numeric IDs which are generated
* for the AD Printers. 
*
* As a consequence, when new IDs are generated for AD Printers, the system
* should ensure that it is not reusing IDs from removed AD Printers.
*
* The RemovedAD_Printer object will combine the information from the tables
* ad_printers_extras and ad_printers_warranties. If the printer was SNMP
* monitored, additional data about it will be stored in RemovedAD_SNMMP_Item 
* objects.
*
* Note that an AD Printer can be removed only if it is "orphan", meaning
* that it is not reported anymore through computers items. Also because
* the AD Printers come through computer items reporting, it is not possible
* to reactivate a removed AD Printer.
*
* W
*/

class RemovedAD_Printer extends Base
{
	/** The unique numeric ID
	* @var int */
	var $id = null;
	
	/** The unique canonical name of the AD Printer
	* @var string */
	var $canonical_name = '';
	
	/** The customer ID
	* @var int */
	var $customer_id = null;
	
	/** The date from which the AD Printer was managed in Keyos
	* @var timestamp */
	var $date_created = 0;
	
	/** The date until which the AD Printer was managed in Keyos
	* @var timestamp */
	var $date_removed = 0;
	
	/** The removal reason
	* @var text */
	var $reason_removed = '';
	
	/** The ID of the user who made the removal
	* @var int */
	var $removed_by = 0;
	
	/** The location ID
	* @var int */
	var $location_id = 0;
	
	/** The AD Printer asset number 
	* @var string */
	var $asset_number = '';
	
	/** The ID of the SNMP monitoring profile - if any
	* @var int */
	var $profile_id = 0;
	
	/** True or False if SNMP was enabled for this AD Printer
	* @var bool */
	var $snmp_enabled = false;
	
	/** The ID of the computer doing the SNMP monitoring
	* @var int */
	var $snmp_computer_id = 0;
	
	/** The IP address at which the printer is located
	* @var string */
	var $snmp_ip = '';
	
	/** The last SNMP contact date with this printer
	* @var timestamp */
	var $last_contact = 0;
	
	/** The serial number, for warranty information - if any
	* @var string */
	var $sn = '';
	
	/** The start date for the warranty
	* @var timestamp */
	var $warranty_starts = 0;
	
	/** The end date for the warranty 
	* @var timestamp */
	var $warranty_ends = 0;
	
	/** The warranty service package ID - if any
	* @var int */
	var $service_package_id = 0;
	
	/** The service level ID - if any
	* @var int */
	var $service_level_id = 0;
	
	/** The warranty contract number - if any
	* @var string */
	var $contract_number = '';
	
	/** The hardware product ID - if any
	* @var string */
	var $hw_product_id = '';
	
	/**  The product number - if any
	* @var string */
	var $product_number = '';
	
	
	/** The AD Printer name, extracted from the canonical name
	* @var string */
	var $name = '';
	
	/** The associated Location object, loaded only on request with load_location()
	* @var Location */
	var $customer_location = null;
	
	/** Associative array with the SNMP collected data, the keys being item IDs and
	* the values being AD_SNMP_Item objects.
	* @var array */
	var $values_snmp = array ();
	
	
	/** The database table storing objects data
	* @var string */
	var $table = TBL_REMOVED_AD_PRINTERS;
	
	/** The fields to use when loading or saving data to database
	* @var array */
	var $fields = array ('id', 'canonical_name', 'customer_id', 'date_created', 'date_removed', 'reason_removed', 
		'location_id', 'asset_number', 'profile_id', 'snmp_enabled', 'snmp_computer_id', 
		'snmp_ip', 'last_contact', 'sn', 'warranty_starts', 'warranty_ends', 'service_package_id', 'service_level_id', 
		'contract_number', 'hw_product_id', 'product_number');
		
		
	/** Constructor. Also loads an object data if any ID is specified */
	function RemovedAD_Printer ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data ();
		}
	}
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->date_removed) {error_msg ($this->get_string('NEED_REMOVED_DATE')); $ret = false; }
		if (!trim($this->reason_removed)) {error_msg ($this->get_string('NEED_REMOVED_REASON')); $ret = false; }
		
		return $ret;
	}
	
	/** [Class Method] Marks an AD Printer as removed, by creating the associated
	* RemovedAD_Printer object and deleting the respective details from the tables
	* ad_printers_extras and ad_printers_warranties.
	* @param	AD_Printer		$ad_printer		The AD Printer object to remove
	* @param	int			$user_id		The ID of the user who made the operation
	* @param	text			$reason			The reason given for the removal
	* @param	timestamp		$date_removed		The date at which to mark that the computer has been removed.
	*								If not specified, the current time will be used
	* @return	RemovedAD_Printer				The newly created RemovedAD_Printer
	*/
	function remove_ad_printer ($ad_printer, $user_id, $reason, $date_removed = 0)
	{
		$ret = null;
		
		if ($ad_printer->id)
		{
			// Cleanup, just in case
			DB::db_query ('DELETE FROM '.TBL_REMOVED_AD_PRINTERS.' WHERE id='.$ad_printer->id);
			DB::db_query ('DELETE FROM '.TBL_REMOVED_PERIPHERALS_ITEMS.' WHERE obj_class='.SNMP_OBJ_CLASS_AD_PRINTER.' AND obj_id='.$ad_printer->id);
			
			// Copy the data to the new object
			$ret = new RemovedAD_Printer ();
			foreach ($ret->fields as $field) if (isset($ad_printer->$field)) $ret->$field = $ad_printer->$field;
			$ret->date_removed = ($date_removed ? $date_removed : time());
			$ret->reason_removed = $reason;
			$ret->save_data ();
			
			// Copy the SNMP data, if any
			$q = 'INSERT INTO '.TBL_REMOVED_PERIPHERALS_ITEMS.' SELECT * FROM '.TBL_PERIPHERALS_ITEMS.' WHERE ';
			$q.= 'obj_class='.SNMP_OBJ_CLASS_AD_PRINTER.' AND obj_id='.$ad_printer->id;
			DB::db_query ($q);
			
			// Delete the information about the removed AD Printer
			AD_Printer::delete_orphan_printer ($ad_printer->id);
		}
		
		return $ret;
	}
	
	/** Loads the object data, as well as any associated SNMP items */
	function load_data ()
	{
		parent::load_data ();
		if ($this->id)
		{
			// Extract the name from the canonical name
			$this->name = preg_replace ('/^.*(\/|\\\)/', '', $this->canonical_name);
			
			// Load the SNMP values, if any
			$this->values_snmp = array ();
			if ($this->profile_id)
			{
				$q = 'SELECT DISTINCT item_id FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$this->profile_id.' ORDER BY item_id';
				$items_ids = $this->db_fetch_vector ($q);
				
				foreach ($items_ids as $item_id) $this->values_snmp[$item_id] = new RemovedAD_SNMP_Item ($this->id, $item_id);
			}
		}
	}
	
	/** Load the associated location, if any */
	function load_location ()
	{
		if ($this->location_id)
		{
			class_load ('Location');
			$this->customer_location = new Location ($this->location_id);
			$this->customer_location->load_parents ();
		}
	}
	
	/** Deletes an AD Printer and all associated data */
	function delete ()
	{
		if ($this->id)
		{
			$q = 'DELETE FROM '.TBL_REMOVED_PERIPHERALS_ITEMS.' WHERE obj_class='.SNMP_OBJ_CLASS_AD_PRINTER.' AND obj_id='.$this->id;
			$this->db_query ($q);
			
			parent::delete ();
		}
	}
	
	/** [Class Method] Returns a list with the removed AD Printers
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: The ID of a customer
	* @return	array					Associative array, the keys being removed AD Printers IDs and
	*							the values being their names
	*/
	function get_removed_ad_printers_list ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id, canonical_name FROM '.TBL_REMOVED_AD_PRINTERS.' ';
		if ($filter['customer_id']) $q.= 'WHERE customer_id='.$filter['customer_id'].' ';
		$q.= 'ORDER BY canonical_name ';
		
		$ret = DB::db_fetch_list ($q);
		foreach ($ret as $id => $canonical_name) $ret[$id] = preg_replace ('/^.*(\/|\\\)/', '', $canonical_name);
		
		return $ret;
	}
	
	/** [Class Method] Returns removed AD Printers
	* @param	array			$filter		Associative array with filtering criteria. Can contain:
	*							- customer_id: The ID of a customer
	* @return	array(RemovedAD_Printer)		Array with the found objects
	*/
	function get_removed_ad_printers ($filter = array ())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_REMOVED_AD_PRINTERS.' ';
		if ($filter['customer_id']) $q.= 'WHERE customer_id='.$filter['customer_id'].' ';
		$q.= 'ORDER BY canonical_name ';
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new RemovedAD_Printer ($id);
		
		return $ret;
	}
}

?>