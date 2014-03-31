<?php

class_load('nusoap');

/**
* SOAP Server for communication with the outlook krifs agent.
* 
* @package KRIFS
* @subpackage KRIFS_SOAP_SERVER
*/
class KrifsServer extends soap_server
{
	/**
	 * [Constructor]
	 * Initializes the SOAP settings and registers the needed methods and data types
	 *
	 * @return KrifsServer
	 */
	function KrifsServer()
	{
		$this->configureWSDL('krifsBinding', 'urn:krifs');
		$this->wsdl->schemaTargetNamespace = 'urn:krifs';
		
		/**************************************************
		 * Define the types needed for communication with the client
		 ***************************************************/
		$this->wsdl->addComplexType('StringsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
			'xsd:string'
		);
		$this->wsdl->addComplexType('IntegerList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:int[]')),
			'xsd:int'
		);
		$this->wsdl->addComplexType('LogonInfo',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'username' => array('name'=>'username', 'type'=>'xsd:string'),
				'password' => array('name'=>'password', 'type'=>'xsd:string')
			)
		);
		
		$this->wsdl->addComplexType('Customer',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'customer_id' => array('name'=>'id', 'type'=>'xsd:int'),
				'customer_name' => array('name'=>'customer_name', 'type'=>'xsd:string')
			)
		);
		$this->wsdl->addComplexType('CustomersList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:Customer[]')),
			'tns:Customer'
		);
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
		
		$this->wsdl->addComplexType('AttachmentsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:TicketAttachment[]')),
			'tns:TicketAttachment'
		);
		
		/**
		 * define a type for identyfying a Ticket
		 */
		$this->wsdl->addComplexType('Ticket',
			'complexType',
			'struct',
			'all',
			'',
			array(
					'ticket_id' => array('name'=>'ticket_id', 'type'=>'xsd:int'),
					'customer_id' => array('name'=>'customer_id', 'type'=>'xsd:int'),
					'user_id' => array('name'=>'user_id', 'type'=>'xsd:int'),
					'subject' => array('name'=>'subject', 'type'=>'xsd:string'),
					'sender' => array('name'=>'sender', 'type'=>'xsd:string'),
					'recvr' => array('name'=>'recvr', 'type'=>'tns:StringsList'),
					'status' => array('name'=>'status', 'type'=>'xsd:int'),
					'source' => array('name'=>'source', 'type'=>'xsd:int'),
					'type' => array('name'=>'type', 'type'=>'xsd:int'),
					'private' => array('name'=>'private', 'type'=>'xsd:int'),
					'billable' => array('name'=>'billable', 'type'=>'xsd:int'),
					'comment' => array('name'=>'comment', 'type'=>'xsd:string'),
					'attachments' => array('name'=>'attachments', 'type'=>'tns:AttachmentsList')
				)
		);
		
		$this->register('testConnection',
			array ('user_logon' => 'tns:LogonInfo'),
			array ('response' => 'xsd:string'),
			'urn:krifs',
			'urn:krifs#testConnection',
			'rpc',
			'encoded',
			'Simple procedure for testing SOAP connection. Returns the received string'
		);
		/**
		 * register the communication methods
		 */
		$this->register('addKrifsTicket',   			// method name
			array('ticket' => 'tns:Ticket', 
			      'logon' => 'tns:LogonInfo'),			// input parameters
			array('resp' => 'xsd:string'),			// output parameters
			'urn:krifs',								// namespace
			'urn:krifs#addKrifsTicket',					// soapaction
			'rpc',										// style
			'encoded',									// use
			'adds a new ticket into keyos or a new ticket detail to an existing ticket' // documentation
		);
		$this->register('getCustomers',
			array(),
			array('customers_list' => 'tns:CustomersList'),
			'urn:krifs',
			'urn:krifs#getCustomers',
			'rpc',
			'encoded',
			'gets a list wiht the name and the id of all active users'
		);
		$this->register('checkID',
			array('ids'=>'tns:IntegerList'),
			array('ticket_id'=>'xsd:int'),
			'urn:krifs',
			'urn:krifs#checkID',
			'rpc',
			'encoded',
			'checks a list of ids and see if there is a ticket with that id'
		);
	}
}
/**
 * Simple procedure for testing SOAP connection. Returns the received string
 *
 * @param string $received
 * @return string
 */
