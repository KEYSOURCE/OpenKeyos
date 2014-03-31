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
		$this->configureWSDL('kawacs', 'urn:kawacs');
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
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
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
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:MonitorItemValue[]')),
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
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:MonitorItem[]')),
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
		
		/**
		 * Define types for working with krifs tickets
		 * */
		
		/** type defining a ticket attachment */
		$this->wsdl->addComplexType('TicketAttachment',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'displayname' => array('name'=>'displayname', 'type'=>'xsd:string'),
				'filename' => array('name'=>'filename', 'type'=>'xsd:string'),
				'inputData' => array('name' => 'inputData', 'type'=>'xsd:base64Binary'),
				'inputLength' => array('name'=>'inputLength', 'type'=>'xsd:int')
			)
		);
		
		/** type defining a list of attchments */
		$this->wsdl->addComplexType('AttachmentsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:TicketAttachment[]')),
			'tns:TicketAttachment'
		);
		
		/** type for identyfying a Ticket */
		$this->wsdl->addComplexType('Ticket',
			'complexType',
			'struct',
			'all',
			'',
			array(
					'ticket_id' => array('name'=>'ticket_id', 'type'=>'xsd:int'),
					'customer_id' => array('name'=>'customer_id', 'type'=>'xsd:int'),
					'customer_name' => array('name' => 'customer_name', 'type'=>'xsd:string'),
					'user_id' => array('name'=>'user_id', 'type'=>'xsd:int'),
					'user_name' => array('name' => 'user_name', 'type'=>'xsd:string'),
					'subject' => array('name'=>'subject', 'type'=>'xsd:string'),
					'status' => array('name'=>'status', 'type'=>'xsd:int'),
					'status_name' => array('name'=> 'status_name', 'type'=>'xsd:string'),
					'source' => array('name'=>'source', 'type'=>'xsd:int'),
					'type' => array('name'=>'type', 'type'=>'xsd:int'),
					'private' => array('name'=>'private', 'type'=>'xsd:int'),
					'billable' => array('name'=>'billable', 'type'=>'xsd:int'),
					'comment' => array('name'=>'comment', 'type'=>'xsd:string'),
					'attachments' => array('name'=>'attachments', 'type'=>'tns:AttachmentsList')
				)
		);
		
		$this->wsdl->addComplexType('TicketsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:Ticket[]')),
			'tns:Ticket'
		);
		
		/** type for identyfing a TicketDetail */
		$this->wsdl->addComplexType('TicketDetail',
			'complexType',
			'struct',
			'all',
			'',
			array(
					'created' => array('name' => 'created', 'type'=>'xsd:int'),
					'comments' => array('name' => 'comments', 'type'=>'xsd:string'),
					'assigned' => array('name' => 'assigned', 'type'=>'xsd:string'),
					'status' => array('name' => 'status', 'type'=>'xsd:string'),
					'priv' => array('name' => 'priv', 'type'=>'xsd:int')
				)
		);
		
		$this->wsdl->addComplexType('TicketDetailList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:TicketDetail[]')),
			'tns:TicketDetail'
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
		
		$this->register('sendUserCreationRequest',
			array(
				'customer_id' => 'xsd:int',
				'username' => 'xsd:string', 'password'=>'xsd:string',
				'first_name' => 'xsd:string', 'last_name' => 'xsd:string',
				'email' => 'xsd:string', 'language'=>'xsd:int'
			),
			array('response'=>'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#sendUserCreationRequest',
			'rpc',
			'encoded',
			'accepts users creation requests from the kawacs agent'
		);		
		
		$this->register('sendCustomerCreationRequest',			
			array(				
				'name' => 'xsd:string',				
				'has_kawacs' => 'xsd:int',				
				'has_krifs' => 'xsd:int',				
				'sla_hours' => 'xsd:int'			
			),			
			array('response'=>'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#sendCustomerCreationRequest',
			'rpc',
			'encoded',
			'accepts and handles customer creation requests'
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
		$this->register('sendLoginRequest',
		        array (
                                'username' => 'xsd:string',
                                'password' => 'xsd:string',
                                'customer_id' => 'xsd:int'
		        ),
		        array ('response' => 'xsd:string'),
		        'urn:kawacs',
		        'urn:kawacs#sendLoginRequest',
			'rpc',
			'encoded',
			'Sends a user login request, the response is a UserID or failed message'
		);
		$this->register('getActiveTickets',
			array (
				'username' => 'xsd:string',
				'user_id'  => 'xsd:string',
				'customer_id' => 'xsd:string'
			),
			array('response' => 'tns:TicketsList'),
			'urn:kawacs',
			'urn:kawacs#getActiveTickets',
			'rpc',
			'encoded',
			'Sends a list list with the active tickets for the customer to the agent, also checks if the user is OK'			
		);
		$this->register('getTicketDetails',
			array (
				'ticket_id' => 'xsd:string',
				'username' => 'xsd:string',
				'user_id'  => 'xsd:string',
				'customer_id' => 'xsd:string'
			),
			array('response' => 'tns:TicketDetailList'),
			'urn:kawacs',
			'urn:kawacs#getTicketDetails',
			'rpc',
			'encoded',
			'Sends a list with all the details for a ticket to the agent, also checks if the user is OK'			
		);
		$this->register('addKrifsTicket',
			array(
				'user_id' 	=> 'xsd:string',
				'username' 	=> 'xsd:string',
				'customer_id' 	=> 'xsd:string',
				'ticket'	=> 'tns:Ticket'
			),
			array('response' => 'xsd:string'),
			'urn:kawacs',
			'urn:kawacs#addKrifsTicket',
			'rpc',
			'encoded',
			'Adds a new ticket to keyos or a ticket detail to an existing ticket'
		);

                $this->register('getCurrentAgentRelease',
                        array('computer_id' => 'xsd:int'),
                        array('download_list' => 'tns:DownloadFilesList'),
                        'urn:kawacs',
                        'urn:kawacs#getCurrentAgentRelease',
                        'rpc',
                        'encoded',
                        'gets the list of the files to download in order to update the agent'
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
	

         //$comp = new Computer();
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
			if ($comp->id){
                           // $comp = $compx;
                            $matched_mac = $mac_address;
                            $comp->check_stolen();
                        }
                        //if($compx) $compx=null;
		}
	}
        //if($all_macs) $all_macs = null;
	
	// If a match was not found, check if the Agent reported a "new" MAC which exists already
	$old_first_mac = $first_mac;

	if (!$comp->id){
		$all_macs = preg_split('/\,\s*/', $computer['new_mac_address']);
		$first_mac = '';
		$matched_mac = '';
		foreach ($all_macs as $mac_address)
		{
			if (!$matched_mac and !is_bogus_mac($mac_address))
			{
				if (!$first_mac) $first_mac = $mac_address;
				$comp = Computer::get_by_mac ($mac_address);
				if ($comp->id){
                                    //$comp = $compx;
                                    $matched_mac = $mac_address;
				    $comp->check_stolen();
                                }
                                //if($compx) $compx = null;
			}
		}
                //if($all_macs) $all_macs = null;
	}
	if (!$first_mac) $first_mac = $old_first_mac;
	
	$requested_items = array();
	$requested_items_snmp = array ();
	$requested_discoveries = array ();
	$requested_events = array ();
	$report_interval = -1;
	$needs_full_update = false;
	
	// Check if an auto-update is needed. Only registered computers are required to make the download
	if ($comp->id){
		$comp->check_stolen();
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
			//Save this for the time that discovery actually works
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
                      //$comp = new Computer();
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
                if($all_new_macs) $all_new_macs = null;
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
	

	/*
	if (LOG_LEVEL >= LOG_LEVEL_TRACE)
	{

		$test_file = dirname(__FILE__).'/../../logs/testlog_getNeededInfo';
		$fp = @fopen ($test_file, 'w');
		if ($fp)
		{
			fwrite($fp, "\n======= ".$_SERVER['REMOTE_ADDR'].($_SERVER['HTTPS']=='on'?' HTTPS':' HTTP').': '.date('Y-m-d H:i:s')." ======================\nReceived Data:\n\n");
			ob_start();
			
			print_r($downloads);
			
			echo "Computer: "; print_r($computer);
			fwrite($fp, ob_get_contents());
			ob_end_clean();
			fclose($fp);
		}
	}
         */

       
        if($requested_items) $requested_items = null;
        if($requested_items_snmp) $requested_items_snmp = null;
        if($requested_discoveries) $requested_discoveries = null;
        if($requested_events) $requested_events = null;

        if($downloads) $downloads = null;
	
	$total_elapsed = round((microtime_float() - $GLOBALS['start_time']), 4);
	do_log ('End from #'.$comp->id.': '.$total_elapsed, LOG_LEVEL_TRACE);
	 
        if($comp) $comp = null;
        //lastly destroy the input computer array... we don't need it anymore once the return is formed
        if($computer) $computer=null;

	return $ret;
}


/**
* Used by the agent to send the monitoring data which was requested by the server 
*/
function sendComputerData ($computer, $data)
{
	$ret = 'OK';
	class_load ('Computer');

        //$comp = new Computer();
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
			if ($comp->id){
                            //$comp = $compx;
                            $matched_mac = $mac_address;
                        }
                        //if($compx) $compx = null;
		}
	}
        //if($all_macs) $all_macs = null;
	do_log ('Got data from #'.$comp->id.': '.$computer['mac_address'].', customer: '.$computer['customer_id'], LOG_LEVEL_TRACE);
	
	/*
	if (false)
	{
		$test_file = dirname(__FILE__).'/../../logs/testlog_sendComputerData';
		$fp = fopen ($test_file, 'w');	
		fwrite($fp, "\n======= ".$comp->id." ".$_SERVER['REMOTE_ADDR']($_SERVER['HTTPS']=='on' ? 'HTTPS' : 'HTTP').': '.date('Y-m-d H:i:s')." ======================\nReceived Data:\n\n");

		ob_start();
		echo "Computer: "; print_r($computer);
		echo "Data: ";print_r($data);
		fwrite($fp, ob_get_contents());
		ob_end_clean();

		fclose($fp);
	}
	*/

        //we prepare to add the reported items .. the computer input array is not needed anymore
        if($computer) $computer = null;

	if ($matched_mac and $comp->id)
	{
		// This is a known computer
		$comp->add_reported_items ($data);
		$comp->check_stolen();
	}

        //the data was added ... realease the memory
        if($data) $data = null;
	
	$total_elapsed = round((microtime_float() - $GLOBALS['start_time']), 4);
	do_log ('End got data from #'.$comp->id.': '.$total_elapsed.'; Response: '.$ret, LOG_LEVEL_TRACE);
	if($comp) $comp=null;

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
       class_load ('Computer');
	
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
        
        if($contact) $contact = null;

        $comp = new Computer($computerID);
        if($comp->id)
                $comp->check_stolen();
	if($comp) $comp = null;

	return $ret;
}

