<?
class_load ('Customer');
class_load ('ActionType');
class_load ('Ticket');

/**
* Class for handling syncronisation of the Keyos database with the ERP database.
*
* The ErpSync objects themselves are not actually stored to the database,
* they are only instantiated in order to establish the connection to the
* ERP database.
*
*/

class ErpSync extends Base
{
	/** Specifies if the connection with the ERP database has been established
	* @var bool */
	var $db_connected = false;

	/** Class constructor. Establishes a connection to the ERP database
	* @param	bool		$do_connect		Specifies if the connection to the ERP database should be established or not.
	*/
	function ErpSync ($do_connect = true)
	{
		if ($do_connect) $this->connect_to_erp ();
	}
	
	
	/** Overloading of the load_data() method. It does nothing, since there
	* is no data to be loaded from database
	*/
	function load_data () {}
	
	/** Overloading of the save_data() method. It does nothing, since there
	* is no data to be saved to database
	*/
	function save_data () {}
	
	/** Overloading of the delete() method. It does nothing, since there
	* is no data to be deleted from the databse
	*/
	function delete () {}
	
	
	/** Creates the connection to the ERP database */
	function connect_to_erp ()
	{
		$conn = @mssql_connect (ERP_DB_HOST, ERP_DB_USER, ERP_DB_PWD);
		if ($conn)
		{
			$db_selected = @mssql_select_db (ERP_DB_NAME);
			if ($db_selected) $this->db_connected = true;
			else error_msg ('Failed opening the ERP database.');
		}
		else
		{
			error_msg ('Failed connecting to the ERP database. '.ERP_DB_HOST);
		}
	}
	
	
	/** Returns the customers from the ERP database
	* @return	array					Array of generic objects, containing customer info from the ERP
	*							database. Besides the ERP fields, each object has a special
	*							field called 'sync_stat', specifying the syncronisation status
	*							for that item - see $GLOBALS['ERP_SYNC_STATS']
	*/
	function get_erp_customers ()
	{
		$ret = array ();
		
		if ($this->db_connected)
		{
			$q = 'SELECT c_nom as erp_name, c_id as erp_id, c_adresse, c_adresse2, c_codep, c_ville, c_pays, c_cle2, ';
			$q.= 'c_tarif, c_cat1, c_cat2 ';
			$q.= 'FROM cli ORDER BY c_nom';
			$h = mssql_query ($q);
			while ($c = mssql_fetch_object ($h)) $ret[] = $c;
			
			// Get the list of ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_CUSTOMERS.' WHERE erp_id<>""';
			$ks_erp_ids = $this->db_fetch_list ($q);
			$selected_erp_ids = array ();
			for ($i=0; $i<count($ret); $i++)
			{
				$r = &$ret[$i];
				
				$r->erp_id = trim($r->erp_id);
				$r->c_cat1 = trim($r->c_cat1);
				$r->c_cat2 = trim($r->c_cat2);
				// Translate the codes
				$r->contract_type = $GLOBALS['ERP_CONTRACT_TYPES'][$r->c_cat1];
				$r->contract_sub_type = $GLOBALS['ERP_CUST_SUBTYPES'][$r->c_cat2];
				$r->price_type = $GLOBALS['ERP_CUST_PRICETYPES'][$r->c_tarif];
				
				// Make sure that undefined values in ERP are presented as zeros
				if (!$r->contract_type) $r->contract_type = 0;
				if (!$r->contract_sub_type) $r->contract_sub_type = 0;
				if (!$r->price_type) $r->price_type = 0;
				
				//debug($ks_erp_ids[trim($r->erp_id)]);
				if (isset($ks_erp_ids[$r->erp_id]))
				{
					$r->customer_id =$ks_erp_ids[$r->erp_id];
					$r->customer = new Customer ($r->customer_id);
					
					// Check if any information was modified
					if (
						$r->erp_name != $r->customer->name or
						$r->contract_type != $r->customer->contract_type or
						$r->contract_sub_type != $r->customer->contract_sub_type or
						$r->price_type != $r->customer->price_type
					)
					{
						$r->sync_stat = ERP_SYNC_STAT_MODIFIED;
					}
					
					// Check if complete info is available in ERP
					if (!$r->contract_type) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
				}
				else
				{
					// This is a customer which doesn't exist in Keyos, check if it is fully defined in ERP
					if (!$r->contract_type) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
					else $r->sync_stat = ERP_SYNC_STAT_ERP_NEW;
				}
				$selected_erp_ids[] = $r->erp_id;
				//debug($r);
			}
			
			// Append the users which are defined only in Keyos
			$q = 'SELECT id, erp_id FROM '.TBL_CUSTOMERS.' WHERE active=1 ORDER BY name';
			$ks_ids = $this->db_fetch_list ($q);
			foreach ($ks_ids as $customer_id => $erp_id)
			{
				if (!$erp_id or ($erp_id and !in_array($erp_id, $selected_erp_ids)))
				{
					$n = null;
					$n->sync_stat = ERP_SYNC_STAT_KS_NEW;
					$n->customer_id = $customer_id;
					$n->customer = new Customer ($customer_id);
					
					// Insert the customer in alphabetical order
					for ($j = 0; ($j<count($ret) and (strtolower($n->customer->name) > strtolower($ret[$j]->erp_name))); $j++);
					array_splice ($ret, $j, 0, array($n));
				}
			}
		}
		
		return $ret;
	}
	
