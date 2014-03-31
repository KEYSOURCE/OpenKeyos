<?php

/**
* Class for handling generic Ajax requests.
*
* Note that this is not the only entry point for Ajax requests. For more
* specialized operations, e.g. related to tickets, users etc. more entry
* points are defined in their respective classes.
*/


class AjaxDisplay extends BaseDisplay
{
	/** Constructor */
	function AjaxDisplay ()
	{
		parent::BaseDisplay ();
	}

	
	/**
	* Marks one or more notifications as being unread.
	* Such requests are sent from with Javascript/Ajax from various
	* pages showing notifications (e.g. Kawacs Console, Notifications)
	* and allows the user to mark certain notifications as being read.
	* This function will respond (in XML) giving the number of remaining
	* unread notifications.
	*/
	function mark_notifications_unread ()
	{
		check_auth ();
		class_load ('NotificationRecipient');
		
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		
		
		// Cleanup the received data and make sure there are only valid notification IDs
		$user_id = intval($this->vars['user_id']);
		$notifs_ids = array ();
		$notifs = array ();
		if (is_array ($this->vars['notif_id']))
		{
			foreach ($this->vars['notif_id'] as $id)
			{
				$id = intval ($id);
				$notif = new Notification ($id);
				if ($notif->id)
				{
					$notifs_ids[] = $id;
					$notifs[] = $notif;
				}
			}
		}
		
		// If we have proper user ID and notification IDs, mark them as unread
		if ($user_id and count($notifs_ids)>0)
		{
			foreach ($notifs as $notif) $notif->mark_read ($user_id);
			$xml.= 'ok';
			
			// Get the number of remaining unread notifications
			$unread_notifications = NotificationRecipient::get_unread_notifs_count ($user_id);
			
			$xml.= '<unread_notifs>'.$unread_notifications.'</unread_notifs>'; 
		}
		else $xml.= 'invalid_data';
		
		$xml.= '</result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		
		die;
	}
	
	function asset_change_category()
	{
		check_auth();
		class_load("Asset");
		class_load("AssetCategory");
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		
		$asset_id = intval($this->vars["id"]);
		$cat_id = $this->vars["cat_id"];
		$cat_name = $GLOBALS['KAMS_OBJ_CLASSES'][$cat_id-1];
		
		$managed = true;
		if($cat_id-1 == KAMS_OBJ_CLASS_GENERIC) $managed = false;
		$mm = $managed?'1':'0';
		$asset = new Asset($asset_id);
		
		if($asset) $assoc_list = $asset->get_assoc_list($cat_id);
		$xml .= '<managed>'.$mm.'</managed>';
		$xml .= '<category>'.$cat_id.'</category>';
		$xml .= '<category_name>'.$cat_name.'</category_name>';
		$xml .= '<assoc_list>';	
		if($assoc_list)
		{
			foreach ($assoc_list as $item)
			{
				$ke = array_keys($assoc_list, $item);
				$xml .= '<item>';
				$xml .= '<id>'.$ke[0].'</id>';
				$xml .= '<name>'.$item.'</name>';
				$xml .= '</item>';
			}
		}
			
		
		$xml.= '</assoc_list></result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		
	//	die;
	}
	
	
	function asset_change_category_add()
	{ 
		check_auth();
		class_load("Asset");
		class_load("AssetCategory");
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		$cat_id = $this->vars['cat_id'];
		$customer_id = $this->vars["customer"];
		$cat_name = $GLOBALS['KAMS_OBJ_CLASSES'][$cat_id-1];
		$managed = true;
		if($cat_id-1 == KAMS_OBJ_CLASS_GENERIC) $managed = false;
		$mm = $managed?'1':'0';
		$assoc_list = Asset::get_assoc_list($cat_id, $customer_id);
		
		$xml .= '<managed>'.$mm.'</managed>';
		$xml .= '<category>'.$cat_id.'</category>';
		$xml .= '<category_name>'.$cat_name.'</category_name>';
		$xml .= '<assoc_list>';	
		if($assoc_list)
		{
			foreach ($assoc_list as $item)
			{
				$ke = array_keys($assoc_list, $item);
				$xml .= '<item>';
				$xml .= '<id>'.$ke[0].'</id>';
				$xml .= '<name>'.$item.'</name>';
				$xml .= '</item>';
			}
		}
			
		
		$xml.= '</assoc_list></result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		
	}
	
