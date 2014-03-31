<?php

class_load ('EventLogRequested');
class_load ('Computer');
class_load ('ComputerItem');

/**
* Class providing methods for processing computer data
*
*/

class ComputerReporting extends Base
{
	/************************************************/
	/* Processing of events logs reported items	*/
	/************************************************/
	
	/** [Class Method] Processes the received event logs data from a computer (computer item EVENTS_ITEM_ID)
	* This is normally called from Computer->add_reported_items()
	* @param	Computer					$computer	The Computer object for which the data was received
	* @param	array						$item_data	The data received from the Agent
	*/
	function process_item_events ($computer, $item_data)
	{
		if ($computer->id and is_array($item_data) and $item_data['id']==EVENTS_ITEM_ID and is_array($item_data['value']))
		{
			$sources_list = EventLogRequested::get_events_sources_list_extended();
			
			// First collect all sources reported
			// We'll also determine the propery keys to be used for accessing the fields in the reported data, since
			// it is safe to assume that all the items use the same keys.
			// Also build the array $items_events, which only stores the events reported
			$key_cat = $key_src = -1;
			$key_date = $key_category = $key_type = $key_source = $key_event_id = $key_description = -1;
			$items_events = array ();
			foreach ($item_data['value'] as $d)
			{
				$vals = $d['field_values'];
				if ($key_cat < 0 and is_array($d['field_names']))
				{
					$k = array_search ('cat', $d['field_names']);
					if (is_numeric($k) and $k >= 0)
					{
						$key_cat = $k;
						$key_src = array_search ('src', $d['field_names']);
						// Double-check that we have all the keys right
						if (!is_numeric($key_src)) $key_cat = $key_src = -1;
					}
				}
				if ($key_date < 0 and is_array($d['field_names']))
				{
					$k = array_search ('date', $d['field_names']);
					if (is_numeric($k) and $k >= 0)
					{
						$key_date = $k;
						$key_category = array_search ('category', $d['field_names']);
						$key_type = array_search ('type', $d['field_names']);
						$key_source = array_search ('source', $d['field_names']);
						$key_event_id = array_search ('event_id', $d['field_names']);
						$key_description = array_search ('description', $d['field_names']);
						// Double-check that we have all the keys right
						if (!is_numeric($key_category) or !is_numeric($key_type) or !is_numeric($key_source) or !is_numeric($key_event_id) or !is_numeric($key_description))
						{
							$key_date = $key_category = $key_type = $key_source = $key_event_id = $key_description = -1;
						}
					}
				}
				
				$found_cat = false;
				if ($key_cat >= 0 and isset($vals[$key_cat]))
				{
					// This is a source reported from a computer
					$cat_id = $vals[$key_cat];
					$source = $vals[$key_src];
					$found_cat = true;
				}
				elseif ($key_date >= 0 and isset($vals[$key_date]) and intval($vals[$key_date])>0)
				{
					// This is a event reported from a computer
					$cat_id = $vals[$key_category];
					$source = $vals[$key_source];
					$found_cat = true;
					
					// Add this to the list of reported events, after doing some cleanup
					$vals[$key_date] = intval($vals[$key_date]);
					$vals[$key_category] = intval($vals[$key_category]);
					$vals[$key_event_id] = intval($vals[$key_event_id]);
					$vals[$key_description] = trim($vals[$key_description]);
					$items_events[] = $vals;
					
				}
				
				if ($found_cat and EventLogRequested::get_source_id($sources_list, $cat_id, $source) < 0)
				{
					if (!EventLogRequested::check_events_source ($source, $cat_id, $computer->id))
					{
						// Source did not exist, reload the list of sources
						$sources_list = EventLogRequested::get_events_sources_list_extended();
					}
				}
                                if($vals) $vals = null;
			}
			
			
			// Check if there have been any reported events, otherwise there is no point in continuing
			if (count($items_events) > 0)
			{
				// Make sure all stored events have the same reported time
				$reported = time ();
				$q = 'UPDATE '.TBL_COMPUTERS_ITEMS.' SET reported='.$reported.' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID;
				DB::db_query ($q);
				
				// Determine the minimum timestamp for the events that have been reported now
				$min_event_date = 0;
				foreach ($items_events as $d)
				{
					$event_date = intval($d[$key_date]);
					if (($min_event_date==0 or $event_date < $min_event_date)) $min_event_date = $event_date;
				}
				
				// Get all nrc and timestamps from database where the event timestamp is higher than the minimum stamp from the reported events
				$q = 'SELECT nrc, value FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID.' AND ';
				$q.= 'field_id='.FIELD_ID_EVENTS_LOG_DATE.' AND value>="'.$min_event_date.'" ORDER BY nrc';
				$exist_dates = DB::db_fetch_list ($q);
				
				if (count($exist_dates) > 0)
				{
					// There are some overlapping events, remove from the reported list the duplicates
					// Load the event IDs and descriptions for existing events, to use for comparison
					$min_exist_nrc = min(array_keys($exist_dates));
					$q = 'SELECT nrc, value FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID.' ';
					$q.= 'AND field_id='.FIELD_ID_EVENTS_LOG_SOURCE.' AND nrc>='.$min_exist_nrc.' ORDER BY nrc';
					$exist_sources_ids = DB::db_fetch_list ($q);
					$q = 'SELECT nrc, value FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID.' ';
					$q.= 'AND field_id='.FIELD_ID_EVENTS_LOG_EVENT_ID.' AND nrc>='.$min_exist_nrc.' ORDER BY nrc';
					$exist_events_ids = DB::db_fetch_list ($q);
					$q = 'SELECT nrc, value FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID.' ';
					$q.= 'AND field_id='.FIELD_ID_EVENTS_LOG_DESCRIPTION.' AND nrc>='.$min_exist_nrc.' ORDER BY nrc';
					$exist_descriptions = DB::db_fetch_list ($q);
					
					foreach ($items_events as $idx => $vals)
					{
						set_time_limit(0);
						$keys = array_keys ($exist_dates, $vals[$key_date]); // Identify all 'nrc' fields with the same date
						set_time_limit(400);
						foreach ($keys as $k)
						{
							$cat_id = $vals[$key_category];
							$source_id = EventLogRequested::get_source_id ($sources_list, $cat_id, $vals[$key_source]);
							if (is_numeric($source_id) and $exist_sources_ids[$k]==$source_id and 
								$exist_events_ids[$k]==$vals[$key_event_id] and
								$exist_descriptions[$k]==$vals[$key_description]
							) {unset ($items_events[$idx]); break;}
						}
					}
                                         if($exist_descriptions) $exist_descriptions = null;
				}				
                                if($exist_dates) $exist_dates = null;

                                // See if any events have remained after checking for duplicates
				if (count($items_events) > 0)
				{
					// Sort the new events by their timestamp
					$GLOBALS['EVLOG_KEY_DATE'] = $key_date;
					$GLOBALS['EVLOG_KEY_CATEGORY'] = $key_category;
					$GLOBALS['EVLOG_KEY_TYPE'] = $key_type;
					usort ($items_events, array('ComputerReporting', 'cmp_events'));
					
					// Get the maximum existing nrc
					$q = 'SELECT max(nrc) as max_nrc FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID;
					$max_nrc = DB::db_fetch_field($q, 'max_nrc');
					if (is_numeric($max_nrc)) $start_nrc_new = $max_nrc+1;
					else $start_nrc_new = 0;
					
					// Insert the reported events in the database
					$batch_size = 25;
					$nrc = $start_nrc_new;
					$cnt = 0;
					$q_template = '('.$computer->id.','.EVENTS_ITEM_ID.','.$reported.',';
					foreach ($items_events as $vals)
					{
						if ($cnt==0) $q = 'INSERT INTO '.TBL_COMPUTERS_ITEMS.' (computer_id,item_id,reported,nrc,field_id,value) VALUES  ';
						
						$cat_id = intval($vals[$key_category]);
						$source_id = EventLogRequested::get_source_id($sources_list, $cat_id, $vals[$key_source]);
						
						if (is_numeric($source_id) and $source_id >= 0)
						{
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_DATE.',"'.intval($vals[$key_date]).'"), ';
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_CATEGORY.',"'.$cat_id.'"), ';
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_TYPE.',"'.mysql_escape_string($vals[$key_type]).'"), ';
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_SOURCE.',"'.$source_id.'"), ';
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_EVENT_ID.',"'.mysql_escape_string($vals[$key_event_id]).'"), ';
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_DESCRIPTION.',"'.mysql_escape_string($vals[$key_description]).'"), ';
							$q.= $q_template.$nrc.','.FIELD_ID_EVENTS_LOG_IGNORED.',"0"), ';
							
							$nrc++;
						}
						
						if ($cnt >= $batch_size)
						{
							$q[strlen($q)-2] = ' ';
							if (is_numeric($source_id) and $source_id >= 0) DB::db_query($q);
							$cnt = 0;
						}
						else $cnt++;
					}
					if ($cnt > 0) {$q[strlen($q)-2] = ' '; DB::db_query($q);}
				}
                
			}
			if($sources_list) $sources_list = null;
            if($items_events) $items_events = null;
		}
		elseif ($computer->id and is_array($item_data) and $item_data['id']==EVENTS_ITEM_ID and !is_array($item_data['value']))
		{
			// This is an empty reporting, no new events were sent by computer
			// Simply update the timestamps in computers_items, so the system will know when was the last report of events log
			$q = 'UPDATE '.TBL_COMPUTERS_ITEMS.' SET reported='.time().' WHERE computer_id='.$computer->id.' AND item_id='.EVENTS_ITEM_ID;
			DB::db_query ($q);
		}		
	}
	
	
	/** [Class Method] Comparator function for sorting reported events by date */
	function cmp_events ($a, $b)
	{
		if ($a[$GLOBALS['EVLOG_KEY_DATE']] == $b[$GLOBALS['EVLOG_KEY_DATE']])
		{
			if ($a[$GLOBALS['EVLOG_KEY_CATEGORY']] == $b[$GLOBALS['EVLOG_KEY_CATEGORY']])
			{
				if ($a[$GLOBALS['EVLOG_KEY_TYPE']] == $b[$GLOBALS['EVLOG_KEY_TYPE']]) $ret = 0;
				else $ret = ($a[$GLOBALS['EVLOG_KEY_TYPE']] < $b[$GLOBALS['EVLOG_KEY_TYPE']] ? -1 : 1);
			}
			else $ret = ($a[$GLOBALS['EVLOG_KEY_CATEGORY']] < $b[$GLOBALS['EVLOG_KEY_CATEGORY']] ? -1 : 1);
		}
		else $ret = ($a[$GLOBALS['EVLOG_KEY_DATE']] < $b[$GLOBALS['EVLOG_KEY_DATE']] ? -1 : 1);
		
		return $ret;
	}
	

