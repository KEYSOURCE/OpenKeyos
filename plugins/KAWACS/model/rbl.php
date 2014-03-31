<?php

require_once('Net/DNSBL.php');

/**
 * Class to check the rbl lists for spamming servers registered on keyos
 *
 */
class Rbl
{
	/**
	 * Id of the customer
	 * @var int
	 */
	private $customer_id = 0;
	
	/**
	 * Array with all the public servers
	 * @var array;
	 */
	public $public_servers = array();
	/**
	 * constructor
	 */
	public function __construct($customer_id = 0) 
	{
		$this->customer_id = $customer_id;
		$this->get_public_servers();	
	}
	
	/**
	 * 
	 */
	function __destruct() {
		if($this->public_servers) $this->public_servers = null;
	}
	
	function get_public_servers()
	{
		if(!$this->customer_id)
		{
			$query = "select distinct cp.remote_ip, c.id as cid from ".TBL_COMPUTERS." cp inner join ".TBL_CUSTOMERS." c on c.id=cp.customer_id where c.active=1 and cp.remote_ip not like '' and cp.remote_ip not like '192.168.%' and cp.remote_ip not like '172.16.%' and cp.remote_ip not like '10.%' and cp.type=".COMP_TYPE_SERVER." order by c.id";
		}
		else {
			$query = "select distinct cp.remote_ip, c.id as cid from ".TBL_COMPUTERS." cp inner join ".TBL_CUSTOMERS." c on c.id=cp.customer_id where c.id=".$this->customer_id." and cp.remote_ip not like '' and cp.remote_ip not like '192.168.%' and cp.remote_ip not like '172.16.%' and cp.remote_ip not like '10.%' and cp.type=".COMP_TYPE_SERVER." order by c.id";
		}		
		$ips = db::db_fetch_array($query);
		foreach($ips as $ip)
		{
			$query = "select id, netbios_name from ".TBL_COMPUTERS." where remote_ip='".$ip->remote_ip."' and  customer_id=".$ip->cid;
			$comps = db::db_fetch_list($query);
			$public_server = array(
				'customer_id' => $ip->cid,
				'remote_ip' => $ip->remote_ip,
				'computers' => $comps
			);
			$this->public_servers[] = $public_server;
		}
	}
	
	function get_statuses()
	{
		$ret = array();
		if(count($this->public_servers) != 0)
		{
			//debug($this->public_servers);
			foreach($this->public_servers as $ss)
			{				
				$dnsbl = new Net_DNSBL();
				$remoteIP = $ss['remote_ip'];
				$cust = $ss['customer_id'];			
				
				$dnsbl->setBlacklists(array('sbl-xbl.spamhaus.org', 'dnsbl.sorbs.net', 'bl.spamcop.net'));
				if ($dnsbl->isListed($remoteIP)) 
				{
					//ok the server is listed, let's get some infos about the listing
					$listingDetails = $dnsbl->getDetails($remoteIP);
					$txt = $listingDetails['txt'][0];
					$txt_e = '';
					$txt_a = split(' ', $txt);
					foreach($txt_a as $t)
					{
						if(substr($t, 0, 7) == "http://") $txt_e.="<a href='".$t."'>".$t."</a>";
						else $txt_e .= $t;
					}
					$listingDetails['txt'][0] = $txt_e;
					$txt = '';
					$txt_a = null;
				    $ret[$cust][] = array('server'=> $ss, 'ip'=>$remoteIP, 'listed'=>"Listed", 'color'=>'red', 'details'=> $listingDetails);
				}
				else {
					$ret[$cust][] = array('server'=> $ss, 'ip'=>$remoteIP, 'listed'=>"Not listed", 'color'=>'grey', 'details'=>array());
				}		
			}
		}
		//debug($ret);
		return $ret;
	}
}

?>