	function financial_infos_change_currency()
	{
		check_auth();
		class_load("AssetFinancialInfo");
		$ret = "";
		$cid = $this->vars['currency'];
		if($cid)
			$ret = AssetFinancialInfo::get_currency_symbol($cid);
		echo $ret;
	}
	
	/**
	 * loads the characteristics of the specified contract type
	 * so the when adding or editing a contract, the user will be given 
	 * only the neecessary fields to complete
	 *
	 */
	function change_contract_type()
	{
		check_auth(array('type_id' => $this->vars['type_id']));
		class_load('ContractType');
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		$contract_type = new ContractType($this->vars['type_id']);
		if(!$contract_type->id)
		{
			$xml .= "<obb>No type</obb>";
		}
		else 
		{
			$xml .= "<obb>ok</obb>";
			$xml .= '<id>'.$contract_type->id.'</id>';
			$xml .= '<name>'.$contract_type->name.'</name>';
			$xml .= '<description>'.$contract_type->description.'</description>';
			$xml .= '<quantity>'.$contract_type->quantity.'</quantity>';
			$xml .= '<total_price>'.$contract_type->total_price.'</total_price>';
			$xml .= '<recurring_payments>'.$contract_type->recurring_payments.'</recurring_payments>';
			$xml .= '<end_date>'.$contract_type->end_date.'</end_date>';
			$xml .= '<vendor>'.$contract_type->vendor.'</vendor>';
			$xml .= '<supplier>'.$contract_type->supplier.'</supplier>';
			$xml .= '<is_warranty_contract>'.$contract_type->is_warranty_contract.'</is_warranty_contract>';
			$xml .= '<send_period_notifs>'.$contract_type->send_period_notifs.'</send_period_notifs>';
			$xml .= '<send_expiration_notifs>'.$contract_type->send_expiration_notifs.'</send_expiration_notifs>';
			$xml .= '<supports_renewals>'.$contract_type->supports_renewals.'</supports_renewals>';
		}
		$xml.= '</result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		
	}
	
	
	function ir_pdf_preview()
	{
		check_auth(array('intervention_id' => $this->vars['intervention_id']));
		class_load('InterventionReport');
		$xml_gen = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		$intervention = new InterventionReport ($this->vars['intervention_id']);
		//generate PDF document
		$xml_tpl = 'krifs/intervention.xml';
		$xsl_tpl = 'krifs/intervention.xslt';
		
		$customer = new Customer ($intervention->customer_id);
		$action_types = ActionType::get_list ();
		$locations_list = InterventionLocation::get_locations_list ();
		$intervention->load_tickets ();
		
		$filter['view'] = $this->vars['view'];
		$filter['show'] = $this->vars['show'];
		
		if(!$filter['show']) $filter['show'] = 'detailed';
		if(!$filter['view']) $filter['view'] = 'customer';
		
		// Calculate the times for each ticket
		for ($i=0; $i<count($intervention->details); $i++)
		{
			$detail = &$intervention->details[$i];
			$ticket = &$intervention->tickets[$detail->ticket_id];
			$ticket->work_time += $detail->work_time;
			$ticket->bill_time += $detail->bill_time;
			
			if ($detail->time_in)
			{
				if (!isset($ticket->time_in) or (isset($ticket->time_in) and $ticket->time_in > $detail->time_in))
					$ticket->time_in = $detail->time_in;
			}
			if ($detail->time_out)
			{
				if (!isset($ticket->time_out) or (isset($ticket->time_out) and $ticket->time_out < $detail->time_out))
					$ticket->time_out = $detail->time_out;
			}
		}
		
		$this->assign ('intervention', $intervention);
		$this->assign ('filter', $filter);
		$this->assign ('customer', $customer);
		$this->assign ('action_types', $action_types);
		$this->assign ('locations_list', $locations_list);
		$this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
		$this->assign('ACCOUNT_MANAGERS_LOGOS', $GLOBALS['ACCOUNT_MANAGERS_LOGOS']);
		$this->assign('ACCOUNT_MANAGERS_INFO', $GLOBALS['ACCOUNT_MANAGERS_INFO']);
		
		
		if (0) {header ('Content-type: text/xml');$this->display_template_only ($xml_tpl); die;}
		$xml = $this->fetch ($xml_tpl);

		//$pdf_name = 'report.pdf';
		$pdf_name = 'intervention_report_'.$intervention->id.'_'.$filter['show'].'_'.$filter['view'].'.pdf';
		//$pdf_name = 'intervention_report_'.$intervention->id.'.pdf';
		$pdf_file_path = make_pdf_xml ($xml, $xsl_tpl,"", true);
		
		//debug($pdf_file_path);
		$new_file_path = KEYOS_TEMP_FILE.'/'.$pdf_name;
		
		//rename($pdf_file_path, $new_file_path);
		exec('mv '.$pdf_file_path.' '.$new_file_path.' 2>&1', $cmd_res);
//		debug($cmd_res);
		
		if(!$new_file_path || !file_exists($new_file_path))
		{
			$xml_gen .= "<obb>No file</obb>";
		}
		else 
		{
			$xml_gen .= "<obb>ok</obb>";
			$xml_gen .= '<id>'.$intervention->id.'</id>';
			$xml_gen .= '<name>https://'.KEYOS_BASE_URL.'/tmp/'.$pdf_name.'</name>';
		}
		$xml_gen.= '</result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml_gen));
		echo $xml_gen;
	}
	