	function sync_erp_customers()
	{
		if ($this->db_connected)
		{
			// Get the customers from the ERP system
			$erp_customers = $this->get_erp_customers ();
			
			// Get the list of ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_CUSTOMERS.' WHERE erp_id<>""';
			$ks_erp_ids = $this->db_fetch_list ($q);
			
			for ($i = 0; $i<count($erp_customers); $i++)
			{
				$r = &$erp_customers[$i];
				if ($r->sync_stat == ERP_SYNC_STAT_ERP_NEW)
				{
					// This is a new customer which can be imported in Keyos
					$customer = new Customer ();
					$customer->name = $r->erp_name;
					$customer->erp_id = $r->erp_id;
					$customer->contract_type = $r->contract_type;
					$customer->contract_sub_type = $r->contract_sub_type;
					$customer->price_type = $r->price_type;
					
					$customer->save_data ();
				}
				elseif ($r->sync_stat == ERP_SYNC_STAT_MODIFIED and isset($ks_erp_ids[$r->erp_id]))
				{
					// This is an customer that has been modified in ERP
					$customer = new Customer ($ks_erp_ids[$r->erp_id]);
					$customer->name = $r->erp_name;
					$customer->erp_id = $r->erp_id;
					$customer->contract_type = $r->contract_type;
					$customer->contract_sub_type = $r->contract_sub_type;
					$customer->price_type = $r->price_type;
					
					$customer->save_data ();
				}
			}
		}
	}
	
	
	/** Get the list of action type categories from Keyos */
	function get_erp_actypes_categories ()
	{
		$ret = array ();
		
		if ($this->db_connected)
		{
			// Action type categories
			$q = 'SELECT id as erp_id, nom as name FROM ss_famil WHERE id_famille="Q1J50PXW11" or id_famille="S1J50PYRYC" ORDER BY nom';
			$h = mssql_query ($q);
			while ($a = mssql_fetch_object ($h)) $ret[] = $a;
			
			// Get the categories already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_ACTION_TYPES_CATEGORIES.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			// Keep track of the ERP IDs we already handled, in case some of the KS action types have an ERP ID that doesn't exist anymore in ERP
			$selected_erp_ids = array ();
			for ($i=0; $i<count($ret); $i++)
			{
				$r = &$ret[$i];
				$r->erp_id = trim($r->erp_id);
				if (isset($ks_erp_ids[$r->erp_id]))
				{
					// This ERP action type already exists in Keyos
					$r->id = $ks_erp_ids[$r->erp_id];
					$r->category = new ActionTypeCategory ($r->id);
					
					// Check if the category has been modified in ERP
					if ($r->category->name != $r->name) $r->sync_stat = ERP_SYNC_STAT_MODIFIED;
					
					// Check if the ERP definition is complete
					if (!$r->erp_id or !$r->name) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
				}
				else
				{
					// This is a category which doesn't exist in Keyos, check if it is fully defined in ERP
					if (!$r->erp_id or !$r->name) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
					else $r->sync_stat = ERP_SYNC_STAT_ERP_NEW;
				}
				$selected_erp_ids[] = $r->erp_id;
			}
		}
		
		return $ret;
	}
	
