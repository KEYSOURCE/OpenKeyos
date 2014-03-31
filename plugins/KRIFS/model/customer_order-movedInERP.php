<?php

/**
* Class for managing customer orders taken from the ERP system
*
*/

class CustomerOrder extends Base
{
	/** Customer order ID
	* @var int */
	var $id = null;
	
	/** The ID (reference number) of the customer order in the ERP system - if any
	* If it is not specified, then this order must be linked to a customer subscription.
	* @var string */
	var $erp_id = '';
	
	/** The subscription number - if this order is part of a customer subscription. It is
	* mutually exclusive with erp_id
	* @var string */
	var $subscription_num = '';
	
	/** The ID of the customer to whom this order belongs to 
	* @var int */
	var $customer_id = null;

	/** Orders's date
	* @var time */
	var $date = 0;
		
	/** The subject of the customer order
	* @var string */
	var $subject = '';
	
	/** The category for this order (not used at the moment)
	* @var int */
	var $category_id = null;
	
	/** The status of the customer order - see $GLOBALS['ORDER_STATS']
	* @var int */
	var $status = ORDER_STAT_OPEN;
	
	/** Specifies if the order is billable or not
	* @var bool */
	var $billable = true;
	
	/** Specifies if this order is linked to a subscription
	* @var bool */
	var $for_subscription = false;
	
	/** Comments regarding this customer order
	* @var text */
	var $comments = '';

	
	/** The tickets associated with this order. Loaded on request, with load_tickets() method
	* @var array(Ticket) */
	var $tickets = array ();
	
	
	var $table = TBL_CUSTOMER_ORDERS;
	var $fields = array ('id', 'erp_id', 'subscription_num', 'customer_id', 'date', 'subject', 'category_id', 'status', 'billable', 'for_subscription', 'comments');

	/**
	* Constructor. Also loads the order data if an ID has been specified
	* @param	int	$id		The ID of the order to load
	*/
	function CustomerOrder ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	
	/** Loads, on request, the tickets associated with this order */
	function load_tickets ()
	{
		$this->tickets = $this->get_tickets();
	}
	