	function ir_multiplepdf_preview()
	{
		
		check_auth(array("length"=>$this->vars['len']));
		class_load('InterventionReport');
		$xml_gen = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		
		$paths = array();
		
		for($k=0; $k<$this->vars['len']; $k++)
		{
			$intervention = new InterventionReport ($this->vars['id'.$k]);
			//generate PDF document
			$xml_tpl = 'krifs/intervention.xml';
			$xsl_tpl = 'krifs/intervention.xslt';
		
			$customer = new Customer ($intervention->customer_id);
			$action_types = ActionType::get_list ();
			$locations_list = InterventionLocation::get_locations_list ();
			$intervention->load_tickets ();
		
			$filter['view'] = $this->vars['view'];
			$filter['show'] = $this->vars['show'];
		
			if(!$filter['show']) $filter['show'] = 'detailed';
			if(!$filter['view']) $filter['view'] = 'customer';
		
			// Calculate the times for each ticket
			for ($i=0; $i<count($intervention->details); $i++)
			{
				$detail = &$intervention->details[$i];
				$ticket = &$intervention->tickets[$detail->ticket_id];
				$ticket->work_time += $detail->work_time;
				$ticket->bill_time += $detail->bill_time;
			
				if ($detail->time_in)
				{
					if (!isset($ticket->time_in) or (isset($ticket->time_in) and $ticket->time_in > $detail->time_in))
						$ticket->time_in = $detail->time_in;
				}
				if ($detail->time_out)
				{
					if (!isset($ticket->time_out) or (isset($ticket->time_out) and $ticket->time_out < $detail->time_out))
						$ticket->time_out = $detail->time_out;
				}
			}
		
			$this->assign ('intervention', $intervention);
			$this->assign ('filter', $filter);
			$this->assign ('customer', $customer);
			$this->assign ('action_types', $action_types);
			$this->assign ('locations_list', $locations_list);
			$this->assign ('INTERVENTION_STATS', $GLOBALS['INTERVENTION_STATS']);
			$this->assign('ACCOUNT_MANAGERS_LOGOS', $GLOBALS['ACCOUNT_MANAGERS_LOGOS']);
			$this->assign('ACCOUNT_MANAGERS_INFO', $GLOBALS['ACCOUNT_MANAGERS_INFO']);
			
		
			if (0) {header ('Content-type: text/xml');$this->display_template_only ($xml_tpl); die;}
			$xml = $this->fetch ($xml_tpl);

			//$pdf_name = 'report.pdf';
			$pdf_name = 'intervention_report_'.$intervention->id.'_'.$filter['show'].'_'.$filter['view'].'.pdf';
			$pdf_file_path = make_pdf_xml ($xml, $xsl_tpl,"", true);
		
			//debug($pdf_file_path);
			$new_file_path = KEYOS_TEMP_FILE.'/'.$pdf_name;
			//rename($pdf_file_path, $new_file_path);
			exec('mv '.$pdf_file_path.' '.$new_file_path.' 2>&1');
	
			if(!$new_file_path || !file_exists($new_file_path))
			{
				$xml_gen .= "<obb>No file</obb>";
			}
			else 
			{
				$xml_gen .= "<obb>ok</obb>";
				$xml_gen .= '<id>'.$intervention->id.'</id>';
				$xml_gen .= '<name>https://'.KEYOS_BASE_URL.'/tmp/'.$pdf_name.'</name>';
				$path[$k] = $new_file_path;
			}
		}
		if(count($path) < $this->vars['len'])
		{
			$xml_gen = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
			$xml_gen .= 'ERROR';
		}
		else 
		{
			//here we link all the generated pdfs in one big file
			$merged_pdf = tempnam(KEYOS_TEMP_FILE, 'merged_');
			@unlink($merged_pdf);
			$merged_pdf .= ".pdf";
			$cmd = "gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=".$merged_pdf." ";
			for($i=0; $i<count($path); $i++)
			{	
				$cmd .= $path[$i]." ";
			}
			unset($cmd_res);
			exec($cmd, $cmd_res, $error);
			if($error)
			{
				$xml_gen = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
				$xml_gen .= 'xERROR '.$cmd;
			}
			else 
			{
				$xml_gen = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
				$xml_gen .= "<obb>ok</obb>";
				$xml_gen .= '<id>merged</id>';
				$file = basename($merged_pdf);
				$xml_gen .= '<name>https://'.KEYOS_BASE_URL.'/tmp/'.$file.'</name>';
				for($i=0; $i<count($path); $i++)
				{	
					@unlink($path[$i]);
				}
			}
			 
		}
		$xml_gen.= '</result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml_gen));
		echo $xml_gen;
	}
	
