<?php

class_load ('Customer');
class_load ('HttpConn');
class_load ('InterventionLocation');
class_load ('BaseDisplay');

define ('EXCH_APP_RES_CREATED', 1);			// The appointment was created OK - doesn't exclude additional errors
define ('EXCH_APP_RES_FAILED_CREATE', 512);		// Failed creating the appointment in Exchange
define ('EXCH_APP_RES_FAILED_CONNECT', 1024);		// Failed connecting to Exchange server
define ('EXCH_APP_RES_FAILED_ORGANIZER_LOGIN', 2048);	// Failed login to Exchange organizer mailbox
define ('EXCH_APP_RES_FAILED_ATTENDEES_LOGIN', 4096);	// Failed login to Exchange for one or more attendeed

/**
* Class for handling interfacing with the Microsoft Exchange server.
*
* This implements authentication functions, either with Digest of Basic authentication,
* using the Exchange users login information stored in database. Note that passwords are
* not stored in database, only the secure identification strings.
*
* It also implements methods for replicating tasks from the local system into the
* Exchange Calendar system.
*
* The connection with the Exchange server is handled through HttpConn objects. The Exchange
* user information is stored in the respective User objects, in the 'exchange' field which
* contains an UserExchange object.
*/

class ExchangeInterface
{
	/** The Exchange login name
	* @var string */
	var $exch_login = '';
	
	/** The Exchange e-mail address of the user
	* @var string */
	var $exch_email = '';
	
	/** The Exchange password of the user. After the authentication is done,
	* this field will be cleared for safety, just in case the object is saved
	* in an unsafe location (e.g. in a session temporary directory
	* @var string */
	var $exch_password = '';
	
	/** The HA1 authentication string to be used if Digest Authentication is employed
	* This is stored in the database in the user properties - it is safe to do so since
	* the string does not contain the password in clear */
	var $exch_ha1 = '';
	
	/** The identification string to be used if Basic Authentication is employed. This
	* is stored in the database in the user properties. It is relatively safe to do so,
	* but less safe than the SHA1 identification string for Digest Authentication.
	* So if Digest Authentication is available, it is better to use that one.
	* @var string */
	var $exch_basic = '';
	
	
	/** The IP address of the Exchange Server. The constant comes from the 
	* system configuration file 
	* @var string */
	var $exch_server = EXCHANGE_SERVER;
	
	/** The base URL for accessing the Web interface of Exchange. The constant comes
	* from the system configuration file
	* @var string */
	var $exch_base_uri = EXCHANGE_BASE_URI;
	
	/** The base URL for accessing the current user's Exchange mailbox. It is build
	* in the constructor, if a user info is provided
	* @var string */
	var $user_base_uri = '';
	
	
	/** The localized name of the Inbox folder for the current user. It is
	* determined when the authentication is performed (do_authentication() method)
	* @var string */
	var $user_inbox_fld = '';
	
	/** The URI to the Inbox for the current user. It is
	* determined when the authentication is performed (do_authentication() method)
	* @var string  */
	var $user_inbox_uri = '';
	
	/** The localized name of the Calendar folder for the current user. It is
	* determined when the authentication is performed (do_authentication() method)
	* @var string */
	var $user_calendar_fld = '';
	
	/** The URI to the Calendar for the current user. It is
	* determined when the authentication is performed (do_authentication() method)
	* @var string */
	var $user_calendar_uri = '';
	
	
	/** True or false if the authentication has been succesfully performed for 
	* the current user 
	* @var bool */
	var $is_authenticated = false;
	
	/** The HttpConn object used for communication with the Exchange server
	* @var HttpConn */
	var $conn = null;
	