	/** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		
		if (!$this->customer_id) {error_msg ('Please specify the customer for this customer order'); $ret = false;}
		if (!$this->subject) {error_msg ('Please specify the subject for this customer order'); $ret = false;}
		if ($this->date < 0) {error_msg ('Please specify the date'); $ret = false;}
		if ($this->for_subscription)
		{
			if (!$this->subscription_num) {error_msg ('Please specify the subscription number for this customer order.'); $ret = false;}
		}
		else
		{
			if (!$this->erp_id) {error_msg ('Please specify the ERP order number for this customer order.'); $ret = false;}
		}
		
		return $ret;
	}
	
		
	/** Checks if the order can be deleted - only open orders and orders which have no linked tickets can be deleted */
	function can_delete ()
	{
		$ret = true;
		if ($this->id)
		{
			if ($this->status != ORDER_STAT_OPEN)
			{
				$ret = false;
				error_msg ('Only open customer orders can be deleted.');
			}
			else
			{
				$q = 'SELECT id FROM '.TBL_TICKETS.' WHERE customer_order_id='.$this->id.' LIMIT 1';
				$id = $this->db_fetch_field ($q);
				if ($id)
				{
					$ret = false;
					error_msg ('This customer order has already been linked to one or more tickets, it can\'t be deleted.');
				}
			}
		}
		return $ret;
	}

	
	/** Returns the ERP number for this order, which is the ERP ID or, if the order is linked to a 
	* subscription, the subscription number */
	function get_erp_num ()
	{
		if ($this->for_subscription) $ret = $this->subscription_num;
		else $ret = $this->erp_id;
		return $ret;
	}
	
	
	/** Return the tickets associated with this order 
	* @return	array(Ticket)			Array with the tickets associated with this order, sorted
	*						by their IDs in reverse order
	*/
	function get_tickets ()
	{
		$ret = array ();
		if ($this->id)
		{
			$q = 'SELECT id FROM '.TBL_TICKETS.' WHERE customer_order_id='.$this->id.' ORDER BY id DESC';
			$ids = $this->db_fetch_vector ($q);
			foreach ($ids as $id) $ret[] = new Ticket ($id);
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns a list with the available open tickets orders
	* @param	int		$customer_id	The ID of the customer for which to fetch the orders
	* @param	bool		$show_prefix	Specifies if the orders subjects to be prefixed with the type (order/subscr)
	* @return	array				Associative array, they keys being order IDs and the values being 
	*						their subjects
	*/
	function get_open_orders_list ($customer_id)
	{
		$ret = array ();
		if ($customer_id)
		{
			$filter = array (
				'customer_id' => $customer_id,
				'status_id' => ORDER_STAT_OPEN,
				'show_prefix' => true
			);
			$ret = CustomerOrder::get_orders_list ($filter);
		}
		
		return $ret;
	}
	
	/** Returns a "name" for this customer order, formatted in the same manner as the names from get_open_orders_list() */
	function get_list_name ()
	{
		$ret = '';
		if ($this->id)
		{
			$ret = ($this->for_subscription ? '[Subscr.] ['.$this->subscription_num.'] ' : '[Order] ['.$this->erp_id.'] ');
			$ret.= $this->subject;
		}
		return $ret;
	}
	
	
	/** [Class Method] Returns a list of available customer orders, according to some criteria
	* @param	array		$filter		Associative array with filtering criteria. Can contain:
	*						- customer_id: Return only orders for this customer
	*						- status: Return only orders of this status
	*						- show_prefix: If True, add "Order/Subscription" prefix to subjects. Default is False.
	* @return	array				Associative array, keys being order IDs and the values being their subjects
	*/
	function get_orders_list ($filter = array())
	{
		$ret = array ();
		
		$q = 'SELECT id, ';
		if ($filter['show_prefix']) $q.= ' concat(if(for_subscription,"[Subscr.] ","[Order] "),"[",if(for_subscription,subscription_num,erp_id),"] ",subject) as subject ';
		else $q.= 'concat("[",if(for_subscription,subscription_num,erp_id),"] ",subject) as subject ';
		$q.= 'FROM '.TBL_CUSTOMER_ORDERS.' WHERE ';
		
		if ($filter['customer_id'] and is_numeric($filter['customer_id'])) $q.= 'customer_id='.$filter['customer_id'].' AND ';
		if ($filter['status_id']) $q.= 'status='.$filter['status_id'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY for_subscription, date DESC, subject, customer_id ';
		
		$ret = DB::db_fetch_list ($q);
		
		return $ret;
	}
	
	
	/** [Class Method] Returns the customer orders available in the system, according to some criteria
	* @param	array		$filter		Associative array with filtering criteria. Can contain:
	*						- customer_id: Return only orders for this customer
	*						- status: Return only orders of this status
	*						- order_by, order_dir: How to sort the results.
	*						- start, limit: Limit the range of returned orders
	* @param	int		$cnt		(By reference) If it is initialized, it will be loaded with the 
	*						total number of orders which matched the given criteria.
	* @return	array(CustomerOrder)		Array with the matched CustomerOrder objects
	*/
	function get_orders ($filter = array(), &$cnt)
	{
		$ret = array ();
		
		if (!isset($filter['order_by'])) $filter['order_by'] = 'date';
		if (!isset($filter['order_dir'])) $filter['order_dir'] = 'DESC';
		$filter['order_by'] = 'co.'.$filter['order_by'];
		
		$q = 'FROM '.TBL_CUSTOMER_ORDERS.' co WHERE ';
		
		if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $this->current_user->get_assigned_customers_list();
			$q.= 'co.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q = trim (preg_replace ('/,\s*$/', '', $q));
			$q.=") AND ";
		}
		
		if ($filter['customer_id']) $q.= 'co.customer_id='.$filter['customer_id'].' AND ';
		if ($filter['status']) $q.= 'co.status='.$filter['status'].' AND ';
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		if (isset($cnt))
		{
			$q_count = 'SELECT count(DISTINCT co.id) as cnt '.$q;
			$cnt = DB::db_fetch_field ($q_count, 'cnt');
		}
		
		$q = 'SELECT id '.$q.' ORDER BY for_subscription, '.$filter['order_by'].' '.$filter['order_dir'].' ';
		$q.= 'LIMIT '.$filter['start'].', '.$filter['limit'];
		
		$ids = DB::db_fetch_vector ($q);
		foreach ($ids as $id) $ret[] = new CustomerOrder ($id);
		
		return $ret;
	}
}
?>