	function get_new_ir_page()
	{
		check_auth(array('customer_id' => $this->vars['customer_id']));
		class_load('InterventionReport');
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		
		$filt['customer_id'] = $this->vars['customer_id'];
		$filt['start'] = $this->vars['start'];
		$filt['limit'] = $this->vars['limit'];
		$tot_intervent = 0;
		$interventions = InterventionReport::get_interventions($filt, $tot_intervent);
		if($filt['start'] > $tot_intervent)
		{
			$filt['start'] = 0;
			$interventions = InterventionReport::get_interventions($filt, $tot_intervent);
		}
		$xml .= "<obb>ok</obb>";
		$xml .= "<interventions>";
		for($i=0; $i<count($interventions); $i++)
		{
			$xml .= "<intervention>";
			$xml .= "<int_id>".$interventions[$i]->id."</int_id>";
			$xml .= "<int_subject>".$interventions[$i]->subject."</int_subject>";
			$xml .= "<int_status>".$interventions[$i]->status."</int_status>";
			$xml .= "<int_created>".$interventions[$i]->created."</int_created>";
			$xml .= "<int_work_time>".$interventions[$i]->work_time."</int_work_time>";
			$xml .= "<int_bill_amount>".$interventions[$i]->bill_amount."</int_bill_amount>";
			$xml .= "<int_tbb_amount>".$interventions[$i]->tbb_amount."</int_tbb_amount>";
			$xml .= "<tickets>";
			$interventions[$i]->load_tickets();
			foreach($interventions[$i]->tickets as $ticket)
			{
				$xml .= "<ticket_id>".$ticket->id."</ticket_id>";	
				$xml .= "<ticket_subject>".$ticket->subject."</ticket_subject>";	
			}
			$xml .= "</tickets>";
			$xml .= "</intervention>";
		}
		$xml .= "</interventions>";
		
		
		$xml .= '</result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		
		//die;
	}
	
