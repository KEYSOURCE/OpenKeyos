<?php
/*
 * Created on Oct 24, 2009
 * SOAP server for syncronizing data with the APISOFT GESTIONEXPERT ERP
 *
 * @package erp
 * @subpackage apisoft_sync_server
 *
 */

 class_load('nusoap');

 class ApisoftSync extends soap_server
 {
 	function ApisoftSync()
 	{
 		$this->configureWSDL('apisoftsync','urn:apisoftsync');
 		$this->wsdl->schemaTargetNamespace = "urn:apisoftsync";

 		/**
 		 * definition of the types needed for cummunicating witht the agents
 		 */

 		  $this->wsdl->addComplexType('Customer',
 		  			'complexType',
 		  			'struct',
 		  			'all',
 		  			'',
 		  			array(
							'code' => array('name'=>'code', 'type'=>'xsd:string'),
							'name' => array('name'=>'name', 'type'=>'xsd:string'),
							'civilite' => array('name'=>'civilite', 'type'=>'xsd:string'),
							'addr' => array('name'=>'addr', 'type'=>'xsd:string'),
							'suite_addr' => array('name' => 'suite_addr', 'type' => 'xsd:string'),
							'code_postal' => array('name' => 'code_postal', 'type' => 'xsd:string'),
							'ville' => array('name'=>'ville', 'type'=>'xsd:string'),
							'pays' => array('name'=>'pays', 'type'=>'xsd:string'),
							'tel' => array('name' => 'tel', 'type'=>'xsd:string'),
							'fax' => array('name' => 'fax', 'type' => 'xsd:string'),
							'email' => array('name' => 'email', 'type' => 'xsd:string'),
							'tarif' => array('name'=>'tarif', 'type'=>'xsd:int')
 		  			)
 		  );
 		  $this->wsdl->addComplexType('Activity',
 		  			'complexType',
 		  			'struct',
 		  			'all',
 		  			'',
 		  			array(
 		  					'code' => array('name'=>'id', 'type'=>'xsd:string'),
 		  					'erp_code' => array('name'=>"erp_code", "type"=>"xsd:string"),
 		  					"category_id" => array("name"=>"category_id", "type"=>"xsd:string"),
 		  					"erp_category_id" => array("name" => "erp_category_id", "type" => "xsd:string"),
 		  					"name" => array("name"=>"name", "type"=>"xsd:string"),
 		  					"erp_name" => array("name"=>"erp_name", "type"=>"xsd:string"),
 		  					"is_travel" => array("name"=>"is_travel", "type" => "xsd:int") // I think this will be 0 or 1
 		  			)
 		  );
 		  $this->wsdl->addComplexType('ActivityCategory',
 		  			'complexType',
 		  			'struct',
 		  			'all',
 		  			'',
 		  			array(
 		  				"code" => array('name'=>'code', 'type'=>'xsd:string'),
 		  				"erp_code" => array('name'=>'erp_code', "type"=>'xsd:string'),
 		  				'name' => array('name' =>"name", "type"=>'xsd:string'),
 		  				'erp_name' => array("name" => "erp_name", "type"=>'xsd:string')
 		  			)
 		  );

 		  $this->wsdl->addComplexType('ActCategoryList',
 		  			'complexType',
 		  			'array',
 		  			'',
 		  			'SOAP-ENC:Array',
 		  			array(),
 		  			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:ActivityCategory[]')),
 		  			'tns:ActivityCategory'
 		  );
 		  $this->wsdl->addComplexType('ActivityList',
 		  			'complexType',
 		  			'array',
 		  			'',
 		  			'SOAP-ENC:Array',
 		  			array(),
 		  			array(array('ref'=>'SOAP-ENC:arrayType', 'wsdl:arrayType'=>'tns:Activity[]')),
 		  			'tns:Activity'
 		  );

 		  $this->wsdl->addComplexType('CustomersList',
 		  			'complexType',
 		  			'array',
 		  			'',
					'SOAP-ENC:Array',
					array(),
					array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Customer[]')),
					'tns:Customer'
 		  );

 		  /**
 		   * register the communication methods
		   */
 		   $this->register(
 		   			'sendCustomers',																						//method name
 		   			array('customers_list' => 'tns:CustomersList'),													//input parameters
 		   			//array('new_customers' => 'tns:CustomersList'),												//output parametrs
 		   			array('resp' => 'xsd:string'),
 		   			'urn:apisoftsync',																							//namespace
 		   			'urn:apisoftsync#sendCustomers',																	//soapaction
 		   			'rpc',																											//style
 		   			'encoded',																									//use
 		   			"the apisoft agent sends it's list of customers that keyos adds or updates, and receives the new customers in keyos to update"
 		   );

 		   $this->register(
 		   			'sendActivities',
 		   			array("erp_activ_list"=>"tns:ActivityList", "erp_act_cat_list"=>"tns:ActCategoryList"), //the list of the activities to syncronize from apisoft
 		   			array("activ_list"=>"tns:ActivityList", "act_cat_list"=>"tns:ActCategoryList"),
 		   			'urn:apisoftSync',
 		   			'urn:apisoftsync#sendActivities',
 		   			'rpc',
 		   			'encoded',
 		   			"the apisoft agent sends a list of activities and the categories involved that keyos  adds or updates and keyos sends it's own list"
 		   );

 		    $this->register(
 		    	'testConnection',
 		    	array('rec' => 'xsd:string'),
 		    	array('ret' => 'xsd:string'),
 		    	'urn:apisoftsync',
 		    	'urn:apisoftsync#testConnection',
 		    	'rpc',
 		    	'encoded',
 		    	'just a test function'
 		    );
 	}
 }

 /**
  * the apisoft agent sends it's list of customers that keyos adds or updates, and receives the new customers in keyos to update
  */
 function sendCustomers($customers_list = array())
 {
     //here we get the customers list from the apisoft, and we add or update in keyos
     //fetch the list of customers managed by MPI that were not affected by previous sycronization and send them back to the agent
     //the agent gets the list and inserts in the gestc.mdb

     class_load('Customer');
     $resp = "";
     $query = "select erp_id, id from ".TBL_CUSTOMERS." where account_manager=".ACCOUNT_MANAGER_MPI;
     $ids = db::db_fetch_list($query);
     $apis_ids = array();

     foreach($customers_list as $aps_cust)
     {
          //add the new customers
          if(!in_array($aps_cust['code'], array_keys($ids)))
          {
            //this one is new => create
            $c = new Customer();
            $c->name = $aps_cust['name'];
            $c->erp_id = $aps_cust['code'];
            $c->ERP_Name = $aps_cust['name'];
            $c->Address_I_1 = $aps_cust['addr'];
            $c->Address_I_2 = $aps_cust['suite_addr'];
            $c->ZIP_I = $aps_cust['code_postal'];
            $c->Locality_I = $aps_cust['ville'];
            $c->Country_D = 7;
            $c->Telephone = $aps_cust['tel'];
            $c->Fax = $aps_cust['fax'];
            $c->EMail = $aps_cust['email'];
            $c->price_type = $aps_cust['tarif'];
            $c->Language = 'fr';
            $c->account_manager = ACCOUNT_MANAGER_MPI;
            $c->save_data();
          }

	  //modify the ones that need modifying
	  //1. get the customer by erp_id
	  else
	  {
	     $cid = $ids[$aps_cust['code']];
	     $c = new Customer($cid);
	     if($c->id)
	     {
		     //this seemd to be valid
		     //get the reported data and update
		     $c->name = $aps_cust['name'];
		     $c->erp_id = $aps_cust['code'];
		     $c->ERP_Name = $aps_cust['name'];
		     $c->Address_I_1 = $aps_cust['addr'];
		     $c->Address_I_2 = $aps_cust['suite_addr'];
		     $c->ZIP_I = $aps_cust['code_postal'];
		     $c->Locality_I = $aps_cust['ville'];
		     $c->Telephone = $aps_cust['tel'];
		     $c->Fax = $aps_cust['fax'];
		     $c->EMail = $aps_cust['email'];
		     $c->price_type = $aps_cust['tarif'];
		     $c->account_manager = ACCOUNT_MANAGER_MPI;
		     $c->save_data();
	     }
	  }

     }

     return $resp;
 }

