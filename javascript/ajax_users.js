

/**
* Contains AJAX functions and classes for Keyos users interactions
*/


/** 
* Class for representing brief user information (user ID, customer ID, name and if it is a customer user)
*/
function UserInfo (user_id, user_name, is_customer_user)
{
	if (user_id) this.user_id = user_id;
	else this.user_id = 0;
	
	if (user_name) this.user_name = user_name;
	else this.user_name = '';
	
	if (is_customer_user) this.is_customer_user = true;
	else this.is_customer_user = false;
}

/** 
* Class for representing a list of UserInfo objects 
*/
function UserInfosList ()
{
	/** Array which will store the users in the list as UserInfo objects */
	this.users = new Array ();
	
	/** Appends a new user to the list and sorts the list. No checks for duplicates is performed */
	this.appendUser = function (user_id, user_name, is_customer_user)
	{
		this.users[this.users.length] = new UserInfo (user_id, user_name, is_customer_user);
		this.sortUsers ();
	}
	
	
	/** Sorts the list of users by name, placing first the Keysource users and then the customer users */
	this.sortUsers = function ()
	{
		var is_sorted = false;
		while (!is_sorted)
		{
			is_sorted = true;
			for (var i=0; i<this.users.length-1; i++)
			{
				if ((this.users[i].is_customer_user < this.users[i+1].is_customer_user) ||
				(this.users[i].is_customer_user==this.users[i+1].is_customer_user && this.users[i].user_name > this.users[i+1].user_name))
				{
					is_sorted = false;
					var tmp = this.users[i];
					this.users[i] = this.users[i+1];
					this.users[i+1] = tmp;
				}
			}
		}
	}
	
	/** Checks if the user with the given ID already exists in the list or not */
	this.hasUser = function (user_id)
	{
		ret = false;
		for (var i=0; i<this.users.length; i++)
		{
			if (this.users[i].user_id == user_id)
			{
				ret = true;
				break;
			}
		}
		return ret;
	}
	
	/** Removes from the list the user at the specified position */
	this.removeUserByIdx = function (idx)
	{
		this.users.splice (idx, 1);
	}
	
	/** Deletes the entire list of users */
	this.clearList = function ()
	{
		this.users = new Array ();
	}
	
	/** Deletes only the customers users from the list */
	this.clearCustomersUsers = function ()
	{
		for (var i=0; i<this.users.length; i++)
		{
			if (this.users[i].is_customer_user)
			{
				this.removeUserByIdx (i);
				i--;
			}
		}
	}
	
}


/** Function for converting XML data received from an XML requester into a UserInfosList object */
function XMLToUserInfosList (xml)
{
	var ret = new UserInfosList ();
	var user_id = null;
	var user_name = null;
	var is_customer_user = null;
	var nodeUser = null;
	
	var usersNodes = xml.getElementsByTagName('user');
	if (usersNodes.length > 0)
	{
		for (var i=0; i<usersNodes.length; i++)
		{
			nodeUser = xml.getElementsByTagName('user')[i];
			user_id = nodeUser.getAttribute ('id');
			is_customer_user = (parseInt(nodeUser.getAttribute ('is_customer_user')) == 1);
			user_name = nodeUser.firstChild.nodeValue;
			
			ret.appendUser (user_id, user_name, is_customer_user);
		}
	}
	
	return ret;
}