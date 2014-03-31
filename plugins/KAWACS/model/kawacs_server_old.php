<?php 

class_load('nusoap');

/**
* SOAP Server for communication with KAWACS Agents.
* 
* @package
* @subpackage KAWACS_SOAP_SERVER
*/
class KawacsServer extends soap_server
{

	/**
	* Constructor. Initializes the SOAP settings and registers the needed methods and data types
	*/
	function KawacsServer ()
	{
		$this->configureWSDL('kawacsBinding', 'urn:kawacs');
		$this->wsdl->schemaTargetNamespace = 'urn:kawacs';
		
		/************************************************************************/
		/* Define the types needed for communication with the client		*/
		/************************************************************************/
		
		/**
		* Simple list for passing an array of strings
		*/
		$this->wsdl->addComplexType('StringsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string')),
			'xsd:string'
		);
		
		
		/**
		* Defines a type for identifying a computer
		*
		* The MAC address represents the main identification method. If the MAC address changes, the agent should use the last known MAC address.
		*/
		$this->wsdl->addComplexType('Computer',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'mac_address' =>  array ('name' => 'mac_address', 'type' => 'xsd:string'),
				'customer_id' =>  array ('name' => 'customer_id', 'type' => 'xsd:int'),
				'new_mac_address' => array ('name' => 'new_mac_address', 'type' => 'xsd:string'),
				'version_agent' => array ('name' => 'version_agent', 'type' => 'xsd:string'),
				'version_library' => array ('name' => 'version_library', 'type' => 'xsd:string'),
				'version_kawacs' => array ('name' => 'version_kawacs', 'type' => 'xsd:string'),
				'version_manager' => array ('name' => 'version_manager', 'type' => 'xsd:string'),
				'version_zipdll' => array ('name' => 'version_zipdll', 'type' => 'xsd:string'),
				'version_linux_agent' => array ('name' => 'version_linux_agent', 'type' => 'xsd:string'), // Used for reporting version by the Linux Agent
				'request_full_update' => array ('name' => 'request_full_update', 'type' => 'xsd:string'), // When set to 'yes', it will request an update of all computer's monitoring items
				'profile_id' => array ('name' => 'profile_id', 'type' => 'xsd:int'), // Is considered only if the computer doesn't have a profile already
				'type_id' => array ('name' => 'type_id', 'type' => 'xsd:int') // Is considered only if the computer doesn't have a type
			)
		);
		
		/**
		* Structure used for reporting collected data about a single monitoring item.
		* If the item is not of type struct, field_names will contain an empty element,
		* while field_values will contain a single element, with the items' value
		*/
		$this->wsdl->addComplexType('MonitorItemValue',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'field_names' =>  array('name' => 'field_names', 'type' => 'tns:StringsList'),
				'field_values' => array('name' => 'field_values', 'type' => 'tns:StringsList')
			)
		);
		
		/**
		* Simple list for storing an array of MonitorItemValue elements
		*/
		$this->wsdl->addComplexType('MonitorItemsValues',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:MonitorItemValue')),
			'tns:MonitorItemValue'
		);
		
		
		/**
		* Structure used for reporting collected data about a monitoring item.
		* If it is of type 'single', the value array will have just one element
		*/
		$this->wsdl->addComplexType('MonitorItem',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'id' =>  array('name' => 'id', 'type' => 'xsd:int'),
				'value' => array('name' => 'field_values', 'type' => 'tns:MonitorItemsValues')
			)
		);
		
		/**
		* Simple list for storing an array of MonitorItem
		*/
		$this->wsdl->addComplexType('MonitorItemsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:MonitorItem')),
			'tns:MonitorItem'
		);
		
		
		/**
		* Defines a type for instructing the agent what files to download 
		*/
		$this->wsdl->addComplexType('DownloadFile',
			'complexType',
			'struct', 
			'all', 
			'',
			array(
				'zip_name' => array('name' => 'zip_name', 'type' => 'xsd:string'),
				'file_name' => array('name' => 'file_name', 'type' => 'xsd:string'),
				'url' => array('name' => 'url', 'type' => 'xsd:string'),
				'md5_checksum' => array('name' => 'md5_checksum', 'type' => 'xsd:string')
			)
		);
		
		/** Defines a list for sending download urls */
		$this->wsdl->addComplexType('DownloadFilesList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:DownloadFile[]')),
			'tns:DownloadFile'
		);
		
		/**
		* Defines a type for instructing the agent what event log items to send back
		*/
		$this->wsdl->addComplexType('RequestEventSource',
			'complexType',
			'struct', 
			'all', 
			'',
			array(
				'category' => array('name' => 'category', 'type' => 'xsd:int'),
				'event_source' => array('name' => 'event_source', 'type' => 'xsd:string'),
				'report_level' => array('name' => 'report_level', 'type' => 'xsd:int')
			)
		);
		
		/** Defines a list for sending definitions of requested events log items */
		$this->wsdl->addComplexType('RequestEventSourcesList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:RequestEventSource[]')),
			'tns:RequestEventSource'
		);
		
		
		
		/** Defines a type for a field ID - snmp OID pair */
		$this->wsdl->addComplexType('FieldOid',
			'complexType',
			'struct', 
			'all', 
			'',
			array(
				'field_id' => array('name' => 'field_id', 'type' => 'xsd:int'),
				'field_name' => array('name' => 'field_name', 'type' => 'xsd:string'),
				'field_oid' => array('name' => 'field_oid', 'type' => 'xsd:string')
			)
		);
		
		/** Defines a list for sending a list of field IDs - snmp OID pairs */
		$this->wsdl->addComplexType('FieldsOidsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:FieldOid[]')),
			'tns:FieldOid'
		);
		
		
		/** Defines a type for sending details of requested SNMP items */
		$this->wsdl->addComplexType('RequestedItemSnmp',
			'complexType',
			'struct', 
			'all', 
			'',
			array(
				'item_id' => array('name' => 'item_id', 'type' => 'xsd:int'),
				'is_self' => array('name' => 'is_self', 'type' => 'xsd:int'),
				'ip_address' => array('name' => 'ip_address', 'type' => 'xsd:string'),
				'obj_class' => array('name' => 'obj_class', 'type' => 'xsd:int'),
				'obj_id' => array('name' => 'obj_id', 'type' => 'xsd:int'),
				'is_struct' => array('name' => 'is_struct', 'type' => 'xsd:int'),
				'is_multi' => array('name' => 'is_multi', 'type' => 'xsd:int'),
				'oid_top' => array('name' => 'oid_top', 'type' => 'xsd:string'),
				'oid_fields' => array('name' => 'oid_fields', 'type' => 'tns:FieldsOidsList')
			)
		);
		
		/** Defines a list for sending SNMP items requests */
		$this->wsdl->addComplexType('RequestedItemsSnmpList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:RequestedItemSnmp[]')),
			'tns:RequestedItemSnmp'
		);
		
		
		/** Defines a type for sending a request for network discovery on a given IP range */
		$this->wsdl->addComplexType('RequestedDiscoveryIPRange',
			'complexType',
			'struct', 
			'all', 
			'',
			array(
				'item_id' => array('name' => 'item_id', 'type' => 'xsd:int'),
				'detail_id' => array('name' => 'detail_id', 'type' => 'xsd:int'),
				'ip_start' => array('name' => 'ip_start', 'type' => 'xsd:string'),
				'ip_end' => array('name' => 'ip_end', 'type' => 'xsd:string'),
				'use_snmp' => array('name' => 'use_snmp', 'type' => 'xsd:boolean'),
				'use_wmi' => array('name' => 'use_wmi', 'type' => 'xsd:boolean'),
				'wmi_login' => array('name' => 'wmi_login', 'type' => 'xsd:string'),
				'wmi_password' => array('name' => 'wmi_password', 'type' => 'xsd:string'),
				'max_threads' => array('name' => 'max_threads', 'type' => 'xsd:int'),
				'default_timeout' => array('name' => 'default_timeout', 'type' => 'xsd:int'),
				'batch_timeout' => array('name' => 'batch_timeout', 'type' => 'xsd:int')
			)
		);
		
		/** Defines a list for sending a list of network discovery requests  */
		$this->wsdl->addComplexType('RequestedDiscoveryIPRangesList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:RequestedDiscoveryIPRange[]')),
			'tns:RequestedDiscoveryIPRange'
		);
		
		
		/** 
		* Defines a type for sending commands and lists of requested items to the Agent
		* The commands are taken from $GLOBALS['kawacs_commands']
		*/
		$this->wsdl->addComplexType('UpdateResponse',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'commands' => array('name' => 'commands', 'type' => 'xsd:int'),
				'requested_items' => array('name' => 'requested_items', 'type' => 'tns:StringsList'),
				'requested_items_snmp' => array('name' => 'requested_items_snmp', 'type' => 'tns:RequestedItemsSnmpList'),
				'requested_discoveries' => array('name' => 'requested_discoveries', 'type' => 'tns:RequestedDiscoveryIPRangesList'),
				'report_interval' => array('name' => 'report_interval', 'type' => 'xsd:float'),
				'download' => array('name' => 'download', 'type' => 'tns:DownloadFilesList'),
				'remote_ip' => array('name' => 'remote_ip', 'type' => 'xsd:string'),
				'computer_id' => array('name' => 'computer_id', 'type' => 'xsd:string'),
				'reported_mac' => array('name' => 'reported_mac', 'type' => 'xsd:string'),
				'requested_events' => array('name' => 'requested_events', 'type' => 'tns:RequestEventSourcesList'),
				'needs_full_update' => array('name' => 'needs_full_update', 'type' => 'xsd:boolean')
			)
		);
		
		
		
		/************************************************************************/
		/* Register the communication methods					*/
		/************************************************************************/
		
		$this->register('getNeededInfo',   			// method name
			array('computer' => 'tns:Computer'),		// input parameters
			array('report_items' => 'tns:UpdateResponse'),	// output parameters
			'urn:kawacs',					// namespace
			'urn:kawacs#getNeededInfo',			// soapaction
			'rpc',						// style
			'encoded',					// use
			'Tells the agent what information it needs to report to the server' // documentation
		);
		
		$this->register('sendComputerData',
			array('computer' => 'tns:Computer', 'data' => 'tns:MonitorItemsList'),
			array('dataok' => 'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#sendComputerData',
			'rpc',
			'encoded',
			'Used by the agent to send the collected information'
		);
		
		$this->register('getRemoteIP',
			array(),
			array('remote_ip' => 'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#getRemoteIP',
			'rpc',
			'encoded',
			'Used by the agent to determine its external public IP address'
		);
		
		$this->register('sendQuickReport',
			array(
				'computerID' => 'xsd:string', 'userName' => 'xsd:string', 
				'computerName' => 'xsd:string', 'computerManufacturer' => 'xsd:string', 'computerModel' => 'xsd:string', 'computerSN' => 'xsd:string',
				'localIP' => 'xsd:string', 'gatewayIP' => 'xsd:string', 'remoteIP' => 'xsd:string', 'macAddress' => 'xsd:string'
			),
			array('response' => 'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#sendQuickReport',
			'rpc',
			'encoded',
			'Used by the agent to send quick information about the computer, to help in identifying for tech support.'
		);
		
		
		$this->register('testSoapConnection',
			array ('received' => 'xsd:string'),
			array ('response' => 'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#testSoapConnection',
			'rpc',
			'encoded',
			'Simple procedure for testing SOAP connection. Returns the received string'
		);
		
	}
}


/************************************************************************/
/* The definitions for the SOAP methods					*/
/************************************************************************/


/**
* Tells the agent what information it needs to report to the server 
*/
function getNeededInfo ($computer)
{
	class_load('MonitorItem');
	class_load('MonitorProfile');
	class_load('Computer');
	class_load('Customer');
	
	//XXXX debug: see what is transmitted in $computer in the first place
	$test_file = dirname(__FILE__).'/../../logs/testlog_getNeededInfo';
	$fp = @fopen($test_file, 'a');
	if($fp){
	    ob_start();
	    echo 'Computer_xxx: '; print_r($computer);
	    fwrite($fp, ob_get_contents());
	    ob_end_clean();
	    fclose($fp);
	}
	
	// Determine the computer by MAC address. 
	// Note that since Agent v.0.9.1.0, if a computer has multiple MACs, they all are reported in a comma-separated string
	$all_macs = preg_split('/\,\s*/', $computer['mac_address']);
	$first_mac = '';
	$matched_mac = '';
	foreach ($all_macs as $mac_address)
	{
		if (!$matched_mac and !is_bogus_mac($mac_address))
		{
			if (!$first_mac) $first_mac = $mac_address;
			$comp = Computer::get_by_mac ($mac_address);
			if ($comp->id) $matched_mac = $mac_address;
		}
	}
	
	// If a match was not found, check if the Agent reported a "new" MAC which exists already
	$old_first_mac = $first_mac;
	if (!$comp->id)
	{
		$all_macs = preg_split('/\,\s*/', $computer['new_mac_address']);
		$first_mac = '';
		$matched_mac = '';
		foreach ($all_macs as $mac_address)
		{
			if (!$matched_mac and !is_bogus_mac($mac_address))
			{
				if (!$first_mac) $first_mac = $mac_address;
				$comp = Computer::get_by_mac ($mac_address);
				if ($comp->id) $matched_mac = $mac_address;
			}
		}
	}
	if (!$first_mac) $first_mac = $old_first_mac;
	
	$requested_items = array();
	$requested_items_snmp = array ();
	$requested_discoveries = array ();
	$requested_events = array ();
	$report_interval = -1;
	$needs_full_update = false;
	
	// Check if an auto-update is needed. Only registered computers are required to make the download
	if ($comp->id)
	{
		if (!isset($computer['version_linux_agent']))
		{
			// Windows Agent. Auto-update will be allowed only for computers with version higher than 0.8.1.4
			if ($computer['version_agent'] > '0.8.1.4')
			{
				if (!$computer['version_library']) $computer['version_library'] = '0.8.1.5';
				$downloads = $comp->check_update_needed ($computer);
			}
		}
		else $downloads = $comp->check_update_needed ($computer); // Linux Agent
	}
	
	if ($computer['version_linux_agent']) $log_version = 'Version (Linux): '. $computer['version_linux_agent'];
	else $log_version = 'Versions: Agent '.$computer['version_agent'].', Library '.$computer['version_library'].', Kawacs '.$computer['version_kawacs'].', Manager '.$computer['version_manager'];
	do_log ('Contact from #'.$comp->id.': '.$computer['mac_address'].', customer: '.$computer['customer_id'].'; '.$log_version, LOG_LEVEL_TRACE);
	
	if (empty ($downloads))
	{
		// No auto-update is needed for this computer, proceed with normal reporting
		if ($comp->id)
		{
			// This is a known computer. Signal contact has been made.
			$comp->contact_made($_SERVER['REMOTE_ADDR']);
			$need_save_computer = false;
			$needs_full_update = ($computer['request_full_update'] or $comp->request_full_update);
			
			// Clear the status of "full update pending" for the computer if full update is requested by server or agent
			if ($needs_full_update) {$comp->request_full_update = false; $need_save_computer = true;}
			
			// Fetch the items that the computer needs to report
			$requested_items = $comp->get_needed_items ($needs_full_update); // Will return items for the default profile if computer has no profile
			$requested_items_snmp = $comp->get_needed_items_snmp ($needs_full_update);
			$requested_discoveries = $comp->get_needed_discoveries ();
			
			if ($comp->profile_id and in_array(EVENTS_ITEM_ID, $requested_items))
			{
				// This computer needs to report its events log, get the criteria for the events it needs to report
				$requested_events = $comp->get_needed_events_report ();
			}
			
			// Check if a specific type has been requested by the computer
			if ($computer['type_id'] and !$comp->type) {$comp->type = $computer['type_id']; $need_save_data = true;}
			
			$report_interval = DEFAULT_MONITOR_INTERVAL;
			if ($comp->profile_id) $report_interval=DB::db_fetch_field ('SELECT report_interval FROM '.TBL_MONITOR_PROFILES.' WHERE id='.$comp->profile_id, 'report_interval');
			
			if ($need_save_computer) $comp->save_data ();
		}
		else
		{
			// This is a new computer that needs to be registered.
			// Check if the remote matches exactly one customer, and if it does then assign the computer to that customer
			// If not, then use whatever customer ID was reported by Kawacs Agent.
			/*
			//Save this part until the network descovery thing will be working again
			
			class_load ('CustomerAllowedIP');
			$customer_id = CustomerAllowedIP::get_customer_for_ip($_SERVER['REMOTE_ADDR']);
			if ($customer_id) $customer = new Customer ($customer_id);
			else
			*/ 
			$customer = new Customer($computer['customer_id']);
			
			// Check if a specific profile was requested by the computer and make sure the profile exists
			if ($computer['profile_id'])
			{
				$profile = new MonitorProfile ($computer['profile_id']);
				if (!$profile->id) $profile = null;
			}

			// Check if a specific type has been requested by the computer
			if ($computer['type_id'] and !$comp->type) $comp->type = $computer['type_id'];
			
			if ($customer->id)
			{
				$comp->customer_id = $customer->id;
				$comp->mac_address = $first_mac; //$computer['mac_address'];
				$comp->remote_ip = $_SERVER['REMOTE_ADDR'];
				if ($profile->id) $comp->profile_id = $profile->id;
				
				$comp->save_data();
				
				// If a type was specified, then also assign it some default remote services
				if ($comp->type)
				{
					class_load ('ComputerRemoteService');
					if ($comp->type==COMP_TYPE_SERVER or $comp->type==COMP_TYPE_WORKSTATION)
					{
						$remote_service = new ComputerRemoteService ();
						$remote_service->computer_id = $comp->id;
						$remote_service->service_id = REMOTE_SERVICE_TYPE_VNC;
						$remote_service->port = $GLOBALS['REMOTE_SERVICES_PORTS'][$remote_service->service_id];
						$remote_service->save_data ();
					}
					if ($comp->type==COMP_TYPE_SERVER)
					{
						$remote_service = new ComputerRemoteService ();
						$remote_service->computer_id = $comp->id;
						$remote_service->service_id = REMOTE_SERVICE_TYPE_TERMINALSRV;
						$remote_service->port = $GLOBALS['REMOTE_SERVICES_PORTS'][$remote_service->service_id];
						$remote_service->save_data ();
					}
				}
			}
			
			// Newly registered computers are instructed to use a default report interval, if no profile was requested
			if (!$profile->id)
			{
				$profile = MonitorProfile::get_default_profile();
				$report_interval = DEFAULT_MONITOR_INTERVAL;
			}
			else $report_interval = $profile->report_interval;
			$requested_items = array_keys($profile->items);
		}
	}
	
	$reported_mac = '';
	if ($computer['new_mac_address'] and $comp->id)
	{
		// The MAC address of the computer has changed, so update it
		// Store in the computer the first MAC address (in case there were many),
		// but send back to the agent the entire list as confirmation.
		$first_mac_new = '';
		$all_new_macs = preg_split('/\,\s*/', $computer['new_mac_address']);
		for ($i=0; ($i<count($all_new_macs) and !$first_mac_new); $i++)
		{
			if (!is_bogus_mac($all_new_macs[$i])) $first_mac_new = $all_new_macs[$i];
		}
		if ($first_mac_new)
		{
			// Save to computer and confirm change to the Agent only if a valid MAC was received
			$comp->mac_address = $first_mac_new; //$computer['new_mac_address'];
			$comp->save_data ();
			$reported_mac = $computer['new_mac_address']; //$comp->mac_address;
		}
	}
	elseif ($comp->id and $comp->mac_address != $first_mac and $first_mac)
	{
		// Make sure the first mac address from the list is the one we store for the computer
		$comp->mac_address = $first_mac;
		$comp->save_data ();
	}
	
	$ret = array(
		'commands' => 1,
		'requested_items' =>  $requested_items,
		'requested_items_snmp' => $requested_items_snmp,
		'requested_discoveries' => $requested_discoveries,
		'report_interval' => $report_interval,
		'download' => $downloads,
		'remote_ip' => $_SERVER['REMOTE_ADDR'],
		'computer_id' => ($comp->id ? $comp->id : ''),
		'reported_mac' => $reported_mac,
		'requested_events' => $requested_events,
		'needs_full_update' => $needs_full_update
	);
	
	
	//if (LOG_LEVEL >= LOG_LEVEL_TRACE)
	//{
	//	$test_file = dirname(__FILE__).'/../../logs/testlog_getNeededInfo';
	//	$fp = @fopen ($test_file, 'a');
	//	if ($fp)
	//	{
	//		fwrite($fp, "\n======= ".$_SERVER['REMOTE_ADDR'].($_SERVER['HTTPS']=='on'?' HTTPS':' HTTP').': '.date('Y-m-d H:i:s')." ======================\nReceived Data:\n\n");
	//		ob_start();
	//		
	//		print_r($downloads);
	//		
	//		echo "Computer: "; print_r($computer);
	//		fwrite($fp, ob_get_contents());
	//		ob_end_clean();
	//		fclose($fp);
	//	}
	//}
	
	
	$total_elapsed = round((microtime_float() - $GLOBALS['start_time']), 4);
	do_log ('End from #'.$comp->id.': '.$total_elapsed, LOG_LEVEL_TRACE);
	return $ret;
}


/**
* Used by the agent to send the monitoring data which was requested by the server 
*/
function sendComputerData ($computer, $data)
{
	$ret = 'OK';
	class_load ('Computer');
	
	// Determine the computer by MAC address. Note that since Agent v.0.9.1.0, all
	// MACs are reported for a computer in a string, separated by commas
	//$comp = Computer::get_by_mac ($computer['mac_address']);
	$all_macs = preg_split('/\,\s*/', $computer['mac_address']);
	$matched_mac = '';
	foreach ($all_macs as $mac_address)
	{
		if (!$matched_mac and !is_bogus_mac($mac_address))
		{
			$comp = Computer::get_by_mac ($mac_address);
			if ($comp->id) $matched_mac = $mac_address;
		}
	}
	do_log ('Got data from #'.$comp->id.': '.$computer['mac_address'].', customer: '.$computer['customer_id'], LOG_LEVEL_TRACE);
	
	if (LOG_LEVEL >= LOG_LEVEL_TRACE)
	{
		$test_file = dirname(__FILE__).'/../../logs/testlog_sendComputerData';
		$fp = fopen ($test_file, 'w');	
		fwrite($fp, "\n======= $comp->id ".$_SERVER['REMOTE_ADDR'].($_SERVER['HTTPS']=='on'?' HTTPS':' HTTP').': '.date('Y-m-d H:i:s')." ======================\nReceived Data:\n\n");

		ob_start();
		echo "Computer: "; print_r($computer);
		echo "Data: ";print_r($data);
		fwrite($fp, ob_get_contents());
		ob_end_clean();

		fclose($fp);
	}
	
	if ($matched_mac and $comp->id)
	{
		// This is a known computer
		$comp->add_reported_items ($data);
	}
	
	$total_elapsed = round((microtime_float() - $GLOBALS['start_time']), 4);
	do_log ('End got data from #'.$comp->id.': '.$total_elapsed.'; Response: '.$ret, LOG_LEVEL_TRACE);
	
	return $ret;
}



/**
* Function to be used by Kawacs Agents to retrieve the remote IP address
*/
function getRemoteIP ()
{
	return $_SERVER['REMOTE_ADDR'];
}


/**
* Function to be used by Kawacs Agent to quickly send computer identification data,
* to help in tech support
*/
function sendQuickReport ($computerID, $userName, $computerName, $computerManufacturer, $computerModel, $computerSN, $localIP, $gatewayIP, $remoteIP, $macAddress)
{
	$ret = 'Failed';
	
	class_load ('ComputerQuickContact');
	
	$contact = new ComputerQuickContact();
	$contact->computer_id = $computerID;
	$contact->user_name = $userName;
	$contact->computer_name = $computerName;
	$contact->computer_manufacturer = $computerManufacturer;
	$contact->computer_model = $computerModel;
	$contact->computer_sn = $computerSN;
	$contact->net_local_ip = $localIP;
	$contact->net_gateway_ip = $gatewayIP;
	$contact->net_remote_ip = $remoteIP;
	$contact->net_mac_address = $macAddress;
	
	$contact->save_data ();
	if ($contact->id) $ret = 'OK';
	
	
	return $ret;
}


/**
* Simple procedure for testing SOAP connection. Returns the received string
*/
function testSoapConnection ($received = '')
{
	return $received;
}

?>