	/** The last error message encountered while communicating with Exchange, if any.
	* @var string */
	var $last_error = '';
	
	
	/** Constructor. Initializes a new interface object, using either a password or and
	* identification string (Digest or Basic Authentication). If a password is passed, 
	* then the HA1 string is ignored (can also be empty).
	* @param	string				$exch_login		The login name for the Exchange server
	* @param	string 				$exch_email		The e-mail address for the Exchange server
	* @param	string				$exch_ha1		The HA1 string - for Digest authentication
	* @param	string				$exch_basic		The Base64 identification string - for Basic authentication
	* @param	string				$exch_password		The Exchnage password. This should be used
	* 									when registering a new Exchange account or when
	*									the password and/or login name has been changed.
	*/
	function ExchangeInterface ($exch_login, $exch_email, $exch_ha1 = '', $exch_basic = '', $exch_password = '')
	{
		$this->exch_login = $exch_login;
		$this->exch_email = $exch_email;
		$this->exch_ha1 = $exch_ha1;
		$this->exch_basic = $exch_basic;
		$this->exch_password = $exch_password;
		
		if ($exch_email)
		{
			$this->user_base_uri = $this->exch_base_uri.'/'.$this->exch_email;
		}
		else $this->user_base_uri = '';
	}
	
	
	/** Performs WebDav authentication and loads the names and URLs of the inbox and calendar folders for the user.
	* If any errors are encountered, they will be placed in the last_error field.
	* @return	bool							True or False if the authentication was OK or not.
	*/
	function do_authentication ()
	{
		$ret = false;
		if ($this->exch_login and $this->exch_email and ($this->exch_password or $this->exch_ha1 or $this->exch_basic))
		{
			// Initialize a new connection to the Exchange server.
			$this->conn = new HttpConn ($this->exch_server, $this->user_base_uri.'/', 'PROPFIND', EXCHANGE_WEB_PORT, EXCHANGE_PROTOCOL);
			
			// While logging in, make also a request to fetch the default folders for the current user
			$xml = '<?xml version="1.0"?><a:propfind xmlns:a="DAV:">'.
				'<a:prop xmlns:mbox="urn:schemas:httpmail:"><mbox:calendar/></a:prop>'.
				'<a:prop xmlns:mbox="urn:schemas:httpmail:"><mbox:inbox/></a:prop>'.
				'</a:propfind>';
			
			// Additional headers needed during Exchange authentication
			$extra_headers = array ('Translate' => 'f',  'Depth' => '0', 'Brief' => 't');
			$this->conn->set_payload ($xml, 'text/xml');
			
			// Set the HTTP credentials - either use the new given password or use the existing identification strings
			if ($this->exch_password) $this->conn->set_credentials_password ($this->exch_login, $this->exch_password);
			elseif ($this->exch_ha1) $this->conn->set_credentials_hash ($this->exch_login, $this->exch_ha1);
			elseif ($this->exch_basic) $this->conn->set_credentials_basic ($this->exch_login, $this->exch_basic);
			
			// Perform a login on the Exchange server with the credentials that we have
			$this->is_authenticated = $this->conn->do_authentication ($response, $extra_headers);
			
			if ($this->is_authenticated)
			{
				// Authentication was OK, load the user's settings from the response
				$ret = true;
				
				$this->exch_ha1 = $this->conn->digest_ha1;
				$this->exch_basic = $this->conn->basic_ident;
				
				// No need to process the full XML response since we know exactly what to expect
				preg_match ('/<d\:inbox>(.*)<\/d:inbox>/', $response, $m);
				$this->user_inbox_uri = $m[1];
				$this->user_inbox_fld = preg_replace ('/^.*\//', '', $this->user_inbox_uri);
				
				preg_match ('/<d\:calendar>(.*)<\/d:calendar>/', $response, $m);
				$this->user_calendar_uri = $m[1];
				$this->user_calendar_fld = preg_replace ('/^.*\//', '', $this->user_calendar_uri);
			}
			else
			{
				// Authentication has failed
				$this->last_error = $this->conn->last_error;
				$ret = false;
			}
			
			// Whatever the result of the authentication, empty the password attribute - in case the
			// object is saved in the session or somewhere else unsafe
			$this->exch_password = '';
		}
		else
		{
			$this->last_error = 'Login name, email and/or password missing.';
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Builds the XML for creating an appointment or meeting request for a Keyos task. When
	* replicating a task to Exchange this will be used twice. Once for getting the appointment creation XML
	* and then, once the appointment was created in the organizer's calendar and we have the appointment UID,
	* this can be called to create the invitation request XML.
	* @param	Task						$task		The Keyos task
	* @param	User						$organizer	The User object for the task organizer
	* @param	array(User)					$attendees	Array with the User objects for the task attendees. It is
	*										assumed that the list includes only user for whom we have
	*										valid Exchange logins.
	* @param	string						$class		The type of XML to generate:
	*										- IPM.Appointment
	*										- IPM.Schedule.Meeting.Request
	* @param	string						$app_uid	Used when creating a meeting invitation, contains
	*										the Exchange UID of the meeting from the organizer's calendar.
	* @return	string								The generate XML data.
	*/
	function build_task_xml ($task, $organizer = null, $attendees = array (), $class = 'IPM.Appointment', $app_uid = '')
	{
		$xml = '';
		
		if ($task->id)
		{
			// Set the subject and location of the appointment
			$xml_subject = '#'.$task->ticket_id.': '.$task->ticket_subject.' ['.Customer::get_customer_name($task->customer_id).'] ';
			$xml_location = InterventionLocation::get_location_name ($task->location_id);
			if ($task->customer_location_name) $xml_location.= ' - '.$task->customer_location_name;
			
			// Set the text content of the appointment
			$xml_content = 'Ticket: '.BaseDisplay::mk_redir('ticket_edit', array('id'=>$task->ticket_id), 'krifs', 'https');
			$xml_content.= "\r\n\r\n".$task->comments;
			
			// Build the list of attendees - if any
			foreach ($attendees as $attendee) $xml_recipients.= $attendee->exchange->exch_email.',';
			$xml_recipients = preg_replace ('/,$/', '', $xml_recipients);
			
			$xml = '<?xml version="1.0"?>' .
				'<g:propertyupdate xmlns:g="DAV:" '.
				'xmlns:e="http://schemas.microsoft.com/exchange/" '.
				'xmlns:mapi="http://schemas.microsoft.com/mapi/" '.
				'xmlns:mapit="http://schemas.microsoft.com/mapi/proptag/" '.
				'xmlns:x="xml:" xmlns:cal="urn:schemas:calendar:" '.
				'xmlns:dt="urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/" '.
				'xmlns:header="urn:schemas:mailheader:" '.
				'xmlns:mail="urn:schemas:httpmail:"> '.
				'<g:set><g:prop>';
			
			// Set the type of object being created - appointment or meeting invitation
			if ($class == 'IPM.Appointment')
			{
				$xml.=	'<g:contentclass>urn:content-classes:appointment</g:contentclass>' .
					'<e:outlookmessageclass>IPM.Appointment</e:outlookmessageclass>';
			}
			elseif ($class == 'IPM.Schedule.Meeting.Request')
			{
				$xml.= '<g:contentclass>urn:content-classes:calendarmessage</g:contentclass>' .
					'<e:outlookmessageclass>IPM.Schedule.Meeting.Request</e:outlookmessageclass>';
			}
			
			// Arrange the dates in the format needed by Exchange
			$date_format = 'Y-m-d\TH:i:00O\Z';
			$xml_date_start = date ($date_format, $task->date_start);
			$xml_date_end = date ($date_format, $task->date_end);
			$xml_date_start = preg_replace ('/00Z$/', ':00Z', $xml_date_start);
			$xml_date_end = preg_replace ('/00Z$/', ':00Z', $xml_date_end);
			
			$xml.=	'<mail:subject>'.htmlentities($xml_subject).'</mail:subject>' .
				//'<mail:htmldescription>'.htmlentities($xml_content).'</mail:htmldescription>' .
				'<mail:textdescription>'.htmlentities($xml_content).'</mail:textdescription>' .
				'<cal:location>'.htmlentities($xml_location).'</cal:location>' .
				'<cal:dtstart dt:dt="dateTime.tz">'.$xml_date_start.'</cal:dtstart>' .
				'<cal:dtend dt:dt="dateTime.tz">'.$xml_date_end.'</cal:dtend>' .
				
				'<cal:instancetype dt:dt="int">0</cal:instancetype>' .
				'<cal:busystatus>BUSY</cal:busystatus>' .
				'<cal:meetingstatus>CONFIRMED</cal:meetingstatus>' .
				'<cal:alldayevent dt:dt="boolean">0</cal:alldayevent>'. 
				'<cal:responserequested dt:dt="boolean">0</cal:responserequested>' .
				'<cal:prodid>Keyos</cal:prodid>' ;
			
			if ($app_uid != '') $xml.= '<cal:uid>'.$app_uid.'</cal:uid>';
				
			$xml.=  '<header:to>'.$xml_recipients.'</header:to>' .
				'<cal:isorganizer dt:dt="boolean">1</cal:isorganizer>' .
				'<mapi:finvited dt:dt="boolean">1</mapi:finvited>';
				
			if ($class == 'IPM.Schedule.Meeting.Request')
			{
				$xml.= '<mapi:responsestatus dt:dt="int">1</mapi:responsestatus>' .
					'<mapi:responsestate dt:dt="int">0</mapi:responsestate>' .
					'<mapi:response_requested dt:dt="boolean">0</mapi:response_requested>' . // If 1, the attendee will be request to enter message
					'<mapi:apptstateflags dt:dt="int">3</mapi:apptstateflags>' .
					'<mapi:busystatus dt:dt="int">1</mapi:busystatus>' .
					'<mapi:intendedbusystatus dt:dt="int">2</mapi:intendedbusystatus>';
			}
			
			$xml.= '<mapi:finvited dt:dt="boolean">1</mapi:finvited>' .
				'</g:prop></g:set>' .
				'</g:propertyupdate>';
		}
		
		return $xml;
	}
	
	
	/** [Class Method] Saves (replicates) a given task from the local Keyos system into the Exchange server, using WebDav
	* For tasks with multiple attendees, the procedure has more steps: first the appointment is created in the organizer's
	* calendar, then meeting invitations are dispatched by mail to the attendees, which invitations are "intercepted" 
	* in the attendee's Inbox and approved.
	* There is no other way to do this, because Exchange does not support automatic approval of meeting requests.
	* @param	Task					$task			The task to save/replicate
	* @param	string					$app_uid		(By ref) Will be loaded with the Exchage UID of the appointment
	* @param	array(User)				$attendees_no_exchange	(By ref) Will be loaded with attendees without Exchange login 
	*										info or for whom the Exchange login has failed
	* @return	int								A sum of one or more the following statuses: 
	*										EXCH_APP_RES_CREATED, EXCH_APP_RES_FAILED_CREATE, 
	*										EXCH_APP_RES_FAILED_CONNECT, EXCH_APP_RES_FAILED_ORGANIZER_LOGIN, 
	*										EXCH_APP_RES_FAILED_ATTENDEES_LOGIN
	*/
	function save_appointment ($task, &$app_uid, &$attendees_no_exchange)
	{
		$ret = 0;
		$app_uid = '';
		$attendees_no_exchange = array ();
		
		$organizer = new User ($task->user_id);
		if (!$organizer->exchange)
		{
			// The organizer doesn't have an Exchange interface defined
			$ret = val_flag_add ($ret, EXCH_APP_RES_FAILED_ORGANIZER_LOGIN);
		}
		else
		{
			// Obtain an authenticated Exchange interface for the organizer
			$exIface = $organizer->exchange->getExchangeInterface (true);
			
			if ($exIface->conn->last_conn_errno > 0)
			{
				// Failed connecting to Exchange server
				$ret = val_flag_add ($ret, EXCH_APP_RES_FAILED_CONNECT);
			}
			else
			{
				if (!$exIface->is_authenticated)
				{
					// Failed logging in to Exchange as organizer
					$ret = val_flag_add ($ret, EXCH_APP_RES_FAILED_ORGANIZER_LOGIN);
				}
				else
				{
					// Ok, we have an authenticated interface, start creating the appointment
					$appointment_name = 'keyos_app_'.$task->id.'.eml';
					$appointment_uri = $exIface->user_base_uri.'/'.$exIface->user_calendar_fld.'/'.$appointment_name;
					
					// Fetch the attendees, if any, and see which ones have have valid Exchange logins
					$attendees = array ();
					$attendees_exchange = array ();	// Only attendees for whom we can get an authenticated Exchange interface
					$attendees_no_exchange = array ();
					$attendees_ex_ifaces = array ();
					foreach ($task->attendees_ids as $attendee_id) $attendees[] = new User ($attendee_id);
					foreach ($attendees as $attendee)
					{
						$valid_exchange = false;
						$exIfaceAttendee = null;
						if ($attendee->exchange->exch_email)
						{
							$exIfaceAttendee = $attendee->exchange->getExchangeInterface (true);
							if ($exIfaceAttendee->is_authenticated) $valid_exchange = true;
						}
						
						if (!$valid_exchange) $attendees_no_exchange[] = $attendee;
						else
						{
							$attendees_exchange[] = $attendee;
							$attendees_ex_ifaces[$attendee->id] = $exIfaceAttendee;
						}
					}
					
					// Build the XML for creating the appointment in organizer's calendar
					$xml = ExchangeInterface::build_task_xml ($task, $organizer, $attendees_exchange);
					
					$exIface->conn->set_new_url ($appointment_uri, 'PROPPATCH', $xml, 'text/xml');
					$exIface->conn->set_default_headers ();
					$exIface->conn->send_request ();
					$response = $exIface->conn->read_response ();
					
					if ($exIface->conn->resp_status_code >= 200 and $exIface->conn->resp_status_code < 300)
					{
						$ret = val_flag_add ($ret, EXCH_APP_RES_CREATED);
						
						// Fetch the UID of the newly created appointment
						$xml_uid = '<?xml version="1.0"?><a:propfind xmlns:a="DAV:">'.
						'<a:prop xmlns:cal="urn:schemas:calendar:"><cal:uid/></a:prop>'.
						'</a:propfind>';
						
						$exIface->conn->set_new_url ($appointment_uri, 'PROPFIND', $xml_uid, 'text/xml');
						$exIface->conn->set_default_headers ();
						$exIface->conn->send_request();
						$response_uid = $exIface->conn->read_response ();
						preg_match ('/<d\:uid>(.*)<\/d:uid>/', $response_uid, $m);
						$app_uid = trim($m[1]);
						
						// If we have attendees with valid Exchange accounts, dispatch and approve the invitations
						if (count($attendees_exchange) > 0)
						{
							if ($exIface->conn->resp_status_code >= 200 and $exIface->conn->resp_status_code < 300 and $app_uid)
							{
								// Build and dispatch the meeting request (this will email all attendees)
								$class = 'IPM.Schedule.Meeting.Request';
								$xml_request = ExchangeInterface::build_task_xml ($task, $organizer, $attendees_exchange, $class, $app_uid);
								
								// Create the request
								$appointment_uri_req = $exIface->user_base_uri.'/'.$exIface->user_calendar_fld.'/req.'.$appointment_name;
								$exIface->conn->set_new_url ($appointment_uri_req, 'PROPPATCH', $xml_request, 'text/xml');
								$exIface->conn->set_default_headers ();
								$exIface->conn->send_request ();
								$response = $exIface->conn->read_response ();
								
								// Move it to the submission url for sending the mails
								$submission_url = $exIface->user_base_uri.'/##DavMailSubmissionURI##/';
								$exIface->conn->set_new_url ($appointment_uri_req, 'MOVE', $xml_request, 'message/rfc822');
								$exIface->conn->headers_extra = array ('Destination' => $submission_url);
								$exIface->conn->set_default_headers ();
								$exIface->conn->send_request();
								$response_request = $exIface->conn->read_response ();
								
								// Now for each attendee login to mailbox and accept the appointment
								foreach ($attendees_exchange as $attendee)
								{
									$exIfaceAttendee = &$attendees_ex_ifaces[$attendee->id];
									
									$xml_src = '<?xml version="1.0"?><a:searchrequest xmlns:a="DAV:">n<a:sql>' .
									'SELECT "DAV:href", "urn:schemas:httpmail:subject", "urn:schemas:calendar:prodid" FROM '.
									'scope(\'shallow traversal of "'.$exIfaceAttendee->user_inbox_uri.'"\') '.
									'WHERE "DAV:contentclass"=\'urn:content-classes:calendarmessage\' ' .
									'AND "urn:schemas:calendar:prodid"=\'Keyos\''.
									'</a:sql></a:searchrequest>';
									
									$exIfaceAttendee->conn->set_new_url ($exIfaceAttendee->user_inbox_uri, 'SEARCH', $xml_src, 'text/xml');
									$exIfaceAttendee->conn->headers_extra = array (
										'Destination' => $submission_url,
									);
									$exIfaceAttendee->conn->set_default_headers ();
									$exIfaceAttendee->conn->send_request();
									$response_src = $exIfaceAttendee->conn->read_response ();
									
									// Automatically accept all the requests coming from Keyos
									preg_match_all ('/<a\:href>([^<]*)<\/a\:href>/', $response_src, $m);
									$requests_urls = array_unique($m[1]);
									
									foreach ($requests_urls as $req_uri)
									{
										$exIfaceAttendee->conn->set_new_url ($req_uri.'?Cmd=accept', 'GET', '', 'text/xml');
										$exIfaceAttendee->headers_extra = array ('Referer' => $req_uri.'?Cmd=open');
										$exIfaceAttendee->conn->set_default_headers ();
										$exIfaceAttendee->conn->send_request();
										$response = $exIfaceAttendee->conn->read_response ();
										
									}
									$exIfaceAttendee->conn->do_disconnect ();
								}
							}
							else
							{
								// The fetching of the UID has failed, which means that the appointment
								// was not properly created in the Exchange mailbox of the organizer
								$ret = val_flag_add ($ret, EXCH_APP_RES_FAILED_CREATE);
							}
							
						}
						
						// Check if we have attendees for which we couldn't get an Exchange interface
						if (count($attendees_no_exchange) > 0)
						{
							$ret = val_flag_add ($ret, EXCH_APP_RES_FAILED_ATTENDEES_LOGIN);
						}
					}
					else
					{
						// The Exchange server returned an error when attempting to create the appointment
						$ret = val_flag_add ($ret, EXCH_APP_RES_FAILED_CREATE);
					}
				}
			}
			
			// Close the Exchange interface for the organizer
			if ($exIface->conn) $exIface->conn->do_disconnect ();
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Deletes a task from Exchange, either completly or only for one of the attendees 
	* @param	Task						$task		The Task object to delete from Exchange
	* @param	int						$attendee_id	The user ID of the attendee for which to delete
	*										the appointment. If not specified, then the appointment
	*										is completly deleted for all attendees.
	*/
	function delete_appointment ($task, $attendee_id = null)
	{
		$ret = false;
		
		if ($task->exchange_uid) 
		{
			$attendees = array ();		// The user attendees from whos Exchange calendars to delete the appointment.
							// Will be limited to attendees with valid exchange logins
			$attendees_ex_ifaces = array (); // Array with authenticated Exchange interfaces for the affected users
			if ($attendee_id)
			{
				// A specific attendee was requested for deletion
				$attendee = new User ($attendee_id);
				if ($attendee->exchange)
				{
					$attendee_ex_iface = $attendee->exchange->getExchangeInterface (true);
					if ($attendee_ex_iface->is_authenticated)
					{
						$attendees[] = $attendee;
						$attendees_ex_ifaces[] = $attendee_ex_iface;
					}
				}
			}
			else
			{
				// No specific attendee was requested, delete for all attendees and organizers
				foreach ($this->attendees_ids as $attendee_id)
				{
					$attendee = new User ($attendee_id);
					if ($attendee->exchange)
					{
						$attendee_ex_iface = $attendee->exchange->getExchangeInterface (true);
						if ($attendee_ex_iface->is_authenticated)
						{
							$attendees[] = $attendee;
							$attendees_ex_ifaces[] = $attendee_ex_iface;
						}
					}
				}
				
				// Add the organizer to the list as well - make it the last one in the list
				$organizer = new User ($task->user_id);
				if ($organizer->exchange)
				{
					$ex_iface = $organizer->exchange->getExchangeInterface (true);
					if ($ex_iface->is_authenticated)
					{
						$attendees[] = $organizer;
						$attendees_ex_ifaces[] = $ex_iface;
					}
				}
			}
			
			// Now we have the users for whom the appointment needs to be deleted
			foreach ($attendees_ex_ifaces as $ex_iface)
			{
				// Find the appointment based on its Exchange UID
				$xml_src = '<?xml version="1.0"?><a:searchrequest xmlns:a="DAV:">n<a:sql>' .
				'SELECT "DAV:href" FROM scope(\'shallow traversal of "'.$ex_iface->user_calendar_uri.'"\') '.
				'WHERE "urn:schemas:calendar:uid"=\''.$task->exchange_uid.'\''.
				'</a:sql></a:searchrequest>';
				
				$ex_iface->conn->set_new_url ($ex_iface->user_calendar_uri, 'SEARCH', $xml_src, 'text/xml');
				$ex_iface->conn->set_default_headers ();
				$ex_iface->conn->send_request();
				$response_src = $ex_iface->conn->read_response ();
				
				// If the appointment was found, delete it
				if ($ex_iface->conn->resp_status_code >= 200 and $ex_iface->conn->resp_status_code < 300)
				{
					preg_match_all ('/<a\:href>([^<]*)<\/a\:href>/', $response_src, $m);
					$app_urls = array_unique($m[1]);
					
					foreach ($app_urls as $app_uri)
					{
						$ex_iface->conn->set_new_url ($app_uri, 'DELETE', '', 'text/html');
						$ex_iface->conn->headers_extra = array ('Content-Length' => 0);
						$ex_iface->conn->set_default_headers ();
						$ex_iface->conn->send_request();
						$response = $ex_iface->conn->read_response ();
					}
				}
			}
		}
		
		return $ret;
	}
	
	
	/** [Class Method] Synchronizes the tasks from Exchange with the ones from Keyos. This means
	* deleting from Keyos the tasks that have been deleted (only by organizers) from Exchange.
	*/
	function synchronize_tasks_exchange ()
	{
		class_load ('Task');
		
		// Fetch the list of Exchange UIDs, ordered by user ID to limit the number of login requests
		$q = 'SELECT id, user_id, exchange_uid FROM '.TBL_TASKS.' WHERE exchange_uid<>"" ORDER BY user_id ';
		$data = DB::db_fetch_array ($q);
		
		$last_user_id = 0;
		$ex_iface = null;
		foreach ($data as $d)
		{
			// Get an Exchange interface for each involved user
			$user_id = $d->user_id;
			$app_uid = $d->exchange_uid;
			if ($user_id != $last_user_id)
			{
				$last_user_id = $user_id;
				if ($ex_iface->conn) $ex_iface->conn->do_disconnect ();
				$ex_iface = null;
				
				$user = new User ($user_id);
				if ($user->exchange)
				{
					$ex_iface = $user->exchange->getExchangeInterface ();
					if (!$ex_iface->is_authenticated) $ex_iface = null;
				}
			}
			
			if ($ex_iface)
			{
				// We have a valid Exchange interface, check if the appointment still exists in Exchange
				$xml_src = '<?xml version="1.0"?><a:searchrequest xmlns:a="DAV:">n<a:sql>' .
				'SELECT "DAV:href" FROM scope(\'shallow traversal of "'.$ex_iface->user_calendar_uri.'"\') '.
				'WHERE "urn:schemas:calendar:uid"=\''.$app_uid.'\''.
				'</a:sql></a:searchrequest>';
				
				$ex_iface->conn->set_new_url ($ex_iface->user_calendar_uri, 'SEARCH', $xml_src, 'text/xml');
				$ex_iface->conn->set_default_headers ();
				$ex_iface->conn->send_request();
				$response_src = $ex_iface->conn->read_response ();
				
				// If the appointment was found, delete it
				if ($ex_iface->conn->resp_status_code >= 200 and $ex_iface->conn->resp_status_code < 300)
				{
					preg_match_all ('/<a\:href>([^<]*)<\/a\:href>/', $response_src, $m);
					$app_urls = array_unique($m[1]);
					
					if (count($app_urls) == 0)
					{
						// The task doesn't exist in Exchange, delete it from Keyos as well
						$task = new Task ($d->id);
						$task->delete ();
					}
				}
			}
		}
	}
	
}

?>