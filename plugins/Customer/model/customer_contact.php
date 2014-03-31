<?php

/**
* Class for storing and manipulating contact persons for customers
*
* As opposed to a customer user, a customer contact does not have a login
* accont on KeyOS.
*
* Phone numbers are stored in CustomerContactPhone objects.
*
*/

class_load ('Customer');
class_load ('CustomerContactPhone');

class CustomerContact extends Base
{
	/** Contact ID
	* @var int*/
	var $id = null;
	
	/** Customer ID
	* @var int */
	var $customer_id = null;
	
	/** First name
	* @var string */
	var $fname = '';
	
	/** Last name
	* @var string */
	var $lname = '';
	
	/** E-mail address
	* @var string */
	var $email = '';
	
	/** The position or function in the company
	* @var string */
	var $position = '';
	
	/** Comments about this contact
	* @var text */
	var $comments = '';
	
	
	/** Array with the phone numbers for this contact - loaded when the object is loaded
	* @var array (CustomerContactPhone) */
	var $phones = array ();
	
	
	/** The database table storing customer contacts data 
	* @var string */
	var $table = TBL_CUSTOMERS_CONTACTS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'fname', 'lname', 'email', 'position', 'comments');

	
	/**
	* Constructor, also loads the customer contact data from the database if a contact ID is specified
	* @param	int $id		The contact ID
	*/
	function CustomerContact ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	/** Loads a contact information from the database, as well as the associated phone numbers */
	function load_data ()
	{
		parent::load_data ();
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_CUSTOMERS_CONTACTS_PHONES.' WHERE contact_id='.$this->id.' ORDER BY id ';
			$ids = db::db_fetch_vector ($q);
			foreach ($ids as $id) $this->phones[] = new CustomerContactPhone ($id);
		}
	}
	
	
	/** Checks if the contact information is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->customer_id) {error_msg ('Please specify the customer to which this contact belongs.'); $ret = false;}
		if (!$this->fname and !$this->lname) {error_msg ('Please specify a name for this contact.'); $ret = false;}
		
		return $ret;
	}
	
	
	/** Deletes a contact information, as well as it associated phones */
	function delete ()
	{
		if ($this->id)
		{
			// Delete the associated phones
			for ($i=0; $i<count($this->phones); $i++) $this->phones[$i]->delete ();
			
			parent::delete ();
		}
	}
	
	
	/**
	* [Class Method] Returns customer contacts according to the specified criteria
	* @param	array	$filter			Associative array with filtering criteria. Can contain:
	*						- customer_id: return only contacts for this customer.
	* @return	array(CustomerContact)		Array with matching CustomerContact objects
	*/
	public static function get_contacts ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_CUSTOMERS_CONTACTS.' cc WHERE ';
		if ($filter['customer_id']) $q.=' cc.customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ' , $q);
		$q.= 'ORDER BY cc.fname, cc.lname';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerContact($id);
		
		return $ret;
	}
        
    /**
     * [Class Method] gets a list of all the customer contacts according to the specified filter
     * @param array $filter                 Associative array with filtering criteris. Can contain
     *                                      - customer_id: returns only contacts for the specified customer
     * @return list(id, contact_name)
     */
    public static function get_contacts_list($filter = array()){
        $ret = array();
        $query = 'SELECT id, concat(fname, " ", lname) as name FROM '.TBL_CUSTOMERS_CONTACTS.' WHERE ';
        if(isset($filter['customer_id']) && is_numeric($filter['customer_id'])){
            $query .= ' customer_id = '.$filter['customer_id'].' AND ';
        }
        $query = preg_replace ('/AND\s*$/', ' ', $query);
        $query = preg_replace ('/WHERE\s*$/', ' ' , $query);
        $query.= 'ORDER BY fname, lname';
        $ret = db::db_fetch_list($query);
        return $ret;
    }

    function verify_access() {
        $uid = get_uid();
        class_load('User');
        $user = new User($uid);
        if($user->type == USER_TYPE_CUSTOMER) {
            if($this->id != $user->customer_id) {
                $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                header("Location: $url\n\n");
                exit;
            }
        }
    }
}

?>