	function generate_graph()
	{
		$image_name = $this->vars['image'];
		$title = $this->vars['title'];
		$cleg = $this->vars['cleg'];
		$legend = array();
		$r = $this->vars['r']; $o = $this->vars['o']; $g = $this->vars['g']; $gr = $this->vars['gr'];
		$percents = array($r,$o,$g,$gr);
		
		for($i=0;$i<$cleg;$i++)
		{
			//debug($this->vars['leg'.$i]." - ".round($percents[$i],1)."%");
			$legend[$i] = $this->vars['leg'.$i]." - ".round($percents[$i],1)."%";
		}
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
		$data = array(0 => array("Backup statuses", $r,$o,$g,$gr));
		//Required Settings
	    include(KEYOS_EXTERNAL."/phplot/phplot.php");
	    $graph = new PHPlot(600, 400);
	    $graph->SetDataType('text-data');  // Must be first thing
	
	    //print_r($data);
	    
	    $graph->SetDataValues($data);
		
	    $graph->SetTitle($title);
	    $graph->SetLegend($legend);
	
	    $graph->SetFileFormat("jpg");
	    $graph->SetPlotType("pie");
		
	    $output_file = tempnam(KEYOS_TEMP_FILE, "_gen_graph_");
	    @unlink($output_file);
	    $output_file .= ".jpg";
	    $graph->output_file = $output_file;
	    
	    $graph->SetYTickIncrement(1);
	    $graph->SetXTickIncrement(1);
	    $graph->SetXTickLength(1);
	    $graph->SetYTickLength(1);
	    $graph->SetXTickCrossing(1);
	    $graph->SetYTickCrossing(1);
	
	
	    $graph->SetShading(5);
	    $graph->SetLineWidth(1);
	    $graph->SetErrorBarLineWidth(1);
	    $graph->SetDataColors(
	            array("red","orange","green","gray"),   //Data Colors
	            array("black")                          //Border Colors
	    );
	    $graph->DrawGraph();
	    
	    
	    if(is_file($output_file) and file_exists($output_file))
	    {
	    	if($image_name!="")
			{	
				$xml .= "<obb>ok</obb>";
				$xml .= "<image>".$image_name."</image>";
			}
			else 
			{
				$xml .= "<obb>not ok</obb>";
			}
			$xml .= "<outfile>".KEYOS_BASE_URL.'/tmp/'.basename($output_file)."</outfile>";
	    }
	    else 
	    {
	    	$xml .= "<obb>not ok</obb>";
	    }
		   
		$xml .= '</result>';
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
	}
	
	function get_kb_subcategories()
	{
		check_auth(array('pcat'=>$this->vars['pcat']));
		class_load("KBCategory");
		
		$parent = new KBCategory($this->vars['pcat']);
		$subcats = KBCategory::getCategoriesList($this->vars['pcat']);
		
		$xml = "<result>";
		$xml.= "<parent_id>".$parent->id."</parent_id><parent_title>".$parent->hasTitle."</parent_title>";
		foreach($subcats as $key=>$v)
		{
			//construct the xml list
			$xml .= "<item><id>".$key."</id><title>".$v."</title></item>";
		}
		$xml .= "</result>";
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		//debug($xml);
		die;
		
	}
	
	function get_kb_subarticles()
	{
		check_auth(array('part'=>$this->vars['part']));
		class_load("KBArticle");
		class_load("KBCategory");
		
		$parent = new KBArticle($this->vars['part']);
		$cat = new KBCategory($parent->category);
		$subarts = $cat->get_articles_list($parent->id);
		
		$xml = "<result>";
		$xml.= "<parent_id>".$parent->id."</parent_id><parent_title>".$parent->hasTitle."</parent_title>";
		foreach($subarts as $key=>$v)
		{
			//construct the xml list
			$xml .= "<item><id>".$key."</id><title>".$v."</title></item>";
		}
		$xml .= "</result>";
		header ('Content-Type: text/xml');
		header ('Content-length: '+strlen($xml));
		echo $xml;
		//debug($xml);
		die;
		
	}
    
    function search_customers(){
        check_auth(array('f_filter' => $this->vars['f_filter'], 'dn' => $this->vars['dn']));
        class_load("Customer");
        $customers = Customer::search_customer($this->vars['f_filter']);
        $xml  = '<?xml version="1.0" encoding="ISO-8859-1" ?><result>';
        $xml .= "<status>ok</status>";
        $xml .= "<dn>".$this->vars['dn']."</dn>" ;
        foreach($customers as $customer){
            $xml .= "<customer>";
            $xml .= "<id>".$customer->id."</id>";
            $xml .= "<name>".$customer->name."</name>";
            $xml .= "<erp_id>".$customer->erp_id."</erp_id>";
            $xml .= "</customer>";
        }
        $xml .= "</result>";
        header('Content-Type: text/xml');
        header('Content-length: '+strlen($xml));
        echo $xml;
        die;
    }	
}

?>