	/** [Class Method] Marks all instances of an event (by event ID, source, category, type and description) as being ignored or un-ignored 
	* @param	Computer						$computer	The Computer object to which this is applied
	* @param	int							$nrc		The nrc of the event which should be ignored
	* @param	int							$is_ignored	1 or 0 if to mark the events as ignored or not ignored
	* @return	int									The number of events marked
	*/
	function set_event_ignored ($computer, $nrc, $is_ignored)
	{
		$ret = 0;
		if ($computer->id)
		{
			$item = $computer->get_item_by_id (EVENTS_ITEM_ID);
			
			// Locate the event for which the operation was requested
			$idx = $item->get_idx_for_nrc($nrc);
			
			$category = $item->val[$idx]->value[FIELD_ID_EVENTS_LOG_CATEGORY];
			$type = $item->val[$idx]->value[FIELD_ID_EVENTS_LOG_TYPE];
			$source = $item->val[$idx]->value[FIELD_ID_EVENTS_LOG_SOURCE];
			$event_id = $item->val[$idx]->value[FIELD_ID_EVENTS_LOG_EVENT_ID];
			$description = $item->val[$idx]->value[FIELD_ID_EVENTS_LOG_DESCRIPTION];
			
			// Locate all similar events and modify them
			foreach ($item->val as $idx => $val)
			{
				$value = &$val->value;
				if ($category == $value[FIELD_ID_EVENTS_LOG_CATEGORY] and
					$type == $value[FIELD_ID_EVENTS_LOG_TYPE] and
					$source == $value[FIELD_ID_EVENTS_LOG_SOURCE] and
					$event_id == $value[FIELD_ID_EVENTS_LOG_EVENT_ID] and
					$description == $value[FIELD_ID_EVENTS_LOG_DESCRIPTION]
				)
				{
					$item->val[$idx]->value[FIELD_ID_EVENTS_LOG_IGNORED] = $is_ignored;
					$item->save_single_value ($val->nrc);
					$ret++;
				}
			}
            if($item) $item = null;
		}       
        
		return $ret;
	}
	
	
	/** [Class Method] Deletes all events logs items that have exceeded the retention period and
	* reassign the NRCs accordingly. This should normally called from the crontab for daily maintenance
	*/
	function cleanup_events_logs ()
	{
		// First, build the list with all the computers that have events older than the retention period
		$min_time = time() - EVENTS_LOG_KEEP_DAYS * 24 * 3600;
		$q = 'SELECT DISTINCT computer_id FROM '.TBL_COMPUTERS_ITEMS.' WHERE item_id='.EVENTS_ITEM_ID.' AND ';
		$q.= 'field_id='.FIELD_ID_EVENTS_LOG_DATE.' AND value < "'.$min_time.'"';
		$ids = DB::db_fetch_vector ($q);
		
		// Loop over each computer and delete the old events. Where the minimum remaining NRC is greater than 0,
		// decrease the remaining NRCs so they start from 0
		foreach ($ids as $id)
		{
			$q = 'SELECT nrc FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$id.' AND item_id='.EVENTS_ITEM_ID.' AND ';
			$q.= 'field_id='.FIELD_ID_EVENTS_LOG_DATE.' AND value<"'.$min_time.'"';
			$del_nrcs = DB::db_fetch_vector ($q);
			
			foreach ($del_nrcs as $nrc)
			{
				$q = 'DELETE FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$id.' AND item_id='.EVENTS_ITEM_ID.' AND nrc='.$nrc;
				DB::db_query ($q);
			}
			if($del_nrcs) $del_nrcs = null;
			
			$q = 'SELECT min(nrc) as nrc FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$id.' AND item_id='.EVENTS_ITEM_ID;
			$min_nrc = DB::db_fetch_field ($q, 'nrc');
			if ($min_nrc > 0)
			{
				$q = 'UPDATE '.TBL_COMPUTERS_ITEMS.' SET nrc=nrc-'.$min_nrc.' WHERE computer_id='.$id.' AND item_id='.EVENTS_ITEM_ID;
				DB::db_query ($q);
			}
		}
        if($ids) $ids = null;
	}
	
	
	/****************************************************************/
	/* Processing of SNMP items collected about peripherals		*/
	/****************************************************************/
	
