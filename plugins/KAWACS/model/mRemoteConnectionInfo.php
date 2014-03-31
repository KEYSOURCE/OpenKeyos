<?php

class mRemoteConnectionInfo extends Base
{
	var $id=null;
	var $descr = "";
	var $username = "";
	var $password = "";
	var $domain = "";
	var $hostname = "";
	var $protocol = null;
	var $port = 0;
	
	
	var $table = TBL_MREMOTE_CONNECTION_INFO;
	
	var $fields = array('id', 'protocol', 'port', 'username', 'password', 'domain', 'hostname');
	
	function mRemoteConnectionInfo($id=null)
	{
		if($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}
	function save_data()
	{
		if($this->id)
		{
			parent::save_data();
		}
	}
	function load_data()
	{
		$this->load_defaults();
		if($this->id)
		{
			if($this->record_exists())
				parent::load_data();
		}
	}
	
	function record_exists()
	{
		$query = "select id from ".TBL_MREMOTE_CONNECTION_INFO." where id=".$this->id;
		$id = $this->db_fetch_field($query, 'id');
		//debug($id);
		if($id) return true;
		else return false;
	}
	
	function load_defaults()
	{
		$this->descr = "";
		$this->username = "";
		$this->password = "";
		$this->domain = "";
		$this->hostname = "";
		if($this->protocol == null) $this->protocol = RDP;
		if($this->port == 0) $this->port = $GLOBALS['MREMOTE_PROTOCOLS_PORTS'][$this->protocol];		
	}
	function get_default_port_for_protocol($protocol)
	{
		return $GLOBALS['MREMOTE_PROTOCOLS_PORTS'][$protocol];
	}
	
	function getConnInfoAttributesXML()
	{
		$retXMLstr = '';
		$retXMLstr.= 'Descr="'.htmlentities($this->descr, ENT_QUOTES).'" ';
		$retXMLstr.= 'Icon="mRemote" Panel="General" ';
		$retXMLstr.= 'Username="'.htmlentities($this->username, ENT_QUOTES).'" '; 
		$retXMLstr.= 'Domain="'.htmlentities($this->domain, ENT_QUOTES).'" '; 
		$retXMLstr.= 'Password="'.md5($this->password).'" '; 
		$retXMLstr.= 'Hostname="'.$this->hostname.'" ';
		$retXMLstr.= 'Protocol="'.$GLOBALS['MREMOTE_PROTOCOLS'][$this->protocol].'" PuttySession="Default Settings" ';
		$retXMLstr.= 'Port="'.$this->port.'" ';		
		$retXMLstr.= 'ConnectToConsole="False" ICAEncryptionStrength="EncrBasic" RDPAuthenticationLevel="NoAuth" ';
		$retXMLstr.= 'Colors="Colors16Bit" Resolution="FitToWindow" DisplayWallpaper="False" '; 
		$retXMLstr.= 'DisplayThemes="False" CacheBitmaps="True" RedirectDiskDrives="False" ';  
		$retXMLstr.= 'RedirectPorts="False" RedirectPrinters="False" RedirectSmartCards="False" ';  
		$retXMLstr.= 'RedirectSound="DoNotPlay" RedirectKeys="False" Connected="False" '; 
		$retXMLstr.= 'PreExtApp="" PostExtApp="" VNCCompression="CompNone" VNCEncoding="EncHextile" ';  
		$retXMLstr.= 'VNCAuthMode="AuthVNC" VNCProxyType="ProxyNone" VNCProxyIP="" VNCProxyPort="0" ';  
		$retXMLstr.= 'VNCProxyUsername="" VNCProxyPassword="" VNCColors="ColNormal" VNCSmartSizeMode="SmartSAspect" '; 
		$retXMLstr.= 'VNCViewOnly="False" InheritCacheBitmaps="False" InheritColors="False" InheritDescription="False" ';  
		$retXMLstr.= 'InheritDisplayThemes="False" InheritDisplayWallpaper="False" InheritDomain="False" ';  
		$retXMLstr.= 'InheritIcon="False" InheritPanel="False" InheritPassword="False" InheritPort="False" ';  
		$retXMLstr.= 'InheritProtocol="False" InheritPuttySession="False" InheritRedirectDiskDrives="False" ';  
		$retXMLstr.= 'InheritRedirectKeys="False" InheritRedirectPorts="False" InheritRedirectPrinters="False" ';  
		$retXMLstr.= 'InheritRedirectSmartCards="False" InheritRedirectSound="False" InheritResolution="False" ';  
		$retXMLstr.= 'InheritUseConsoleSession="False" InheritUsername="False" InheritICAEncryptionStrength="False" ';  
		$retXMLstr.= 'InheritRDPAuthenticationLevel="False" InheritPreExtApp="False" InheritPostExtApp="False" ';  
		$retXMLstr.= 'InheritVNCCompression="False" InheritVNCEncoding="False" InheritVNCAuthMode="False" ';  
		$retXMLstr.= 'InheritVNCProxyType="False" InheritVNCProxyIP="False" InheritVNCProxyPort="False" ';  
		$retXMLstr.= 'InheritVNCProxyUsername="False" InheritVNCProxyPassword="False" InheritVNCColors="False" '; 
		$retXMLstr.= 'InheritVNCSmartSizeMode="False" InheritVNCViewOnly="False"';
		return $retXMLstr;
	}
}

?>