function sendActivities($erp_activities_list = array(), $erp_act_cat_list = array())
{
	class_load("ActionType");
	class_load("ActionTypeCategory");
	$ret = array();
	//TODO: we don't have to update activities that allready have an erp code assigned from the MERCATOR
	//MOD: alter table activities_categories add erp_code varchar(30) default '';
	$act_cat = ActionTypeCategory::get_categories();
	$activities = ActionType::get_actions();

	$cat_list = array();
	foreach($act_cat as $ac)
	{
		$cat_list[$ac->id] = $ac->erp_id;
	}

	$test_file = dirname(__FILE__).'/../../logs/testlog_sendActivities';
        $fp = @fopen ($test_file, 'w');
        if ($fp)
        {
	      fwrite($fp, "\n======= ".$_SERVER['REMOTE_ADDR'].($_SERVER['HTTPS']=='on'?' HTTPS':' HTTP').': '.date('Y-m-d H:i:s')." ======================\nReceived Data:\n\n");
	      ob_start();

	foreach($erp_act_cat_list as $eac)
	{
		if(in_array($eac['erp_code'], $cat_list))
		{
		        print_r("found category: ".$eac['erp_code']." Update....\n");
			//we update here
			foreach($act_cat as $ac)
			{
				if($ac->erp_id == $eac['erp_code'])
				{
					if($ac->erp_id != $eac['erp_code'])
					{
						$cat = new ActionTypeCategory($ac->id);
						print_r("Updating cat id: ".$cat->id." .....\n");
						$cat->name = $eac['erp_name'];
						$cat->erp_id = $eac['erp_code'];

						if($cat->is_valid_data())
						{
							$cat->save_data();
					        }
						else
							print_r('item validity: '.error_msg().'\n');

					}
				}
			}
		}
		else
		{
		        print_r("Insering cat erp_id: ".$eac['erp_code']." .....\n");
			//this one is defined in the erp so we should create this category
			$cat = new ActionTypeCategory();
			$cat->name = $eac['erp_name'];
			$cat->erp_id = $eac['erp_code'];
			if($cat->is_valid_data())
			{
			    $cat->save_data();
			}
			else
			    print_r('item validity: '.error_msg().'\n');
		}
	}

	//now update the activities
	$act_list = array();
	$act_list_names = array();
	foreach($act_list as $act)
	{
		$act_list[$act->id] = $act->erp_code;
		$act_list_names[$act->id] = $act->name;
	}
	foreach($erp_activities_list as $ea)
	{
		if(in_array($ea['erp_code'], $act_list))
		{
		        print_r("Found item erp_code: ".$ea['erp_code']." Updating.....\n");
			//we found it, now update the information -- do not update the erp_id -- might be from mercator and the category
			foreach($activities as $activ)
			{
				if($activ->erp_id == $ea["erp_code"] and $activ->erp_code== $ea["erp_code"])
				{
					//we have it ... update the name

					$a = new ActionType($activ->id);
					print_r("Updating item ".$a->id.".. \n");
					//$a->name = $ea['erp_name'];	//only the erp_name... maybe there was a name modified in keyos
					$a->erp_nam = $ea['erp_name'];
					if($a->category == null and $ea['erp_category_id'] != "")
					{
						//now we update the category_id
						$ecat = $ea['erp_category_id'];
						$cat_id = ActionTypeCategory::get_categ_by_erp_code($ecat);
						if($cat_id)
						{
							$a->category = $cat_id;
						}
					}
					if($a->is_valid_data())
					{
					  $a->save_data();
					}
					else
					  print_r('item validity: '.error_msg().'\n');
				}
			}
		}
		 else
		{
			//this is new just add... but first get the category ... this should be already set.. if is not skip
		     $ecat = $ea['erp_category_id'];
		     if($ecat!="")
			     $cat_id = ActionTypeCategory::get_categ_by_erp_code($ecat);
		     else
			     $cat_id = 0;
		     //we must have a category set...
		     print_r("Category: ".$ecat.";  ".$cat_id."\n");
		     if($cat_id!=0)
		     {
			    $a = new ActionType();
			    print_r("New item add ".$ea['erp_code']);
			    $a->name = $ea['erp_name'];
			    $a->category = $cat_id;
			    $a->erp_id = $ea['erp_code'];
			    $a->erp_code = $ea['erp_code'];
			    $a->erp_nam = $ea['erp_name'];
			    $a->special_type=0;
			    $a->price_type = 1;
			    $a->billable = 1;
			    $a->billing_unit = 60;
			    $a->contract_types = 4;
			    $a->contract_sub_type = 11;
			    if($a->is_valid_data())
			    {
				   $a->save_data();
			    }
			    else
				   print_r('item validity: '.error_msg().'\n');
		     }
		}
	}

	fwrite($fp, ob_get_contents());
        ob_end_clean();
        fclose($fp);

	}

	//now we have to send what keyos has
	//get the activities and categories
	$activities = ActionType::get_actions();
	$involved_cat_ids = array();	//here we store all the categories ids invloved
	$ks_activ = array();
	$ks_activ_cat = array();
	foreach($activities as $activ)
	{
		$cat_id = $activ->category;
		if(!in_array($cat_id, $involved_cat_ids))
		{
			//not found add it
			$involved_cat_ids[] = $cat_id;
		}
		if($activ->id)
		{
			$ks_activ[] = array(
					'code' => $activ->id,
					'erp_code' => $activ->erp_code,
					'category_id' => $activ->category,
					'erp_category_id' => "",
					'name' => $activ->name,
					'erp_name' => $activ->erp_nam,
					'is_travel' => 0
			);
		}
	}
	foreach($involved_cat_ids as $cat_id)
	{
		$acat = new ActionTypeCategory($cat_id);
		if($acat->id)
		{
			$ks_activ_cat[] = array(
					'code' => $acat->id,
					'erp_code' => $acat->erp_id,
					'name' => $acat->name,
					'erp_name' => $acat->name
			);
		}
	}

	$ret[0] = $ks_activ; $ret[1] = $ks_activ_cat;
	return $ret;
}


 function testConnection($rec = '')
 {
       class_load('ActionTypeCategory');
       $cat_id = ActionTypeCategory::get_categ_by_erp_code("KS-PREST.");
       $ret = "tttt: ".$cat_id;
       return $ret;
 }

?>