function sendUserCreationRequest($customer_id, $username, $password, $first_name, $last_name, $email, $language)
{
    class_load('User');	
    $userdata = array(
	'login' => $username,
	'password' => $password,
	'password_confirm' => $password,
	'fname' => $first_name,
	'lname' => $last_name,
	'email' => $email,
	'language' => $language,
	'newsletter' => 0,
	'type' => USER_TYPE_CUSTOMER,
	'customer_id' => $customer_id,
	'allow_private' => 0,
	'allow_dashboard' => 0,
	'has_kadeum' => 0,
	'send_invitation_email' => 1
    );
    $user = new User();
    $user->load_from_array($userdata);
    if($user->is_valid_data())
    {
	$user->save_data ();
	$user->load_data ();
	
	$user->roles_list = array (DEFAULT_CUSTOMER_ROLE);
	$user->save_data ();			
	
	$user->send_invitation_email ();
    }																										
    return 'OK';
}


/** 
 * Takes a request from a customer and handles the new customer creation. Returns the new customer id, the closest match or, error message 
 */
 function sendCustomerCreationRequest($name, $has_kawacs, $has_krifs, $sla_hours)
 {	
 	class_load('Customer');		
 	//first see if a customer with the same name or a similar one does not allready exist in keyos	
 	//we consider a distance of 2 between the lowercase names to be a typing error if the select has only one result	
 	$query = "select id, lower(name) as name from ".TBL_CUSTOMERS;	
 	$custs = db::db_fetch_array($query);		
 	$nm = strtolower($name);	
 	$closest_distance = -1;	
 	foreach($custs as $cust)		
 	{		
 		$lev = levenshtein($nm, $cust->name);		
 		if($lev == 0)		
 		{			
 			//ok, we have an exact match			
 			$cc = $cust;			
 			$closest_distance = 0;			
 			break; //break out of the loop, we have an exact match		
		}		
		if($lev <= $closest_distance || $closest_distance < 0)		
		{			
			$closest_distance  = $lev;			
			$cc = $cust;		
		}			
	}	
	if($closest_distance <= 3 )	
	{		
		if($closest_distance == 0)		
		{			
			//we have an exact match			
			//return the customer id			
			return $cc->id;		
		}		
		else		
		{			
			$resp = 'A customer with a very simmilar name allready exists in keyos: "'.$cust->name.'". Please use this name to use this customer or try a name a little bit more different!';
			return $resp;		
		}	
	}		
	
	$customer_data = array(		
				'name' => $name,		
				'has_kawacs' => $has_kawacs,		
				'has_krifs' => $has_krifs,		
				'sla_hours' => $sla_hours	
			);	
	$customer = new Customer();	
	$customer->load_from_array($customer_data);	
	if($customer->is_valid_data())	
	{		
		$customer->save_data();		
		$customer->load_data();		
		if($customer->id) 
			return $customer->id;		
		else 		
		{			
			$resp = 'There was an error while creating this user: '.error_msg();			
			return $resp;		
		}
	}		
	return 'Failed';
}

