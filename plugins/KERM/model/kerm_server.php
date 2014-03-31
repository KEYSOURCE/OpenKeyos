<?php

class_load('nusoap');

/**
* SOAP Server for communication with the kerm agent.
* 
* @package KRIFS
* @subpackage KRIFS_SOAP_SERVER
*/
class KermServer extends soap_server
{
	/**
	 * [Constructor]
	 * Initializes the SOAP settings and registers the needed methods and data types
	 *
	 * @return KermServer
	 */
	function KermServer()
	{
		$this->configureWSDL('kermBinding', 'urn:kerm');
		$this->wsdl->schemaTargetNamespace = 'urn:kerm';
		
		/************************************************************
		 * Define de types need for the communication with the client
		 *************************************************************/
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
		$this->wsdl->addComplexType("ADGroup",
			'complexType',
			'struct',
			'all',
			'',
			array(
				'customer_id' => array('name'=>'customer_id', 'type'=>'xsd:int'),
				'name' => array('name'=>'name', 'type'=>'xsd:string'),
				'distinguishedname' => array('name'=>'distinguishedname', 'type'=>'xsd:string'),
				'description' => array('name'=>'description', 'type'=>'xsd:string')
			)
		);
		$this->wsdl->addComplexType("ADUser",
			'complexType',
			'struct',
			'all',
			'',
			array(
				'customer_id' => array('name'=>'customer_id', 'type'=>'xsd:int'),
				'status'=>array('name'=>'status', 'type'=>'xsd:int'),
				'FirstName' =>	array('name'=>'FirstName', 'type'=>'xsd:string'),
				'LastName' => array('name'=>'LastName', 'type' => 'xsd:string'),
				'MiddleInitials' => array('name'=>'MiddleInitials', 'type'=>'xsd:string'),
				'DisplayName' => array('name'=>'DisplayName', 'type'=>'xsd:string'),
				'UserPrincipalName' => array('name'=>'UserPrincipalName', 'type'=>'xsd:string'),
				'UserName' => array('name'=>'UserName', 'type'=>'xsd:string'),
				'Password' => array('name'=>'Password', 'type'=>'xsd:string'),
				'Email' => array('name'=>'Email', 'type'=>'xsd:string'),
				'GroupName' => array('name'=>'GroupName', 'type'=>'xsd:string'),
				'PostalAddress' => array('name'=>'PostalAddress', 'type'=>'xsd:string'),
				'MailingAddress' => array('name'=>'MailingAddress', 'type'=>'xsd:string'),
				'ResidentialAddress' => array('name'=>'ResidentialAddress', 'type'=>'xsd:string'),
				'Title' => array('name'=>'Title', 'type'=>'xsd:string'),
				'HomePhone' => array('name'=>'HomePhone', 'type'=>'xsd:string'),
				'OfficePhone' => array('name'=>'OfficePhone', 'type'=>'xsd:string'),
				'Mobile' => array('name'=>'Mobile', 'type'=>'xsd:string'),
				'Fax' => array('name'=>'Fax', 'type'=>'xsd:string'),
				'Url' => array('name'=>'Url', 'type'=>'xsd:string'),
				'DistinguishedName' => array('name'=>'DistinguishedName', 'type'=>'xsd:string'),
				'Active' => array('name'=>'Active', 'type'=>'xsd:int')
				
			)
		);
		$this->wsdl->addComplexType('ADGroupsList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:ADGroup[]')),
			'tns:ADGroup'
		);
		$this->wsdl->addComplexType('ADUsersList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:ADUser[]')),
			'tns:ADUser'
		);
		
		$this->register('testConnection',
			array('message'=>'xsd:string'),
			array('response'=>'xsd:string'),
			'urn:kerm',
			'urn:kerm#testConnection',
			'rpc',
			'encoded',
			'Simple procedure for testing the SOAP connection. Returns the received string'		
		);
		$this->register('sendKermGroups',
			array(
				'groups_list'=>'tns:ADGroupsList',
				'customer_id' =>'xsd:int'
			),
			array('resp'=>'xsd:string'),
			'urn:kerm',
			'urn:kerm#sendKermGroups',
			'rpc',
			'encoded',
			'adds the AD groups from the client to the keyos database'
		);
		/**
		 * send a list with all the users that need to be added in kerm
		 */
		$this->register('sendKermUsers',
			array(
				'users_list'=>'tns:ADUsersList',
				'customer_id' => 'xsd:int'
			),
			array('resp'=>'xsd:int'),
			'urn:kerm',
			'urn:kerm#sendKermUsers',
			'rpc',
			'encoded',
			'add customer users to the kewos database'
		);
		$this->register('sendAvailableDomains',
			array('customer_id' => 'xsd:int', 'domains'=>'tns:StringsList'),
			array('resp' => 'xsd:int'),
			'urn:kerm',
			'urn:kerm#sendAvailableDomains',
			'rpc',
			'encoded',
			'add the available domains for each customer'			
		);
		
		$this->register('getUsersToAdd',
			array('customer_id'=>'xsd:int'),
			array('resp'=>'tns:ADUsersList'),
			'urn:kerm',
			'urn:kerm#getUsersToAdd',
			'rpc',
			'encoded',
			'gets the list of users to add or to modify'
		);
		/**
		 * gets the last report time as a unix timestamp
		 * if the server has any users addeed beyond that date it'll send the new users back to the server
		 * if there is not last report time, the exchange will send a list with all the user in the system
		 */
		$this->register('getLastReportTime',
			array('customer_id'=>'xsd:int'),
			array('resp'=>'xsd:int'),
			'urn:kerm',
			'urn:kerm#getLastReportTime',
			'rpc',
			'encoded',
			'gets the last report time as a unix timestamp'
		);
	}
}