function testConnection($user_logon)
{
	class_load('Auth');
	class_load('User');
	$response = "";
	
	$auth = new Auth();
	$valid = $auth->validate_login($user_logon['username'], $user_logon['password']);
	if(!$valid)
	{
		$response = "Failed to login in the keyos server. Check your login information!";
	}
	else {
		$response = $valid;
		$user = new User($valid);
	}
	$response .= "Name: ".$user->lname." ".$user->fname;
	
	$auth->logout();
	$test_file = dirname(__FILE__).'/../../logs/testlog_krifsTestConnection';
	$fp = @fopen($test_file, 'wb');
	if($fp){
	    ob_start();
	    echo 'Return message: '; print_r($user_logon);
	    echo "return: ".$response;
	    echo "\r\n".$valid;
	    fwrite($fp, ob_get_contents());
	    ob_end_clean();
	    fclose($fp);
	}
	return $response;
}
function addKrifsTicket($ticket, $logon)
{
	class_load('Auth');
	class_load('User');
	class_load('Ticket');
	class_load('TicketDetail');
	class_load('CustomerCCRecipient');
	$response = "";
	$auth = new Auth();
	$valid = $auth->validate_login($logon['username'], $logon['password']);
	if(!$valid)
	{
            return "Failed to login to the KeyOS server. Check your login information in the settings form!";
	}
	$response = $valid;
	$user = new User($valid);
	 
	$sndr = User::checkEmailAddress($ticket['sender']);
	$rcv = array();
	foreach ($ticket['recvr'] as $eml)
	{
		$rcv[] = User::checkEmailAddress($eml);
	}
	
	
	//here we must create the ticket
	if($ticket['ticket_id'] == 0)
	{
		$cc_users_data = CustomerCCRecipient::get_cc_recipients($ticket['customer_id']);
		//foreach($cc_users_data as $idx=>$usr) $cc_users_data[$idx]->is_customer_user = ($usr->is_customer_user == 'true' ? true : false);
		//echo "cc_users: "; print_r($cc_users_data);
		$ticket_data = $ticket;
		$ticket_data['cc_list'] = array();
		foreach ($cc_users_data as $usr) $ticket_data['cc_list'][] = $usr->id;
		$ticket_detail_data = array();
		if(!$ticket['private']) 
		{
			$ticket_data['private'] = false;
			$ticket_detail_data['private'] = false;
		}
		$ticket_data['assigned_id'] = $user->id;
		$ticket_data['user_id'] = $user->id;
		$ticket_data['owner_id'] = $user->id;
		$ticket_data['created'] = time();	
		$ticket_data['last_modified'] = time();
		$ticket_data['status'] = TICKET_STATUS_NEW;
		$ticket_data['priority'] = TICKET_PRIORITY_NORMAL;
		
		$ticket_detail_data['user_id'] = $user->id;
		$ticket_detail_data['assigned_id'] = $user->id;
		$ticket_detail_data['created'] = time();
		$ticket_detail_data['comments'] = $ticket['comment'];
		$ticket_detail_data['billable'] = $ticket['billable'];

		$t = new Ticket();
		$t->load_from_array($ticket_data);
		$t->cc_list = array();
		$t->cc_list = $ticket_data['cc_list'];
		
		if ($sndr>0) {
			array_push($t->cc_list, $sndr);
		}
		foreach ($rcv as $rr)
		{
			if($rr>0) array_push($t->cc_list, $rr);
		}
		
		$test_file = dirname(__FILE__).'/../../logs/testlog_addTicketCCUsers';
		$fp = @fopen($test_file, 'wb');
		if($fp){
		    ob_start();
		    echo 'Ticket: '; print_r($ticket);
		    echo "CC List: "; print_r($t->cc_list);
		    echo "Recvrs: "; print_r($rcv);
		    echo "Sender: "; print_r($sndr); 
		    fwrite($fp, ob_get_contents());
		    ob_end_clean();
		    fclose($fp);
		}
		
		if($t->is_valid_data())
		{		
			$t->save_data();
			$t->log_action($user->id, TICKET_ACCESS_CREATE);
			$t->load_data();
			$td = new TicketDetail();
			$td->load_from_array($ticket_detail_data);
			$td->ticket_id = $t->id;
			$td->status = $t->status;
			$td->save_data();
			$td->log_action($user->id, TICKET_ACCESS_DETAIL_CREATE);
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
						'user_id' => $user->id
					);
				
					$attachment = new TicketAttachment ();
					$attachment->load_from_array ($data, false);
					$attachment->save_data();
				}
			}
			$t->dispatch_notifications(TICKET_NOTIF_TYPE_NEW, $user->id);
			$response ="Ticket created; Ticket id: ".$t->id."; Ticket detail id: ".$td->id;
		}
		else {
			$response="Ticket not created. Reason: ".error_msg();
		}
	}
	else 
	{
		$t = new Ticket($ticket['ticket_id']);
		if($t->id)
		{
			$ticket_detail_data = array();
			
			$ticket_detail_data['user_id'] = $user->id;
			$ticket_detail_data['assigned_id'] = $user->id;
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
						'user_id' => $user->id
					);
				
					$attachment = new TicketAttachment ();
					$attachment->load_from_array ($data, false);
					$attachment->save_data();
				}
			}
			
			
			$t->dispatch_notifications(TICKET_NOTIF_TYPE_UPDATED, $user->id);
			$response ="Ticket created; Ticket id: ".$t->id."; Ticket detail id: ".$td->id;
		}
		else {
			$response = "Invalid ticket ID!";
		}
	}
		
	$auth->logout();
	return $response;
}
function getCustomers()
{
	class_load('Customer');
	$customers = Customer::get_customers_list();
	$ret = array();
	if(!sizeof($computers) != 0)
	{
		foreach ($customers as $key=>$cust)
		{
			$rr = array(
				'customer_id' => $key,
				'customer_name' => $cust
			);
			array_push($ret, $rr);
		}
	}
	else 
	{
		$ret = array('customer_id'=>0, 'customer_name'=>'no name');
	}
	/*
	$test_file = dirname(__FILE__).'/../../logs/testlog_getCustomers';
	$fp = @fopen($test_file, 'wb');
	if($fp)
	{
	   ob_start();
	    echo 'TicketData: \r\n'; print_r($ret);
	    fwrite($fp, ob_get_contents());
	    ob_end_clean();
	    fclose($fp);
	}
	*/
	return $ret;
}
function checkID($ids = array())
{
    class_load('Ticket');
    $ret = 0;
    if(sizeof($ids) > 0)
    {
    	for($i=0;$i<sizeof($ids); $i++)
    	{
    		$valid = Ticket::isValidID($ids[$i]);
    		if($valid!=0) $ret = $ids[$i];
    		return $ret;	
    	}
    }
    return $ret;	
}
?>