	/** [Class Method] This function takes care of the processing of the reported items
	* about peripherals. It is normally invoked from Computer->add_reported_items().
	* The processing and storing of the items is done in a similar manner with that
	* for computers itmes from add_reported_items().
	* @param	Computer		$computer		The Computer object for which the data was received
	* @param	array			$item_data		Associative array with the data received from SOAP about this item
	* @return	bool						True or False if the data was processed OK
	*/
	function process_item_periph ($computer, $item_data)
	{
		$ret = false;
		if ($item_data['id'] and $item_data['obj_class'] and $item_data['obj_id'])
		{
			$time = time ();
			$obj_class = intval($item_data['obj_class']);
			$obj_id = intval($item_data['obj_id']);
			$item_id = intval($item_data['id']);
			
			// Determine what type of logging is set for this item and this profile
			$q = 'SELECT profile_id FROM '.($obj_class==SNMP_OBJ_CLASS_PERIPHERAL ? TBL_PERIPHERALS : TBL_AD_PRINTERS_EXTRAS).' WHERE id='.$obj_id;
			$profile_id = DB::db_fetch_field ($q, 'profile_id');
			if (!$profile_id) return false;
			$q = 'SELECT log_type FROM '.TBL_MONITOR_PROFILES_ITEMS_PERIPH.' WHERE profile_id='.$profile_id.' AND item_id='.$item_id;
			$log_type = DB::db_fetch_field ($q, 'log_type');
			if (!$log_type) return false;
			
			$ret = true;
			$q_val_item = '';
			$q_val_item_log = '';
			$record_item = true;	// Will tell if this reported item will be actually saved in peripherals_items or if only the time needs updating
			
			$new_values_arr = array (); // Will be used for comparing with existing values, to see if logging is needed
			$fields_vals = array (); // Will store the fields values, to be used when updating the peripherals data fields (if needed)
			$need_logging = ($log_type==MONITOR_LOG_ALL or $log_type==MONITOR_LOG_CHANGES);
			
			$max_values = ($monitor_item->multi_values == MONITOR_MULTI_NO ? 1 : count ($item_data['value']));
			$monitor_item = new MonitorItem ($item_id);
			switch ($monitor_item->type)
			{
				case MONITOR_TYPE_INT:
				case MONITOR_TYPE_STRING:
				case MONITOR_TYPE_TEXT:
				case MONITOR_TYPE_FLOAT:
				case MONITOR_TYPE_DATE:
				case MONITOR_TYPE_FILE:
					for ($j=0; $j<$max_values; $j++)
					{
						$val = $computer->get_storing_value ($monitor_item->type, $item_data['value'][$j]['field_values'][0], $item_id);
						$q_val_item.= '('.$obj_id.','.$obj_class.','.$item_id.','.$j.',0,"'.mysql_escape_string($val).'",'.$time.'), ';
						$new_values_arr[$j][0] = $val;
						$fields_vals[0] = $monitor_item->get_formatted_value($val);
                        if($val) $val = null;
					}
					break;
				
				case MONITOR_TYPE_STRUCT:
					for ($j=0; $j<$max_values; $j++)
					{
						// Set the field values for easier access
						$field_values = array();
						for ($k=0; $k<count($item_data['value'][$j]['field_names']); $k++)
						{
							foreach ($monitor_item->struct_fields as $field)
							{
								if ($field->short_name == $item_data['value'][$j]['field_names'][$k])
								{
									// Pass all fields values through the filters
									$field_values [$field->id] = $computer->get_storing_value($field->type, $item_data['value'][$j]['field_values'][$k], $item_id, $field->short_name);
									
									$fields_vals[$field->id] = $field->get_formatted_value($field_values [$field->id]); 
								}
							}
						}
						
						foreach ($field_values as $key => $val)
						{
							$q_val_item.= '('.$obj_id.','.$obj_class.','.$item_id.','.$j.','.$key.',';
							$q_val_item.= '"'.mysql_escape_string($val).'",'.$time.'), ';
							$new_values_arr[$j][$key] = $val;
						}
                        if($field_values) $field_values = null;
					}
					break;
			}
			
			// Check if there is indeed anything needing logging.
			if ($need_logging)
			{
				$record_item = false;	// The item will be recorded for the computer only if it is also logged,
							// otherwise only the reported time will be updated, since there were no changes.
				$q_val_item_log = trim (preg_replace ('/,\s*$/', '', $q_val_item));
				
				if ($q_val_item_log != '')
				{
					$q_val_item_log = 'INSERT INTO '.TBL_PERIPHERALS_ITEMS_LOG.' (obj_id, obj_class, item_id, nrc, field_id, value, reported) VALUES '.$q_val_item_log;
					
					if ($log_type == MONITOR_LOG_ALL)
					{
						// Log all values
						DB::db_query ($q_val_item_log);
						$record_item = true;
					}
					else
					{
						// Check if there were previously logged values. Using "Limit 1" instead of "count()" for speed
						$q_ck_log = 'SELECT obj_id FROM '.TBL_PERIPHERALS_ITEMS_LOG.' WHERE ';
						$q_ck_log.= 'obj_id='.$obj_id.' AND obj_class='.$obj_class.' AND item_id='.$item_id.' LIMIT 1';
						$existing_log = DB::db_fetch_field ($q_ck_log, 'obj_id');
						
						if ($existing_log) 
						{
							// Log only changes, so check if current values are different
							$q_ck_changes = 'SELECT nrc, field_id, value FROM '.TBL_PERIPHERALS_ITEMS.' WHERE ';
							$q_ck_changes.= 'obj_id='.$obj_id.' AND obj_class='.$obj_class.' AND item_id='.$item_id.' ORDER BY nrc ';
							
							$arr = $this->db_fetch_array ($q_ck_changes);
							$old_values_arr = array();
							foreach ($arr as $old_val) $old_values_arr[$old_val->nrc][$old_val->field_id] = $old_val->value;
							
                            if($arr) $arr = null;	
                                
							if ($computer->are_values_changed ($monitor_item, $old_values_arr, $new_values_arr))
							{
								// There are changes, so log the item
								DB::db_query ($q_val_item_log);
								// Since there are changed
								$record_item = true;
							}
							
                            if($old_values_arr) $old_values_arr = null;
						}
						else
						{
							// There hasn't been any logs before, so save it
							DB::db_query ($q_val_item_log);
							$record_item = true;
						}
					}
				}
			}
			
			$q_val_item = trim (preg_replace ('/,\s*$/', '', $q_val_item));
			if (!$record_item)
			{
				// The value hasn't changed, so update the time only
				DB::db_query ('UPDATE '.TBL_PERIPHERALS_ITEMS.' SET reported='.$time.' WHERE obj_id='.$obj_id.' AND obj_class='.$obj_class.' AND item_id='.$item_id);
			}
			else
			{
				// Delete first previous values for the current reported item
				DB::db_query ('DELETE FROM '.TBL_PERIPHERALS_ITEMS.' WHERE obj_id='.$obj_id.' AND obj_class='.$obj_class.' AND item_id='.$item_id);
				
				$q_val_item = 'INSERT INTO '.TBL_PERIPHERALS_ITEMS.' (obj_id, obj_class, item_id, nrc, field_id, value, reported) VALUES '.$q_val_item;
				$this->db_query ($q_val_item);
				
				// For peripherals, if the value have changed then update the peripherals fields data as well
				if ($obj_class == SNMP_OBJ_CLASS_PERIPHERAL)
				{
					// Fetch the mapping between peripheral fields and the peripheral's profile items
					$periph_class_id = DB::db_fetch_field ('SELECT class_id FROM '.TBL_PERIPHERALS.' WHERE id='.$obj_id, 'class_id');
					$q = 'SELECT class_field_id, item_field_id FROM '.TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS.' ';
					$q.= 'WHERE class_id='.$periph_class_id.' AND profile_id='.$profile_id.' AND item_id='.$item_id;
					$fields_mappings = DB::db_fetch_list ($q);
					
					foreach ($fields_mappings as $class_field_id => $item_field_id)
					{
						$q = 'REPLACE INTO '.TBL_PERIPHERALS_FIELDS.' (peripheral_id, field_id, value) VALUES ';
						$q.= '('.$obj_id.','.$class_field_id.',"'.mysql_escape_string($fields_vals[$item_field_id]).'")';
						DB::db_query ($q);
					}
                    if($fields_mappings) $fields_mappings = null;
				}
			}
			
			// Update the heartbeat (contact time);
			$q = 'UPDATE '.($obj_class==SNMP_OBJ_CLASS_PERIPHERAL ? TBL_PERIPHERALS : TBL_AD_PRINTERS_EXTRAS).' set last_contact='.$time.' WHERE id='.$obj_id;
			DB::db_query ($q);
			
			$ret = true;
            
            if($new_values_arr) $new_values_arr = null;
            if($fields_vals) $fields_vals = null;
            if($monitor_item) $monitor_item = null;
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Processing items for network discoveries			*/
	/****************************************************************/
	
	/** [Class Method] This function takes care of the processing of the reported items
	* about networks discoveries. It is normally invoked from Computer->add_reported_items().
	* @param	Computer		$computer		The Computer object from which the discovery was done
	* @param	array			$item_data		Associative array with the data received from SOAP about this item
	* @return	bool						True or False if the data was processed OK
	*/
	function process_item_discoveries ($computer, $item_data)
	{
		$ret = false;
		class_load ('Discovery');
		
		if ($computer->id and $item_data['id']==DISCOVERY_ITEM_ID and is_array($item_data['value']))
		{
			// Load the existing discoveries from the database, to be used for 
			// determining which of the reported discoveries are new and which are not
			$discoveries = Discovery::get_discoveries ($computer->customer_id, array('order_by' => 'last_discovered_desc'));
			
			$time = time (); 		// Just to be sure we use the same stamp everywhere
			$disc_details_ids = array();  	// Array with all the IDs of the DiscoverySettingDetail objects involved in this reporting
			$discoveries_start = array();	// Associative arrays for details IDs with the start and end times of the discoveries
			$discoveries_end = array();
			
			
			// Now process the reported discoveries
			foreach ($item_data['value'] as $d)
			{
				// Put the collected data in an associative array
				$data = array ();
				foreach ($d['field_names'] as $idx => $field_name)
				{
					$data[$field_name] = trim($d['field_values'][$idx]);
					// To be sure we don't have problems with NOT NULL columns in database
					if (is_null($data[$field_name]) or $data[$field_name]=='NULL') $data[$field_name] = ''; 
				}
				
				// Always make sure the we have the ID of the corresponding DiscoverySettingDetail object
				if ($data['detail_id'])
				{
					$detail_id = $data['detail_id'];
					
					// If this detail ID was not seen before in this run, add it to the list
					// and clear all unidentfiable objects for it
					if (!in_array($detail_id, $disc_details_ids))
					{
						$disc_details_ids[] = $detail_id;
						Discovery::clear_unidentifiable_devices ($detail_id);
					}
					
					
					// Make sure we only use valid MAC addresses
					if (is_bogus_mac($data['mac'])) $data['mac'] = '';
					if (is_bogus_mac($data['nb_mac'])) $data['nb_mac'] = '';
					
					$discovery = new Discovery ();
					$discovery->load_reported_data ($data);
					
					// Check if this is a new device or not
					if ($exist_id = Discovery::getExistingId ($discovery, $discoveries))
					{
						// Reload the object from the database, to preserve any needed data
						$discovery = new Discovery ($exist_id);
						$discovery->load_reported_data ($data);
					}
					
					// If this discovery doesn't have a Keyos match already, search for one
					if (!$discovery->matched_obj_id)
					{
						$matches = $discovery->get_keyos_matches (true, $computer->customer_id);
						if (count($matches) == 1)
						{
							$discovery->set_matched_object ($matches[0]['obj_class'], $matches[0]['obj_id'], true);
						}
					}
					
					// Save the discovery to the database
					$discovery->last_discovered = $time;
					$discovery->save_data ();
					
					if ($data['batch_start'])
					{
						if (!$discoveries_start[$detail_id]) $discoveries_start[$detail_id] = $data['batch_start'];
						else $discoveries_start[$detail_id] = min($data['batch_start'], $discoveries_start[$detail_id]);
					}
					if ($data['batch_end'])
					{
						if (!$discoveries_end[$detail_id]) $discoveries_end[$detail_id] = $data['batch_end'];
						else $discoveries_end[$detail_id] = max($data['batch_end'], $discoveries_end[$detail_id]);
					}
				}
			}
			
			// Finally, mark in all involved DiscoverySettingDetail objects that a report has been received
			foreach ($disc_details_ids as $detail_id)
			{
				$detail = new DiscoverySettingDetail ($detail_id);
				if (!$discoveries_start[$detail_id] or !$discoveries_end[$detail_id]) $duration = 0;
				else $duration = ($discoveries_end[$detail_id] - $discoveries_start[$detail_id]);
				
				$detail->mark_contact ($time, $duration);
			}
		}
		
		return $ret;
	}
	
	
	/****************************************************************/
	/* Determine computers networks					*/
	/****************************************************************/
	
	
	/** [Class Method] Returns all networks available for a customer. The networks are determined from
	* the network interfaces reported by all customer's computers.
	* @param	int			$customer_id		The ID of the customer
	* @param	array			$computers_list		(Optional) A list of customer's computers, the computer IDs being the array keys.
	*								This is useful if the list of computers has already been loaded before calling
	*								this function, so the function can skip this step and reduce running time.
	* @return	array						Array with the found networks, the elements being associative array with the 
	*								following fields: network_address, network_mask, broadcast_address,
	*								hosts_number (the number of IPs in the range, NOT the actual number of computers found),
	*								ip_min, ip_max (the first and last IP in the range) and computer, which is an array
	*								with the details of each computer found in each network, the elements being associative
	*								arrays with the following fields: computer_id, interface_name, ip_address.
	*/
	function get_customer_networks ($customer_id, $computers_list = array ())
	{
		
		$networks_unique = array ();
		if ($customer_id)
		{
			// Load the list of computers, if it was not specified already in the parameters
			if (empty($computers_list) or !is_array($computers_list))
			{
				$computers_list = Computer::get_computers_list (array('customer_id' => $customer_id, 'order_by' => 'type'));
			}
			
			// For each computer in the list, build the list of available interfaces/networks
			$networks = array ();
			foreach ($computers_list as $computer_id => $computer_name)
			{
				if (!isset($networks[$computer_id])) $networks[$computer_id];
				$data = new ComputerItem ($computer_id, NET_ADAPTERS_ITEM_ID);
				foreach ($data->val as $v)
				{
					$nets = get_subnets($v->value[FIELD_ID_NET_IP], $v->value[FIELD_ID_NET_MASK]);
					foreach ($nets as $idx => $net)
					{
						$nets[$idx]['interface_name'] = $v->value[FIELD_ID_NET_NAME];
						$nets[$idx]['ip_address'] = $v->value[FIELD_ID_NET_IP];
					}
					$networks[$computer_id] = array_merge($networks[$computer_id], $nets);
				}
			}
			
			// Now select the unique networks and set the computers which belong to each of the networks
			$networks_unique = array ();
			foreach ($networks as $computer_id => $nets)
			{
				foreach ($nets as $net)
				{
					$found = false;
					foreach ($networks_unique as $idx => $net_unique)
					{
						if ($net['network_address']==$net_unique['network_address'] and $net['broadcast_address']==$net_unique['broadcast_address'])
						{
							$found = true;
							$networks_unique[$idx]['computers'][] = array('computer_id'=>$computer_id, 'interface_name'=>$net['interface_name'], 'ip_address'=>$net['ip_address']);
						}
					}
					if (!$found)
					{
						$net['computers'][] = array ('computer_id'=>$computer_id, 'interface_name'=>$net['interface_name'], 'ip_address'=>$net['ip_address']);
						$networks_unique[] = $net;
					}
				}
			}
			
			// Clear the fields which are not needed anymore
			for ($i=0; $i<count($networks_unique); $i++)
			{
				unset($networks_unique[$i]['interface_name']);
				unset($networks_unique[$i]['ip_address']);
			}
		}
		return $networks_unique;
	}
	
	/****************************************************************/
	/* Detection of computer conflicts and problems			*/
	/****************************************************************/
	
	/** Returns all computers that have the same MAC address 
	* @return	array					Associative array, the  keys being the conflicting MAC addresses
	*							and the values being arrays of generic objects (one for each computer
	*							with the same MAC), having the fields: id, customer_id, customer_name, 
	*							netbios_name, mac_address, last_contact.
	*/
	function get_conflicting_macs ()
	{
		$ret = array ();
		$q = 'SELECT c.mac_address, count(distinct c.id) as cnt FROM '.TBL_COMPUTERS.' c ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON c.id=b.computer_id ';
		$q.= 'WHERE (b.computer_id IS NULL OR ((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) ';
		$q.= 'GROUP BY c.mac_address HAVING cnt>1 ORDER BY mac_address';
		$conflicts = DB::db_fetch_list ($q);
		
		foreach ($conflicts as $mac => $cnt)
		{
			$q = 'SELECT c.id, c.customer_id, cust.name as customer_name, c.netbios_name, c.mac_address, c.last_contact ';
			$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 ';
			$q.= 'WHERE c.mac_address="'.mysql_escape_string($mac).'" ORDER BY c.id ';
			$ret[$mac] = DB::db_fetch_array ($q);
		}
		
		return $ret;
	}
	
	/** Returns all computers that have the same Netbios name
	* @return	array					Associative array, the keys being the conflicting names and the 
	*							values being arrays of generic objects (one for each computer with 
	*							the same name), having the fields: id, customer_id, customer_name,
	*							netbios_name, mac_address, last_contact
	*/
	function get_conflicting_names ()
	{
		$ret = array ();
		$q = 'SELECT c.netbios_name, count(distinct c.id) as cnt FROM '.TBL_COMPUTERS.' c ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 ';
		$q.= 'LEFT OUTER JOIN '.TBL_VALID_DUP_NAMES.' d ON c.id=d.computer_id AND c.netbios_name=d.netbios_name ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON c.id=b.computer_id ';
		$q.= 'WHERE c.netbios_name<>"" AND d.id IS NULL AND ';
		$q.= '(b.computer_id IS NULL OR ((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) ';
		$q.= 'GROUP BY c.netbios_name HAVING cnt>1 ORDER BY c.netbios_name';
		$conflicts = DB::db_fetch_list ($q);
		
		foreach ($conflicts as $name => $cnt)
		{
			$q = 'SELECT c.id, c.customer_id, cust.name as customer_name, c.netbios_name, c.mac_address, c.last_contact ';
			$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 ';
			$q.= 'LEFT OUTER JOIN '.TBL_VALID_DUP_NAMES.' d ON c.id=d.computer_id AND c.netbios_name=d.netbios_name ';
			$q.= 'WHERE c.netbios_name="'.mysql_escape_string($name).'" AND d.id IS NULL ORDER BY c.id ';
			$ret[$name] = DB::db_fetch_array ($q);
		}
		
		return $ret;
	}
	
	/** Returns the computers the have changed their names too often recently. This is determined by
	* checking the computers_items_log table, which stores the reported values for the current week.
	* A computer is considered "name swinger" if it appears to have changed its name back and forth,
	* which usually means that two different computers are reporting to the same ID. 
	* If a computer changes its name and then the old name doesn't appear again in the log, even if
	* this is happends multiple times, it is NOT considered a "name swing", since most likely it
	* is just a normal name change for the computer.
	* @return	array					Associative array, the keys being computer IDs for which
	*							name 
	*/
	function get_name_swingers ()
	{
		$ret = array ();
		
		$q = 'SELECT ci.computer_id, count(distinct ci.value) as cnt FROM '.TBL_COMPUTERS_ITEMS_LOG.' ci ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON ci.computer_id=b.computer_id ';
		$q.= 'WHERE ci.item_id='.NAME_ITEM_ID.' AND ';
		$q.= '(b.computer_id IS NULL OR ((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) ';
		$q.= 'GROUP BY ci.computer_id HAVING cnt>1 ORDER BY ci.computer_id';
		$swingers = DB::db_fetch_list ($q);
		
		foreach ($swingers as $id => $cnt)
		{
			$q = 'SELECT value, max(reported) as max, min(reported) as min ';
			$q.= 'FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE computer_id='.$id.' AND item_id='.NAME_ITEM_ID.' ';
			$q.= 'GROUP BY value ORDER BY value';
			$data = DB::db_fetch_array ($q);
			
			// Check if it is indeed a case of name swingers, by checking if the report intervals for each name are overlapping
			$is_swinger = false;
			foreach ($data as $d1)
			{
				foreach ($data as $d2)
				{
					if (($d1->min>$d2->min and $d1->min<$d2->max) or ($d1->max>$d2->min and $d1->max<$d2->max))
					{
						$is_swinger = true;
						break;
					}
				}
				if ($is_swinger) break;
			}
			
			if ($is_swinger) foreach ($data as $d) $ret[$id][] = $d->value;
		}
		
		return $ret;
	}
	
	/** [Class Method] Cleans invalid names for a computer name swinger from the computer items log, keeping only the specified name.
	* This operation is needed because once the root problem which caused the name swinging has been solved, the invalid
	* names need to removed from the logs so the alert will not be raised anymore.
	* @param	int				$computer_id		The ID of the computer for which to perform the cleanup
	* @param	string				$keep_name		The computer name to keep
	*/
	function clean_name_swinger_logs ($computer_id, $keep_name)
	{
		if ($computer_id and $keep_name)
		{
			$q = 'SELECT DISTINCT reported FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE computer_id='.$computer_id.' AND ';
			$q.= 'item_id='.NAME_ITEM_ID.' AND value<>"'.mysql_escape_string($keep_name).'" ';
			$dates = DB::db_fetch_vector ($q);
			foreach ($dates as $date)
			{
				// Will delete all data reported for that computer at the same time when an invalid name
				// has been reported, because that data is invalid as well
				$q = 'DELETE FROM '.TBL_COMPUTERS_ITEMS_LOG.' WHERE computer_id='.$computer_id.' AND reported='.$date;
				DB::db_query ($q);
			}
		}
	}
	
	/** [Class Method] Returns all the computers for which the remote IPs are not in the allowed list of remote IPs for their
	* respective customers. These can indicate that either the computers are assigned to the wrong customer
	* or that the new IP addresses needs to be added to the allowed list.
	*/
	function get_conflicting_reporting_ips ()
	{
		
		$ret = array ();

		/*XXXX disabled by Victor -- Still have to see what's the problem really -- I think the way 
		the discovery is done... must automate more
		*/
		/*$q = 'SELECT c.id, c.customer_id, cust.name as customer_name, c.netbios_name, c.mac_address, c.last_contact, c.remote_ip ';
		$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id AND cust.active=1 ';
		$q.= 'LEFT OUTER JOIN '.TBL_CUSTOMERS_ALLOWED_IPS.' ai ON c.remote_ip=ai.remote_ip ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_BLACKOUTS.' b ON c.id=b.computer_id ';
		$q.= 'WHERE (c.remote_ip<>"" AND c.remote_ip<>"0.0.0.0") AND (ai.customer_id IS NULL OR c.customer_id<>ai.customer_id) AND ';
		$q.= '(b.computer_id IS NULL OR ((b.start_date<>0 AND b.start_date>'.time().') OR ((b.end_date<>0 AND b.end_date<'.time().')))) ';
		$q.= 'ORDER BY cust.name, c.netbios_name ';
		$data = DB::db_fetch_array ($q);
		
		foreach ($data as $d) $ret[$d->remote_ip][$d->customer_id][] = $d;*/
		
		return $ret;
	}
	
	
	/** [Class Method] Returns all customer IDs which have computers reporting through a specific IP address
	* @return	array							Associative array, the keys being customer IDs
	*									and the values being the number of computers
	*									found to be reporting through the specified IP
	*									for that customer
	*/
	function get_customers_using_ip ($ip)
	{
		$ret = array ();
		
		if ($ip)
		{
			$q = 'SELECT customer_id, count(*) FROM '.TBL_COMPUTERS.' c WHERE remote_ip="'.mysql_escape_string($ip).'" ';
			$q.= 'GROUP BY customer_id ORDER BY 2,1 ';
			$ret = DB::db_fetch_list ($q);
		}
		
		return $ret;
	}
}

?>