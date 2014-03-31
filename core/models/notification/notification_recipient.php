<?php

class_load ('Notification');

/**
* Class for managing notifications recipients.
*
* For each user which should see/receive a notification, there will be one NotificationRecipient
* object linked to that notification.
*
*/

class NotificationRecipient extends Base
{
	/** Notification recipient ID
	* @var int */
	var $id = null;
	
	/** The ID of the notification to which this object is linked
	* @var int */
	var $notification_id = null;
	
	/** The ID of the user for which this object is linked
	* @var int */
	var $user_id = null;
	
	/** The time when the last e-mail was sent to this user for this notification
	* @var timestamp */
	var $emailed_last = 0;
	
	/** Additional information about the notification (subject), if it needs to 
	* be different than the parent's text for this user.
	* @var text */
	var $text = '';
	
	/** If True, e-mails will not be generated when the notification is "repeated".
	* If True, it also superceds the "no_repeat" field from the parent notification.
	* @var int */
	var $no_repeat = false;
	
	/** The template to use when sending e-mail notifications - if it's not the default template or
	* the template set in the parent notification.
	* @var string */
	var $template = '';
	
	/** The date when the notification was "read" (acknowledged) by the recipient. This 
	* is relevant only for Keysource users recipients.
	* @var timestamp */
	var $date_read = 0;
	
	
	/** The user object associated with this notification. Note that this is loaded only on request, with load_user()
	* @var User */
	var $user = null;
	
	/** The name of the table storing objects data
	* @var string */
	var $table = TBL_NOTIFICATIONS_RECIPIENTS;
	
	/** The name of the table fields to be used when loading/saving the object to the database
	* @var array */
	var $fields = array ('id', 'notification_id', 'user_id', 'emailed_last', 'text', 'no_repeat', 'template', 'date_read');


	/**
	* Constructor. Also loads the object data if an ID is specified
	* @param	int	$id		The ID of the notification recipient object to load
	*/
	function NotificationRecipient ($id = null)
	{
		if ($id)
		{
			$this->id = $id; 
			$this->load_data();
		}
	}
	
	
	/** Loads, on request, the User object for this object */
	function load_user ()
	{
		if ($this->user_id)
		{
			$this->user = new User ($this->user_id);
		}
	}
	
	/** [Class Method] Returns the number of "unread" notifications for a given user */
	public static function get_unread_notifs_count ($user_id)
	{
		if (is_numeric($user_id))
		{
			$q = 'SELECT count(DISTINCT notification_id) as cnt FROM '.TBL_NOTIFICATIONS_RECIPIENTS.' ';
			$q.= 'WHERE user_id='.$user_id.' AND date_read=0';
			return DB::db_fetch_field ($q, 'cnt');
		}
	}
	
	/** [Class Method] Returns all the recipients for a specific notification 
	* @param	int		$notification_id	The ID of the notification
	* @return	array(NotificationRecipient)		Associative array with the notification's recipients,
	*							the keys being user IDs
	*/
	public static function get_notification_recipients ($notification_id)
	{
		$ret = array ();
		if ($notification_id)
		{
			$q = 'SELECT id, user_id FROM '.TBL_NOTIFICATIONS_RECIPIENTS.' WHERE notification_id='.$notification_id;
			$ids = DB::db_fetch_list ($q);
			foreach ($ids as $id=>$user_id) $ret[$user_id] = new NotificationRecipient ($id);
		}
		
		return $ret;
	}
	
	/** [Class Method] Clears all the notifications recipients from the database that are linked to notifications
	* which don't exist anymore. Normally this should be called from the hourly crontab */
	public static function clear_orphan_recipients ()
	{
		$q = 'SELECT DISTINCT r.notification_id FROM '.TBL_NOTIFICATIONS_RECIPIENTS.' r ';
		$q.= 'LEFT OUTER JOIN '.TBL_NOTIFICATIONS.' n ON r.notification_id=n.id ';
		$q.= 'WHERE n.id IS NULL ';
		$ids = DB::db_fetch_vector ($q);
		
		foreach ($ids as $id) DB::db_query ('DELETE FROM '.TBL_NOTIFICATIONS_RECIPIENTS.' WHERE notification_id='.$id);
	}
	
}

?>