	function sync_erp_actypes_categories ()
	{
		if ($this->db_connected)
		{
			// Get the action types categories from the ERP system
			$erp_actypes_categories = $this->get_erp_actypes_categories ();
			
			// Get the ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_ACTION_TYPES_CATEGORIES.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			
			for ($i = 0; $i<count($erp_actypes_categories); $i++)
			{
				$r = &$erp_actypes_categories[$i];
				if ($r->sync_stat == ERP_SYNC_STAT_ERP_NEW)
				{
					// This is a new category which can be imported in Keyos
					$category = new ActionTypeCategory ();
					$category->erp_id = $r->erp_id;
					$category->name = $r->name;
					$category->save_data ();
				}
				elseif ($r->sync_stat == ERP_SYNC_STAT_MODIFIED and isset($ks_erp_ids[$r->erp_id]))
				{
					// This is an existing action type that has been modified in ERP
					$category = new ActionTypeCategory ($ks_erp_ids[$r->erp_id]);
					$category->name = $r->name;
					$category->save_data ();
				}
			}
		}
	}
	
	function get_erp_actypes ()
	{
		$ret = array ();
		
		if ($this->db_connected)
		{
			// Articles
			$q = 'SELECT stock.s_id as erp_id, stock.s_modele as erp_name, stock.s_cle3 as erp_code, familles.nom as family, ';
			$q.= 'stock.s_id_ssfam, stock.s_cat2, stock.s_cat3, stock.s_id_famil ';
			$q.= 'FROM stock, familles ';
			$q.= 'WHERE familles.id=s_id_famil AND stock.s_id_rayon="R000000004" AND ';
			$q.= '(stock.s_id_famil="Q1J50PXW11" OR stock.s_id_famil="S1J50PYRYC") ';
			/*
			stock.s_cat2 is the categorie2 (Basic/TC/Keypro)
			stock.s_cat3 is the categorie3 (Basic/TC level1/ TC level2 / TC level3/Keypro /GlobalPro)
			stock.s_cat4 is the categorie3 (HourlyBased/FixedBased)
			
			stock.s_id_rayon='R000000004' (service ks)
			stock.s_id_famil='Q1J50PXW11' (regie tc)
			stock.s_id_famil='S1J50PYRYC' (regie autre)
			
			famille ([ID_RAYON] => R000000004): 
				Abo: P1J50PXPEQ
				Techniciens: P1KR12M74O
				Régie TC: Q1J50PXW11
				Autres: Q1KR12NDTT
				Régie autre: S1J50PYRYC
			*/
			
			$h = mssql_query ($q);
			while ($a = mssql_fetch_object ($h)) $ret[] = $a;
			
			// Get the ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_ACTION_TYPES.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			// Keep track of the ERP IDs we already handled, in case some of the KS action types have an ERP ID that doesn't exist anymore in ERP
			$selected_erp_ids = array ();
			$erp_actypes = ActionTypeCategory::get_erp_categories_translation ();
			for ($i=0; $i<count($ret); $i++)
			{
				$r = &$ret[$i];
				$r->erp_id = trim($r->erp_id);
				$r->s_id_ssfarm = trim($r->s_id_ssfarm);
				$r->s_cat2 = trim($r->s_cat2);
				$r->s_cat3 = trim($r->s_cat3);				

				$r->category = $erp_actypes[$r->s_id_ssfam];
				$r->contract_types = $GLOBALS['ERP_CONTRACT_TYPES'][$r->s_cat2];
				$r->contract_sub_type = $GLOBALS['ERP_CONTRACT_SUBTYPES_ACTIONS'][$r->s_cat2];
				$r->price_type = $GLOBALS['ERP_PRICE_TYPES'][$r->s_cat3];
				
				if (isset($ks_erp_ids[$r->erp_id]))
				{
					// This ERP action type already exists in Keyos
					$r->action_type_id = $ks_erp_ids[$r->erp_id];
					$r->action_type = new ActionType ($r->action_type_id);
					
					// Check if the action type has been modified in ERP
					if (
						$r->action_type->erp_code != $r->erp_code or 
						$r->action_type->erp_name != $r->erp_name or 
						$r->action_type->category != $r->category or
						$r->action_type->contract_types != $r->contract_types or 
						$r->action_type->contract_sub_type != $r->contract_sub_type or 
						$r->action_type->price_type != $r->price_type or
						$r->action_type->family != $r->family
					) $r->sync_stat = ERP_SYNC_STAT_MODIFIED;
					
					// Check if the ERP definition is complete
					if (!$r->erp_code or !$r->contract_types or !$r->contract_sub_type or !$r->price_type or !$r->category) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
				}
				else
				{
					// This is an action type which doesn't exist in Keyos, check if it is fully defined in ERP
					if (!$r->erp_code or !$r->contract_types or !$r->contract_sub_type or !$r->price_type or !$r->category) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
					else $r->sync_stat = ERP_SYNC_STAT_ERP_NEW;
				}
				$selected_erp_ids[] = $r->erp_id;
			}
		}
		
		return $ret;
		
	}
	