function testConnection($message)
{
	return "This message was received by the server: '".$message."'";
}
function sendKermGroups($groups_list, $customer_id)
{
	class_load('KermADGroup');
	$result = "I received the following groups: ";
	foreach ($groups_list as $group)
	{
		$kad_grp = new KermADGroup();
		$kad_grp->customer_id = $group['customer_id'];
		$kad_grp->name = $group['name'];
		$kad_grp->distinguishedname = $group['distinguishedname'];
		$kad_grp->description = $group['description'];
		$kad_grp->save_data();
	}
	//aici trebuie modificat 1 in customer_id
	$res = KermADGroup::get_groups_list($customer_id);
	foreach ($res as $rr) {
		$result.=$rr;	
	}
	return $result;
}
function sendAvailableDomains($customer_id, $domains)
{
	if($domains != null)
	{
		$count = count($domains);
		if($count>0)
		{
			foreach ($domains as $domain)
			{
				db::db_query("replace into ".TBL_KERM_CUSATOMERS_DOMAINS." values (".$customer_id.", '".$domain."')");
			}
		}
	}
	return 1;
}
function sendKermUsers($users_list, $customer_id)
{
	class_load('KermADUser');
	foreach ($users_list as $user)
	{
		//check if this user doesn't already exist
		$uid = KermADUser::get_existing_uid($user['UserName'], $customer_id);
		$kad_user = new KermADUser($uid);
		$kad_user->load_from_array($user);
		
		$kad_user->save_data();
	}
	//now we need to recod this time in the last report time from this customer
	$query = "replace into ".TBL_KERM_AD_REPORTS." values (".$customer_id.", unix_timestamp(now()))";
	db::db_query($query);
	return 1;
	
}
function getLastReportTime($customer_id)
{
	$query = "select last_report from ".TBL_KERM_AD_REPORTS." where customer_id=".$customer_id;
	$lr = db::db_fetch_field($query, 'last_report');
	return $lr;
}
function getUsersToAdd($customer_id)
{
	class_load('KermADUser');
	$users = KermADUser::get_users_list(array("status"=>CKERM_STATUS_APPROVED, 'customers'=>array($customer_id)));
	return $users;
}
?>