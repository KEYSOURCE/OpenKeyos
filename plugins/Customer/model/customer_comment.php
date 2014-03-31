<?php
/**
* Class for storing and manipulating comments about customers
*
*/

class_load ('Customer');

class CustomerComment extends Base
{
	/** Comment ID
	* @var int*/
	var $id = null;
	
	/** Customer ID
	* @var int */
	var $customer_id = null;
	
	/** Subject for the comment
	* @var string */
	var $subject = '';
	
	/** Comments
	* @var text */
	var $comments = '';
	
	/** The ID of the user who entered them
	* @var in */
	var $user_id = null;
	
	/** The date when it was entered
	* @var time */
	var $created = 0;
	
	
	/** The database table storing customer comments data 
	* @var string */
	var $table = TBL_CUSTOMERS_COMMENTS;
	
	
	/** List of fields to be used when fetching or saving data to the database
	* @var array */
	var $fields = array('id', 'customer_id', 'subject', 'comments', 'user_id', 'created');

	
	/**
	* Constructor, also loads the customer comments data from the database if a contact ID is specified
	* @param	int $id		The contact ID
	*/
	function CustomerComment ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
                        //$this->verify_access();
		}
	}
	
	
	/** Checks if the comment data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->subject) {error_msg ('Please specify a subject'); $ret = false;}
		if (!$this->customer_id) {error_msg ('Please specify a customer'); $ret = false;}
		
		return $ret;
	}
	
	
	/**
	* [Class Method] Returns customer comments according to the specified criteria
	* @param	array	$filter			Associative array with filtering criteria. Can contain:
	*						- customer_id: return only comments for this customer.
	* @return	array(CustomerComment)		Array with matching CustomerComment objects
	*/
	public static function get_comments ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT id FROM '.TBL_CUSTOMERS_COMMENTS.' cc WHERE ';
		if ($filter['customer_id']) $q.=' cc.customer_id='.$filter['customer_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ' , $q);
		$q.= 'ORDER BY cc.subject';
		
		$ids = db::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerComment ($id);
		
		return $ret;
	}

        function verify_access() {
            $uid = get_uid();
            class_load('User');
            $user = new User($uid);
            if($user->type == USER_TYPE_CUSTOMER) {
                if($this->customer_id != $user->customer_id) {
                    $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                    header("Location: $url\n\n");
                    exit;
                }
            }
        }
}

?>