/**
 * tries to log on a user based on it's username, password and customer_id
 *
 * TODO:
 * as a feature -- will not be implemented right now
 * even if the username and password are valid, if the user does not belong to the provided
 * customer_id the login will fail
 **/
function sendLoginRequest($username, $password, $customer_id)
{
	class_load('Auth');
	//feature
	//class_load('User');
	$response = '';
	$auth = new Auth();
	$valid = $auth->validate_login($username, $password);
	if(!$valid)
	{
		$response = "Login failed!";
	}
	else
	{
		//feature - not used right now.
		//$user = new User($valid);
		//if($user->customer_id == $customer_id)
		//	$response = $valid;
		//else
		//	$reponse = "Customer mismatch";
		$response = $valid;
	}	
	return $response;
}




/**
 * Gets a list with all the active tickets for the customer_id and send them to the agent
 * also checks if a user is logged on before sending anything
 * */
function getActiveTickets($username, $user_id, $customer_id)
{
	class_load('Ticket');
	class_load('User');
	class_load('Customer');
	$response = array();
	//first let's check the authenticity of the User
	$user = new User($user_id);
	if(!$user->id or $user->login != $username)
	{
		//invalid data was submitted - return empty
		return $response;
	}
	//we have a valid user - let's check if it has rights for this customer
	if($user->is_customer_user())
	{
		//this is a customer user - so make shure the customer_id is the right one
		if($user->customer_id != $customer_id)
		{
			return $response; //return empty -- not the right customer
		}
	}
	else
	{
		//this one is a keysource user -- so it should be able to see tickets
	}
	
	//now the user and customer are ok -- let's get the tickets
	$tickets_filter = array(
					'customer_ids' => $customer_id,
					'status' => -1
				);
	$tcount = 0;
	$tickets = Ticket::get_tickets($tickets_filter, $tcount);
	foreach($tickets as $ticket)
	{
		$t = array(
				'ticket_id' => $ticket->id,
				'customer_id' => $ticket->customer_id,
				'customer_name' => $ticket->customer->name,
				'user_id' => $ticket->user_id,
				'user_name' => $ticket->user->fname." ".$ticket->user->lname,
				'subject' => $ticket->subject,
				'status' => $ticket->status,
				'status_name' => $GLOBALS ['TICKET_STATUSES'][$ticket->status],
				'source' => $ticket->source,
				'type' => $ticket->type,
				'private' => $ticket->private,
				'billable' => $ticket->billable,
				'comment' => "",
				'attachment' => array()
			   );
		$response[] = $t;
	}
	
	/*$test_file = dirname(__FILE__).'/../../logs/testlog_kwkr';
	$fp = fopen ($test_file, 'w');	
	ob_start();
	echo "Customer: ".$customer_id."; User ".$user_id."; username; ".$username;
	print_r($response);
	fwrite($fp, ob_get_contents());
	ob_end_clean();

	fclose($fp);
	*/
	return $response;
}