	function sync_erp_actypes ()
	{
		if ($this->db_connected)
		{
			// Get the action types from the ERP system
			$erp_actypes = $this->get_erp_actypes ();
			
			// Get the ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_ACTION_TYPES.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			
			for ($i = 0; $i<count($erp_actypes); $i++)
			{
				$r = &$erp_actypes[$i];
				if ($r->sync_stat == ERP_SYNC_STAT_ERP_NEW)
				{
					// This is a new action type which can be imported in Keyos
					$action_type = new ActionType ();
					$action_type->erp_code = $r->erp_code;
					$action_type->erp_id = $r->erp_id;
					$action_type->name = $r->erp_name;
					$action_type->erp_name = $r->erp_name;
					$action_type->category = $r->category;
					$action_type->contract_types = $r->contract_types;
					$action_type->contract_sub_type = $r->contract_sub_type;
					$action_type->price_type = $r->price_type;
					$action_type->family = $r->family;
					$action_type->save_data ();
				}
				elseif ($r->sync_stat == ERP_SYNC_STAT_MODIFIED and isset($ks_erp_ids[$r->erp_id]))
				{
					// This is an existing action type that has been modified in ERP
					$action_type = new ActionType ($ks_erp_ids[$r->erp_id]);
					$action_type->erp_code = $r->erp_code;
					$action_type->erp_name = $r->erp_name;
					$action_type->category = $r->category;
					$action_type->contract_types = $r->contract_types;
					$action_type->contract_sub_type = $r->contract_sub_type;
					$action_type->price_type = $r->price_type;
					$action_type->family = $r->family;
					$action_type->save_data ();
				}
			}
		}
	}
	
	
	/** Fetch from the ERP system the activities to be used for Timesheets */
	function get_erp_activities ()
	{
		$ret = array ();
		class_load ('Activity');
		
		if ($this->db_connected)
		{
		/*
		[4] => stdClass Object
        (
            [id] => H1KY0YA0M9
            [lib] => Cécile Blitz
            [erp_id_service] => 137108
            [erp_id_travel] => 137099
            [erp_id_adminw] => 140453 - O1MH0X4XKM
            [erp_id_holid] => 140463 - P1MH0X52UB
	    
	    
        )
		*/
			
			// Activities
			$q = 'SELECT g.id as erp_id, g.lib as erp_name FROM gamenum g ';
			$q.= 'WHERE g.id_type="L1KY0X3EG3" and g.lib<>"" and g.lib<>"1H"';
			
			$h = mssql_query ($q);
			while ($a = mssql_fetch_object ($h)) $ret[] = $a;
			
			
			// Fetch a list with all erp IDs for users
			$users_ids = DB::db_fetch_list ('SELECT erp_id, id FROM '.TBL_USERS.' WHERE erp_id<>""');
			// For each activity ERP ID, make a list with the user-specific codes
			// s.s_gamenum2 will be the ERP ID of a user, g.id will be ERP ID of an activity, s.s_id is the "code" for that user-activity combination
			$q = 'SELECT s.s_gamenum2 as user_id, g.id as activity_id, s.s_id as code FROM gamenum g, stock s WHERE ';
			$q.= 'g.id=s.s_gamenum1 AND g.lib<>"" AND g.lib<>"1H" ';
			$h = mssql_query ($q);
			$users_activities = array ();
			while ($a = mssql_fetch_object ($h)) $users_activities[$a->activity_id][$users_ids[$a->user_id]] = $a->code;
			// Sort each array by user ID
			foreach ($users_activities as $erp_id => $codes) ksort ($users_activities[$erp_id]);
			
			
			/*
			[A1MH0XGOC9] => Array
        (
            [4] => 140569
            [26] => 140570
            [14] => 140571
            [28] => 140572
            [468] => 140573
            [21] => 140574
            [2] => 140575
            [5] => 140576
            [7] => 140577
            [1] => 140578
        )*/
			
			// Get the ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_ACTIVITIES.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			// Keep track of the ERP IDs we already handled, in case some of the KS activities have an ERP ID that doesn't exist anymore in ERP
			$selected_erp_ids = array ();
			for ($i=0; $i<count($ret); $i++)
			{
				$r = &$ret[$i];
				if (isset($ks_erp_ids[$r->erp_id]))
				{
					// This ERP action type already exists in Keyos
					$r->activity_id = $ks_erp_ids[$r->erp_id];
					$r->users_codes = $users_activities[$r->erp_id];
					$r->activity = new Activity ($r->activity_id);
					$r->activity->load_users_codes ();
					
					// Check if the ERP definition is complete
					if (!$r->erp_id or !$r->erp_name) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
					
					// Check if the service and travel ERP IDs are the same
					elseif ($r->erp_id != $r->activity->erp_id or $r->erp_name != $r->activity->erp_name or $r->users_codes!=$r->activity->users_codes) $r->sync_stat = ERP_SYNC_STAT_MODIFIED;
				}
				else
				{
					// This is a category which doesn't exist in Keyos, check if it is fully defined in ERP
					if (!$r->erp_id or !$r->erp_name) $r->sync_stat = ERP_SYNC_STAT_ERP_INCOMPLETE;
					else $r->sync_stat = ERP_SYNC_STAT_ERP_NEW;
				}
				$selected_erp_ids[] = $r->erp_id;
			}
			
			// Append the activities which are defined only in Keyos
			$q = 'SELECT id, erp_id FROM '.TBL_ACTIVITIES.' ORDER BY name';
			$ks_ids = $this->db_fetch_list ($q);
			foreach ($ks_ids as $activity_id => $erp_id)
			{
				if (!$erp_id or ($erp_id and !in_array($erp_id, $selected_erp_ids)))
				{
					$n = null;
					$n->sync_stat = ERP_SYNC_STAT_KS_NEW;
					$n->activity_id = $activity_id;
					$n->activity = new Activity ($activity_id);
					
					// Insert the customer in alphabetical order
					for ($j = 0; ($j<count($ret) and (strtolower($n->actvity->name) > strtolower($ret[$j]->erp_name))); $j++);
					array_splice ($ret, $j, 0, array($n));
				}
			}
		}
		return $ret;
		
	}
	
