<?php

class_load('mRemoteConnectionInfo');
class_load('Computer');
class_load('ComputerRemoteService');
class_load('ComputerPassword');

class mRemoteConnection extends Base
{
	var $id = null;
	var $parent_id = null;
	var $name = "";
	var $customer_id = null;
	var $computer_id = null;
	var $machine_type = null;
	var $type = MREMOTE_CONNECTION_TYPE_CONNECTION;
	
	var $table = TBL_MREMOTE_CONNECTIONS;
	
	var $fields = array('id', 'parent_id', 'name', 'type', 'machine_type', 'customer_id', 'computer_id');
	
	var $connInfo = null;
	var $nodes = array();
	
	function mRemoteConnection($id = null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	
	function load_data()
	{
		//parent::load_data();
		if($this->id)
		{
			parent::load_data();
			if($this->type == MREMOTE_CONNECTION_TYPE_CONTAINER)
			{
				$this->nodes = $this->getChildrenNodes();
			}
			$this->loadConnectionInfo();			
		}
	}
	
	function save_data()
	{
		parent::save_data();
		if($this->connInfo) 
			$this->connInfo->save_data();
		else 
		{
			$this->connInfo = new mRemoteConnectionInfo($this->id);
			$this->connInfo->save_data();
		}
	}
	
	
	function getChildrenNodes()
	{
		//in order for this to have children it must be a container connection
		$ret = array();
		if($this->type != MREMOTE_CONNECTION_TYPE_CONTAINER) return;
		$q = "select id from ".TBL_MREMOTE_CONNECTIONS." where parent_id=".$this->id;
		$ids = db::db_fetch_vector($q);
		foreach($ids as $id)
		{
			$pp = new mRemoteConnection($id);
			$ret[] = $pp;
		}		
		return $ret;
	}
	function has_children()
	{
		$ret = false;
		if(!empty($this->nodes))
		{
			$ret = true;
		}
		return $ret;		
	}
	
	/**
	 * [Class Method] 
	 * Gets all the connections of containers of connections that don't have any parent (parent_id==0)
	 *
	 */
	function getRootNodes($customer_id = 0)
	{
		$ret = array();
		
		if($this->id and get_class($this)=="mRemoteConnection") $customer_id = $this->customer_id;
		
		$query = "select id from ".TBL_MREMOTE_CONNECTIONS." where parent_id=0";
		if($customer_id)
			$query.=" and customer_id=".$customer_id;
		//debug($query);
		$ids = db::db_fetch_vector($query);
		foreach ($ids as $id)
		{
			$pp = new mRemoteConnection($id);
			$pp->loadConnectionInfo();
			$ret[] = $pp; 
		}
		return $ret;
	}
	function loadConnectionInfo()
	{
		$this->connInfo = new mRemoteConnectionInfo($this->id);		
	}
	
	function getAllChildren()
	{
		$ret=array();
		if($this->has_children())
		{
			foreach($this->nodes  as $node)
			{
				$ret[] = $node;
				$ret = array_merge($ret, $node->getAllChildren());
			}
		}
		return $ret;
	}
	
	function getConnectionXML()
	{
		if($this->type == MREMOTE_CONNECTION_TYPE_CONTAINER)
		{
		$retXML = '
<Node ';
		}
		else
		{
		$retXML = '
    <Node ';
		}
		$retXML .= 'Name="'.trim(str_replace("&", "&amp;",$this->name)).'" ';
		$retXML .= 'Type="'.$GLOBALS['MREMOTE_CONNECTION_TYPES'][$this->type].'" ';
		
		//if we have a containter
		if($this->type == MREMOTE_CONNECTION_TYPE_CONTAINER)
		{
			$retXML.='Expanded="False" ';
			$retXML.= $this->connInfo->getConnInfoAttributesXML();
			$retXML.='>';
			foreach($this->nodes as $node)
			{
				$retXML.=$node->getConnectionXML();
			}			
			$retXML.="
</Node>";
		}
		if($this->type == MREMOTE_CONNECTION_TYPE_CONNECTION)
		{
			$retXML.= $this->connInfo->getConnInfoAttributesXML();
			$retXML.=' />';
		}
		return $retXML;
	}
	
	function getXMLHeader()
	{
		$retXML = '<?xml version="1.0" encoding="utf-8"?>
';
		$retXML.= '<Connections Name="Connections" Export="False" Protected="Rg9wALTiCc4P3D7IA0HJwXkD8h1gZ5tIvtGT3gA38rvX88fQ5Nimkdg6fbVJTicW" ConfVersion="1.4">';
		return $retXML;
	}
	
	function getXMLFooter()
	{
		$retXML = '
</Connections>';
		return $retXML;
	}
	
	function information_exists($name, $type, $parent_id, $customer_id, $protocol)
	{
		$qq = "SELECT mrc.id from ".TBL_MREMOTE_CONNECTIONS." mrc inner join ".TBL_MREMOTE_CONNECTION_INFO." mrci on mrc.id=mrci.id where mrc.name='".$name."' and  mrc.type=".$type." and mrc.parent_id=".$parent_id." and mrc.customer_id=".$customer_id." and mrci.protocol=".$protocol;				
		$exists = db::db_fetch_field($qq, 'id');
		return $exists;
	}
	
	/**
	 * [Class method]
	 * gets the public connections and populates the mRemote connections table
	 *
	 */
	function fetchPublicConnections()
	{
		class_load("Customer");
		//first get all the customers
		//first clear everything
		$query = "truncate table ".TBL_MREMOTE_CONNECTIONS;
		db::db_query($query);
		$query = "truncate table ".TBL_MREMOTE_CONNECTION_INFO;
		db::db_query($query);
		//$query="";
		
		$query = "select distinct id, name from ".TBL_CUSTOMERS." where active=1";
		$customers_list = db::db_fetch_list($query);
		
		debug($customers_list);
		
		foreach($customers_list as $cid=>$c_name)
		{
			//debug($c_name);
			//first we need to create a registration in the TBL_MREMOTE_CONNECTIONS
			
			$qq = "SELECT id from ".TBL_MREMOTE_CONNECTIONS." where name='".$c_name."' and  type=".MREMOTE_CONNECTION_TYPE_CONTAINER;
			$exists = db::db_fetch_field($qq, 'id');
			if(!$exists)
			{
				$conn = new mRemoteConnection();
				//we need to create the registration				
				$conn->parent_id = 0;
				$conn->name = $c_name;
				$conn->customer_id = $cid;
				$conn->type = MREMOTE_CONNECTION_TYPE_CONTAINER;
				$conn->save_data();
			}
			else 
				$conn = new mRemoteConnection($exists);
				
			$q = "select distinct remote_ip from ".TBL_COMPUTERS." where customer_id=".$cid." and remote_ip<>'' and remote_ip<>'0.0.0.0' and remote_ip not like '192.168.%' and remote_ip not like '172.16.%' and remote_ip not like '10.%'";
			$public_ips = db::db_fetch_vector($q);		
			//debug($public_ips);
			foreach($public_ips as $pip)
			{
				//debug($pip);
				//create a new connection for each of them
				$qq = "SELECT id from ".TBL_MREMOTE_CONNECTIONS." where name='".$pip."' and  type=".MREMOTE_CONNECTION_TYPE_CONTAINER." and parent_id=".$conn->id." and customer_id=".$cid;				
				$exists = db::db_fetch_field($qq, 'id');
				if(!$exists)
				{
					$conn1 = new mRemoteConnection();
					$conn1->parent_id = $conn->id;
					$conn1->name = $pip;
					$conn1->customer_id = $cid;
					$conn1->type = MREMOTE_CONNECTION_TYPE_CONTAINER;
					$conn1->save_data();
				}
				else {
					$conn1 = new mRemoteConnection($exists);
				}
				
				//now we should get the computers for each connection
				$q = "select id, profile_id, netbios_name from ".TBL_COMPUTERS." where remote_ip='".$pip."' and customer_id = ".$cid;
				$comps = db::db_fetch_array($q);
				
				//debug($comps);
				
				//here I have all the computers
				foreach($comps as $comp)
				{
					//debug($comp->netbios_name);
					$computer_services = ComputerRemoteService::get_services(array('computer_id' => $comp->id, 'by_computer' => true));
					//debug($computer_services);
					foreach($computer_services[$comp->id] as $c_srv)
					{				
						$name = $comp->netbios_name."_".$GLOBALS['MREMOTE_PROTOCOLS'][$c_srv->service_id];
						$sid = mRemoteConnection::information_exists($name, MREMOTE_CONNECTION_TYPE_CONNECTION, $conn1->id, $cid, $c_srv->service_id);
						if(!$sid)
						{
							//create a new connection
							$c = new mRemoteConnection();
							if($comp->profile_id==2 or $comp->profile_id==3)
								$c->machine_type = 2;
							else $c->machine_type = 1;
							
							$c->name = $name;
							$c->computer_id = $comp->id;
							$c->customer_id = $cid;
							$c->parent_id = $conn1->id;
							$c->type = MREMOTE_CONNECTION_TYPE_CONNECTION;
							$c->save_data();							
							$c->load_data();
							
							//debug($c->id);
							//$connInfo = new mRemoteConnectionInfo($c->id);
							
							//prepare the connInfo	
							$c->connInfo->protocol = $c_srv->service_id;
							if(!$c_srv->port) $c->connInfo->port = mRemoteConnectionInfo::get_default_port_for_protocol($c->connInfo->protocol);
							else $c->connInfo->port = $c_srv->port;
							$c->connInfo->hostname = $comp->netbios_name;
							$passwords = ComputerPassword::get_passwords(array('computer_id'=>$comp->id, 'by_computer'=>true));
							if(!empty($passwords))
							{
								$cp = $passwords[$comp->id][0];
								$c->connInfo->username = $cp->login;
								$c->connInfo->password = $cp->password;
							}
												
							$c->connInfo->save_data();
							$c->connInfo->load_data();
							//debug($c);
							//debug($connInfo);
							
						}													
					}
				}
			}
		}
	}
}