<?php
class KermADUser extends Base 
{
	var $id = null;
	var $customer_id = null;
	var $status;
	
	var $FirstName = "";
	var $MiddleInitals = "";
	var $LastName = "";
	var $DisplayName="";
	var $UserPrincipalName= "";
	var $PostalAddress = "";
	var $MailingAddress = "";
	var $ResidentialAddress = "";
	var $Title = "";
	var $HomePhone = "";
	var $OfficePhone = "";
	var $Mobile = "";
	var $Fax = "";
	var $Email = "";
	var $Url = "";
	var $UserName = "";
	var $Active = 0;
	var $Password = "";
	var $DistinguishedName = "";
	
	var $GroupName = "";
	 
	var $table = TBL_KERM_AD_USERS;
	var $fields = array('id', 'customer_id', 'status','FirstName', 'MiddleInitials', 'LastName', 'DisplayName', 'UserPrincipalName', 
	'PostalAddress', 'MailingAddress', 'ResidentialAddress', 'Title', 'HomePhone', 'OfficePhone', 'Mobile', 'Fax', 'Email', 'Url',
	'UserName', 'Active', 'Password', "DistinguishedName", 'GroupName');
	
	function KermADUser($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();	
		}
	}
	
	function get_users_list($filter = array())
	{
		$result = array();
		$status = $filter['status'];
		$customers = $filter['customers'];
		$order_by = $filter['order_by'];
		$order_dir = $filter['order_dir'];
		$query = "select id from ".TBL_KERM_AD_USERS." WHERE ";
		//if(($status!=null and $status!=-1) or ($customers!=null and !empty($customers))) $query.=" WHERE ";
		
		if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $this->current_user->get_assigned_customers_list();
			$query.= ' customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $query.=$k.", ";
				else $query.=$k;
			}
			$query.=") AND ";
		}
		
		if($status!=null and $status!=-1) $query.=" status=".$status;
		if($customers!=null and !empty($customers) and $status!=null and $status!=-1)
		{ 
			$query.=" AND customer_id in ";
			$q = "(";
			$i=0;
			$_cnt = count($customers);
			foreach ($customers as $cid)
			{
				if($i<$_cnt-1) $q.=$cid.", ";
				if($i == $_cnt-1) $q.=$cid;
				$i++;
			}
			$q.=")";
			$query.=$q;
		}
		else if($customers!=null and !empty($customers) and ($status==null or $status == -1))
		{
			$query.=" customer_id in ";
			$q = "(";
			$i=0;
			$_cnt = count($customers);
			foreach ($customers as $cid)
			{
				if($i<$_cnt-1) $q.=$cid.", ";
				if($i == $_cnt-1) $q.=$cid;
				$i++;
			}
			$q.=")";
			$query.=$q;
		}
		$query = preg_replace ('/WHERE\s*AND/', 'WHERE ', $query);
		$query = preg_replace ('/WHERE\s*$/', '', $query);
		$query = preg_replace ('/AND\s*$/', '', $query);
		
		if($order_by)
		{
			$od = 'ASC';
			if($order_dir) $od = $order_dir;
			$query .= " order by ".$order_by." ".$od;
			
		}
		//$query .= " LIMIT ".$filter['start'].", ".$filter['limit'];

		//debug($query);
		$ids = db::db_fetch_array($query);
		foreach($ids as $id)
		{
			$result[] = new KermADUser($id->id);
		}
		return $result;
	}
	
	function is_valid_data()
	{
		
		$ret = true;
		if(!$this->FirstName or $this->FirstName=="") 
		{
			error_msg("You have to set the first name for this user!");
			$ret = false;
		}
		if(!$this->LastName or $this->LastName=="") 
		{
			error_msg("You have to set the last name for this user!");
			$ret = false;
		}
		if(!$this->UserName or $this->UserName == "")
		{
			error_msg("You must set the login for the new account");
			$ret = false;
		}
		if(!$this->Email or $this->Email == "")
		{
			error_msg("You must set the email for the new account");
			$ret = false;
		}
		//if($this->exch_email and $this->exch_email!="")
		//{
		//	list($userName, $mailDomain) = split("@", $this->exch_email); 
		//	if($userName == "" or $mailDomain=="") 
		//	{
		//		error_msg("The username or the domain name from the email address are not valid");
		//		$ret = false;
		//	}
		//}
		return $ret;	
	}
	
	function get_group()
	{
		$query = 'select id from '.TBL_KERM_AD_GROUPS.' where name="'.$this->GroupName.'" and customer_id='.$this->customer_id;
		$id = db::db_fetch_field($query, 'id');	
		return $id;
	}
	function get_existing_uid($login, $customer)
	{
		
		$query = "select id from ".TBL_KERM_AD_USERS." where UserName='".$login."' and customer_id=".$customer;
		$id = db::db_fetch_field($query, 'id');
		return $id;
	}
	function get_available_domains($customer_id=null)
	{
		if($customer_id == null and $this->customer_id!=null) $customer_id = $this->customer_id;
		if($customer_id == null and $this->customer_id==null) $customer_id = 0;
		$query = "select domain from ".TBL_KERM_CUSATOMERS_DOMAINS." where customer_id=".$customer_id;
		$domains = db::db_fetch_vector($query);
		return $domains;
	}
}
?>