	/** Synchronize the activities (for Timesheets) in Keyos with the ones from ERP */
	function sync_erp_activities ()
	{
		if ($this->db_connected)
		{
			// Get the action types categories from the ERP system
			$erp_activities = $this->get_erp_activities ();
			
			// Get the ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_ACTIVITIES.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			
			for ($i = 0; $i<count($erp_activities); $i++)
			{
				$r = &$erp_activities[$i];
				if ($r->sync_stat == ERP_SYNC_STAT_ERP_NEW)
				{
					// This is a new category which can be imported in Keyos
					$activity = new Activity ();
					$activity->erp_id = $r->erp_id; 
					$activity->name = $r->erp_name;
					$activity->erp_name = $r->erp_name;
					$activity->save_data ();
					$activity->set_users_codes ($r->users_codes);
				}
				elseif ($r->sync_stat == ERP_SYNC_STAT_MODIFIED and isset($ks_erp_ids[$r->erp_id]))
				{
					// This is an existing action type that has been modified in ERP
					$activity = new Activity ($ks_erp_ids[$r->erp_id]);
					$activity->erp_name = $r->erp_name;
					$activity->save_data ();
					$activity->set_users_codes ($r->users_codes);
				}
			}
		}
	}
	
	
	
	
	
	/** Returns the engineers and associated IDs from the ERP database 
	*/
	function get_erp_engineers ($get_ks = true)
	{
		$ret = array ();
		
		if ($this->db_connected)
		{
			// Fetch from the ERP database the list of engineers and the travel and service ERP IDs
			$q = 'SELECT g.id as erp_id, g.lib as erp_name, s1.s_id as erp_id_service, s2.s_id erp_id_travel ';
			$q.= 'FROM gamenum g, stock s1, stock s2 ';
			$q.= 'WHERE g.id=s1.s_gamenum2 AND g.id=s2.s_gamenum2 AND ';
			$q.= 's1.s_id_rayon="R000000004" AND s2.s_id_rayon="R000000004" AND s1.s_id_famil="P1KR12M74O" AND s2.s_id_famil="P1KR12M74O" AND ';
			$q.= 's1.s_gamtyp1="L1KY0X3EG3" AND s2.s_gamtyp1="L1KY0X3EG3" AND ';
			$q.= 's1.s_gamenum1="O1KY0X40EG" AND '; // Filter s1 to services (1H) articles
			$q.= 's2.s_gamenum1="N1KY0X3UW3" '; // Filter s2 to travel costs articles
			$q.= 'ORDER BY g.lib';
			
			$h = mssql_query ($q);
			while ($e = mssql_fetch_object ($h)) $ret[] = $e;
			
			// Get the list of ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_USERS.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			// Keep track of the ERP IDs we already handled, in case some of the KS users have an ERP ID that doesn't exist anymore in ERP
			$selected_erp_ids = array ();
			for ($i=0; $i<count($ret); $i++)
			{
				$r = &$ret[$i];
				if (isset($ks_erp_ids[$r->erp_id]))
				{
					$r->user_id = $ks_erp_ids[$r->erp_id];
					$r->user = new User ($r->user_id);
					$r->actype_travel = ActionType::get_user_travel_cost ($r->user->id);
					
					// Check if the service and travel ERP IDs are the same
					if (
						$r->erp_id_service != $r->user->erp_id_service or
						$r->erp_id_travel != $r->user->erp_id_travel or
						!$r->actype_travel or
						($r->actype_travel->id and $r->actype_travel->erp_id != $r->erp_id_travel)
					) $r->sync_stat = ERP_SYNC_STAT_MODIFIED;
				}
				else
				{
					// This engineer is only defined in ERP
					$r->sync_stat = ERP_SYNC_STAT_ERP_NEW;
				}
				$selected_erp_ids[] = $r->erp_id;
			}
			
			// Append the users which are defined only in Keyos, if requested
			if ($get_ks)
			{
				$q = 'SELECT id, erp_id FROM '.TBL_USERS.' WHERE type='.USER_TYPE_KEYSOURCE.' AND active=1 ORDER BY fname, lname';
				$ks_ids = $this->db_fetch_list ($q);
				
				foreach ($ks_ids as $user_id => $erp_id)
				{
					if (!$erp_id or ($erp_id and !in_array($erp_id, $selected_erp_ids)))
					{
						$n = &$ret[];
						$n->sync_stat = ERP_SYNC_STAT_KS_NEW;
						$n->user_id = $user_id;
						$n->user = new User ($user_id);
					}
				}
			}
		}
		
		return $ret;
	}
	
	
	/** Performs a synchronization between the engineer records in ERP and Keyos. Note this
	* means only synchrnoizing travel and service codes. New users are NOT automatically created.
	* This function also performs the updating of the special action types for users travel costs.
	*/
	function sync_erp_engineers ()
	{
		if ($this->db_connected)
		{
			// Get the list of ERP engineers
			$erp_engineers = $this->get_erp_engineers (false);
			
			// Get the list of ERP IDs already defined in Keyos
			$q = 'SELECT erp_id, id FROM '.TBL_USERS.' WHERE erp_id<>"" ';
			$ks_erp_ids = $this->db_fetch_list ($q);
			
			for ($i=0; $i<count($erp_engineers); $i++)
			{
				$r = &$erp_engineers[$i];
				if ($r->sync_stat == ERP_SYNC_STAT_MODIFIED and $r->user->id)
				{
					// Synchronize the user itself
					$r->user->erp_id_service = $r->erp_id_service;
					$r->user->erp_id_travel = $r->erp_id_travel;
					$r->user->save_data ();
					
					// Synchronize the action type for travel cost
					if ($r->actype_travel->id)
					{
						// The action type exist, only update it
						$r->actype_travel->erp_id = $r->erp_id_travel;
						$r->actype_travel->name = 'Travel costs - '.$r->user->get_name ();
						$r->actype_travel->erp_name = 'Travel costs - '.$r->user->get_name ();
						$r->actype_travel->save_data ();
					}
					else
					{
						// The action type doesn't exist, create it
						$r->actype_travel = new ActionType ();
						$r->actype_travel->erp_id = $r->erp_id_travel;
						$r->actype_travel->name = 'Travel costs - '.$r->user->get_name ();
						$r->actype_travel->erp_name = 'Travel costs - '.$r->user->get_name ();
						$r->actype_travel->price_type = PRICE_TYPE_FIXED;
						$r->actype_travel->special_type = ACTYPE_SPECIAL_TRAVEL;
						$r->actype_travel->user_id = $r->user->id;
						$r->actype_travel->billable = true;
						$r->actype_travel->active = true;
						$r->actype_travel->save_data ();
					}
				}
			}
			
			// Check for the existence of the generic action type for engineers travel costs
			// XXXX @TODO: Make better synchronization for this
			$q = 'SELECT id FROM '.TBL_ACTION_TYPES.' WHERE erp_id="'.ERP_TRAVEL_ID.'"';
			$travel_id = $this->db_fetch_field ($q, 'id');
			if (!$travel_id)
			{
				$actype_travel = new ActionType ();
				$actype_travel->erp_id = ERP_TRAVEL_ID;
				$actype_travel->erp_code = ERP_TRAVEL_CODE;
				$actype_travel->name = ERP_TRAVEL_NAME;
				$actype_travel->erp_name = ERP_TRAVEL_NAME;
				$actype_travel->price_type = PRICE_TYPE_FIXED;
				$actype_travel->billable = true;
				$actype_travel->active = true;
				$r->actype_travel->save_data ();
			}
		}
	}
}
?>