/**
 * Sends a list with all the details for the specified ticket, checks the authenticity of the user
 * */
function getTicketDetails($ticket_id, $username, $user_id, $customer_id)
{
	class_load('Ticket');
	class_load('User');
	class_load('TicketDetail');
	$response = array();
	
	//first let's check the authenticity of the User
	$user = new User($user_id);
	if(!$user->id or $user->login != $username)
	{
		//invalid data was submitted - return empty
		return $response;
	}
	//we have a valid user - let's check if it has rights for this customer
	$ticket = new Ticket($ticket_id);
	if(!$ticket->id) return $response; //invalid ticket -- return null
	$details = array();
	if($user->is_customer_user())
	{
		//this is a customer user - so make shure the customer_id is the right one
		if($user->customer_id != $customer_id)
		{
			return $response; //return empty -- not the right customer
		}
		else{
			//load only public details
			$details = $ticket->details;
			foreach($details as $detail)
			{				
				if(!$detail->private)
				{
					$d = array(
							'created' => $detail->created,
							'comments' => $detail->comments,
							'assigned' => $detail->assigned->fname." ".$detail->assigned->lname,
							'status' => $GLOBALS ['TICKET_STATUSES'][$detail->status],
							'priv' => $detail->private
					);
					$response[] = $d;
				}
			}
			
		}
	}
	else
	{
		//this one is a keysource user -- so it should be able to see ticket details
		$details = $ticket->details;
		foreach($details as $detail)
		{
			$d = array(
					'created' => $detail->created,
					'comments' => $detail->comments,
					'assigned' => $detail->assigned->fname." ".$detail->assigned->lname,
					'status' => $GLOBALS ['TICKET_STATUSES'][$detail->status],
					'priv' => $detail->private
			);
			$response[] = $d;
		}
	}
	return $response;
	
}

/**
 *Adds a new ticket to keyos krifs or adds a new comment to an existing ticket
 **/
function addKrifsTicket($user_id, $username, $customer_id, $ticket)
{
	class_load('Ticket');
	class_load('TicketDetail');
	class_load('User');
	
	$response = "nok";
	
	$user = new User($user_id);
	if(!$user->id or $user->login != $username)
	{
		//invalid data was submitted - return empty
		return $response;
	}
	if($user->is_customer_user())
	{
		//this is a customer user - so make shure the customer_id is the right one
		if($user->customer_id != $customer_id)
		{
			return $response; //return empty -- not the right customer
		}
	}
	
	//now create the ticket
	if($ticket['ticket_id'] == 0)
	{
		//create new ticket
		$ticket_data = $ticket; //copy the contents in ticket_data
		$ticket_detail_data =  array();
		$ticket_data['priavate'] = $ticket['private'];
		$ticket_detail_data['private'] = $ticket['private'];
		$ticket_data['assigned_id'] = $user_id;
		$ticket_data['user_id'] = $user_id;
		$ticket_data['owner_id'] = $user_id;
		$ticket_data['created'] = time();	
		$ticket_data['last_modified'] = time();
		$ticket_data['status'] = TICKET_STATUS_NEW;
		$ticket_data['priority'] = TICKET_PRIORITY_NORMAL;
		
		$ticket_detail_data['user_id'] = $user_id;
		$ticket_detail_data['assigned_id'] = $user_id;
		$ticket_detail_data['created'] = time();
		$ticket_detail_data['comments'] = $ticket['comment'];
		$ticket_detail_data['billable'] = $ticket['billable'];
		
		$t = new Ticket();
		$t->load_from_array($ticket_data);
		if($t->is_valid_data())
		{
			$t->save_data();
			$t->log_action($user_id, TICKET_ACCESS_CREATE);
			$t->load_data();
			$td = new TicketDetail();
			$td->load_from_array($ticket_detail_data);
			$td->ticket_id = $t->id;
			$td->status = $t->status;
			$td->save_data();
			$td->log_action($user_id, TICKET_ACCESS_DETAIL_CREATE);
			//relaod the ticket to ensure consistency
			$td->load_data();
			
			$t->load_data();
			if(is_array($ticket['attachments']) && !empty($ticket['attachments']))
			{
				class_load('TicketAttachment');
				foreach ($ticket['attachments'] as $t_att)
				{
					$binary = base64_decode($t_att['inputData']);
					$tmp_name = tempnam(KEYOS_TEMP_FILE, 'attach_');
					$fp = @fopen($tmp_name, 'wb');
					if($fp)
					{
					    //echo($binary);
					    fwrite($fp, $binary, $t_att['inputLength']);
					    fclose($fp);
					}
					
					$data = array (
						'name' =>  $t_att['filename'],
						'tmp_name' => $tmp_name,
						'ticket_id' => $t->id,
						'user_id' => $user_id
					);
				
					$attachment = new TicketAttachment ();
					$attachment->load_from_array ($data, false);
					$attachment->save_data();
				}
			}
			/*try{
				$t->dispatch_notifications(TICKET_NOTIF_TYPE_NEW, $user_id);
			}
			catch(Exception $e)
			{
				//do nothing if the previous message dispatching failed
			}*/
			$response = "Ticket created; Ticket id: ".$t->id."; Ticket detail id: ".$td->id;
		}
	}
	else 
	{
		$t = new Ticket($ticket['ticket_id']);
		if($t->id)
		{
			$ticket_detail_data = array();
			
			$ticket_detail_data['user_id'] = $user_id;
			$ticket_detail_data['assigned_id'] = $user_id;
			$ticket_detail_data['created'] = time();
			$ticket_detail_data['comments'] = $ticket['comment'];
			$ticket_detail_data['billable'] = $ticket['billable'];
			$ticket_detail_data['private'] = $ticket['private'];
			$td = new TicketDetail();
			$td->load_from_array($ticket_detail_data);
			$td->ticket_id = $t->id;
			$td->status = $t->status;
			$td->save_data();
			$td->log_action($user->id, TICKET_ACCESS_DETAIL_CREATE);
			//relaod the ticket to ensure consistency
			$td->load_data();
			$t->last_modified = time();
			$t->save_data();
			$t->load_data();
			
			if(is_array($ticket['attachments']) && !empty($ticket['attachments']))
			{
				class_load('TicketAttachment');
				foreach ($ticket['attachments'] as $t_att)
				{
					$binary = base64_decode($t_att['inputData']);
					$tmp_name = tempnam(KEYOS_TEMP_FILE, 'attach_');
					$fp = @fopen($tmp_name, 'wb');
					if($fp)
					{
					    fwrite($fp, $binary, $t_att['inputLength']);
					    fclose($fp);
					}
					
					$data = array (
						'name' =>  $t_att['filename'],
						'tmp_name' => $tmp_name,
						'ticket_id' => $t->id,
						'user_id' => $user_id
					);
				
					$attachment = new TicketAttachment ();
					$attachment->load_from_array ($data, false);
					$attachment->save_data();
				}
			}
			
			/*try{
				$t->dispatch_notifications(TICKET_NOTIF_TYPE_UPDATED, $user_id);
			}
			catch(Exception $e)
			{
				//do nothing just wait for this to pass
			}*/
			$response ="Ticket created; Ticket id: ".$t->id."; Ticket detail id: ".$td->id;
		}
	}
	
	
	
	$test_file = dirname(__FILE__).'/../../logs/testlog_kwkrAddTicket';
	$fp = fopen ($test_file, 'w');	
	ob_start();
	echo "Customer: ".$customer_id."; User ".$user_id."; username; ".$username;
	print_r($response);
	fwrite($fp, ob_get_contents());
	ob_end_clean();

	fclose($fp);
	
	return $response;
}


/**
* Simple procedure for testing SOAP connection. Returns the received string
*/
function testSoapConnection ($received = '')
{
	return $received;
}


/**
 * Gets the latest release of the agent
 */
function getCurrentAgentRelease($computer_id=null){
    class_load('KawacsAgentUpdate');
    class_load('KawacsAgentUpdateFile');
    //do_log('computer_id: '.$computer_id);
    if($computer_id == 0) $computer_id=null;
    if($_REQUEST['debug']) $computer_id=2046;
    $response = array();    
    $latest_update = KawacsAgentUpdate::get_current_release($computer_id);
    foreach($latest_update->files as $update_file){
        $download_file = array();             
        $download_file['zip_name'] = $GLOBALS['KAWACS_AGENT_FILES'][$update_file->file_id].'.zip';
        $download_file['file_name'] = $GLOBALS['KAWACS_AGENT_FILES'][$update_file->file_id];
        $download_file['url'] = $update_file->get_download_url();
        $download_file['md5_checksum'] = $update_file->md5;
        $response[] = $download_file;
    }    
    return $response;
}

?>