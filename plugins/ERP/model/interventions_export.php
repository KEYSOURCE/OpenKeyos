<?php

class_load ('InterventionReport');

/**
* Class for managing export batches of intervention reports
*
* Every time a request comes from ERP to fetch a new batch of intervention reports,
* it should be first checked with the class method has_export() if there are any
* interventions ready to be exported and only after that, if there are, a new export
* object should be created.
*/

class InterventionsExport extends Base
{
	/** Export ID
	* @var int */
	var $id = null;

	/** The date when the export was created
	* @var int */
	var $created = 0;

	/** The status of the export - see $GLOBALS['INTERVENTIONS_EXPORTS_STATS']
	* @var int */
	var $status = INTERVENTION_EXPORT_STAT_NEW;

	/** The number of intervention reports exported (used for control)
	* @var int */
	var $cnt_interventions = 0;

	/** The total of TBB time in all interventions (used for control)
	* @var int */
	var $tbb_sum = 0;

	/** The MD5 checksum of the exported XML file
	* @var string */
	var $md5_file = '';

	/** The IP address of the computer from which the export was requested
	* @var string */
	var $requester_ip = '';


	/** Array with the IDs of the intervention reports which were included in this export
	* @var array */
	var $interventions_ids = array ();

	/** Array with the actions/confirmation received for this export
	* @var array */
	var $actions = array ();


	var $table = TBL_INTERVENTIONS_EXPORTS;
	var $fields = array ('id', 'created', 'status', 'cnt_interventions', 'tbb_sum', 'md5_file', 'requester_ip');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function InterventionsExport ($id = null)
	{
		if ($id)
		{
			$this->id = $id;
			$this->load_data();
		}
	}

	function load_data ()
	{
		parent::load_data ();
		if ($this->id)
		{
			// Load the list of actions for this export
			$q = 'SELECT * from '.TBL_INTERVENTIONS_EXPORTS_ACTIONS.' WHERE export_id='.$this->id.' ORDER BY created DESC ';
			$this->actions = $this->db_fetch_array ($q);

			// Load the list of timesheet IDs for this export
			$q = 'SELECT intervention_id FROM '.TBL_INTERVENTIONS_EXPORTS_IDS.' WHERE export_id='.$this->id.' ORDER BY intervention_id ';
			$this->interventions_ids = $this->db_fetch_vector ($q);
		}
	}

	/** Saves the object data */
	function save_data ()
	{
		if (!$this->created) $this->created = time ();
		parent::save_data ();

		if ($this->id)
		{
			// Save the list of intervention report IDs
			$this->db_query ('DELETE FROM '.TBL_INTERVENTIONS_EXPORTS_IDS.' WHERE export_id='.$this->id);
			foreach ($this->interventions_ids as $ir_id)
			{
				$q = 'INSERT INTO '.TBL_INTERVENTIONS_EXPORTS_IDS.'(export_id,intervention_id) VALUES ';
				$q.= '('.$this->id.','.$ir_id.')';
				$this->db_query ($q);
			}
		}
	}


	/** Called when an export request comes from ERP, this method generates the XML export batch, marks the intervention
	* reports as being "pending" and updates the status of the export. Note that this function will also save the
	* object data.
	* @param	string		$requester_ip		The IP address from which the export was requested.
	* @return	bool					True or False if the exporting has bee done.
	*/
	function make_export ($manager=DEFAULT_ACCOUNT_MANAGER, $requester_ip = '')
	{
		class_load ('InterventionLocation');

		$ret = false;
		if ($this->id)
		{
			// Fetch the list of intervention reports waiting to be exported
			$filter = array ('status' => INTERVENTION_STAT_PENDING_CENTRALIZE);
			$interventions = InterventionReport::get_interventions ($manager, $filter, $no_count);
			$filter = array ('status' => INTERVENTION_STAT_APPROVED);
			$interventions = array_merge($interventions, InterventionReport::get_interventions ($manager, $filter, $no_count));
			$total_tbb_hours = 0;		// Will add hours, not minutes;

			$this->interventions_ids = array ();
			for ($i=0; $i<count($interventions); $i++)
			{
				$intervention = &$interventions[$i];
				$this->interventions_ids[] = $intervention->id;
				$intervention->customer = new Customer ($intervention->customer_id);
				for ($j=0; $j<count($intervention->lines); $j++) $total_tbb_hours+= $intervention->lines[$j]->get_tbb_amount_hours();

				// Mark the interventions as being in process of being "centralized" by the ERP system
				$intervention->status = INTERVENTION_STAT_PENDING_CENTRALIZE;
				$intervention->save_data ();
			}

			$this->requester_ip = $requester_ip;
			$this->tbb_sum = $total_tbb_hours;
			$this->cnt_interventions = count($interventions);

			$parser = new BaseDisplay ();
			$xml_tpl = 'erp/interventions_export.xml';

			$parser->assign ('export', $this);
			$parser->assign ('interventions', $interventions);
			$parser->assign ('base_url', 'http://'.get_base_url());
			$parser->assign ('locations_list', InterventionLocation::get_locations_list ());

			$xml = $parser->fetch ($xml_tpl);

			// Save the XML data into a file
			$fw = @fopen($this->get_fname(), 'w');
			if ($fw)
			{
				fwrite ($fw, $xml);
				fclose ($fw);
			}

			// Calculate the MD5 sum of the file
			//$this->md5_file = md5_file ($this->get_fname());
			$this->md5_file = md5 ($xml);
			$this->status = INTERVENTION_EXPORT_STAT_SENT;
			$this->save_data ();

			$ret = true;
		}

		return $ret;
	}


	/** Called when the ERP wants to communicate the MD5 of the downloaded file */
	function is_file_confirmation_ok ($md5_sum, $request_url, $remote_ip)
	{
		$ret = false;
		if ($this->id)
		{
			$q = "INSERT INTO ".TBL_INTERVENTIONS_EXPORTS_ACTIONS."(export_id, created, request_url, requester_ip) ";
			$q.= "VALUES ($this->id, ".time().", '".mysql_escape_string ($request_url)."', '".mysql_escape_string($remote_ip)."') ";
			$this->db_query ($q);

			if (strtolower($this->md5_file) == strtolower($md5_sum))
			{
				$this->status = INTERVENTION_EXPORT_STAT_FILE_CONFIRMED;
				$ret = true;
			}

			$this->save_data ();
		}

		return $ret;
	}


	/** Called when the ERP wants to communicate import results. If confirmation values are OK,
	* then it will also mark all the linked IR as "Centralized" */
	function is_import_confirmation_ok ($cnt_interventions, $tbb_sum, $request_url, $remote_ip)
	{
		$ret = false;
		if ($this->id)
		{
			$q = "INSERT INTO ".TBL_INTERVENTIONS_EXPORTS_ACTIONS." (export_id, created, request_url, requester_ip) ";
			$q.= "VALUES ($this->id, ".time().", '".mysql_escape_string ($request_url)."', '".mysql_escape_string($remote_ip)."') ";
			$this->db_query ($q);

			if ($this->cnt_interventions == $cnt_interventions and round($this->tbb_sum,2) == round($tbb_sum,2))
			{
				$this->status = INTERVENTION_EXPORT_STAT_IMPORT_CONFIRMED;
				$ret = true;

				// Mark the intervention reports as "Centralized"
				foreach ($this->interventions_ids as $id)
				{
					$ir = new InterventionReport ($id);
					$ir->status = INTERVENTION_STAT_CENTRALIZED;
					$ir->save_data ();
				}
			}

			$this->save_data ();
		}

		return $ret;
	}


	/** Returns the full path and fname of the XML file for this export */
	function get_fname ()
	{
		return DIR_EXPORT_XML_INTERVENTIONS.'/'.FILE_PREFIX_EXPORT_XML_INTERVENTIONS.$this->id.'.xml';
	}


	/** Sends directly to the browser (but without setting anything in the header) the XML file */
	function serve_file ()
	{
		if ($this->id)
		{
			readfile ($this->get_fname());
		}
	}


	function get_confirmation_file_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$parser = new BaseDisplay ();
			$ret = $parser->mk_redir ('interventions_confirm_file', array('id' => $this->id, 'md5' => ''), 'erp');
		}
		return $ret;
	}


	function get_confirmation_import_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$parser = new BaseDisplay ();
			$ret = $parser->mk_redir ('interventions_confirm_import', array('id' => $this->id), 'erp');
		}
		return $ret;
	}


	function get_retransfer_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$parser = new BaseDisplay ();
			$ret = $parser->mk_redir ('interventions_retransfer', array('id' => $this->id), 'erp');
		}
		return $ret;
	}


	/** [Class Method] Tells if there are any interventions waiting to be exported
	* @param	bool					True or false if there are any intervention reports ready
	*							to be exported.
	*/
	function has_export ($manager = DEFAULT_ACCOUNT_MANAGER)
	{
		$ret = false;
		$q = 'SELECT ir.id FROM '.TBL_INTERVENTION_REPORTS.' ir inner join '.TBL_CUSTOMERS.' c on c.id=ir.customer_id WHERE (status='.INTERVENTION_STAT_APPROVED.' or status='.INTERVENTION_STAT_PENDING_CENTRALIZE.') and c.account_manager='.$manager.' LIMIT 1';
		$id = DB::db_fetch_field ($q, 'id');
		if ($id) $ret = true;

		return $ret;
	}


	function get_exports ($manager= DEFAULT_ACCOUNT_MANAGER, $filter = array ())
	{
		$q = 'SELECT distinct ie.id FROM '.TBL_INTERVENTIONS_EXPORTS.' ie ';
		$q.= 'INNER JOIN '.TBL_INTERVENTIONS_EXPORTS_IDS.' iei on ie.id=iei.export_id ';
		$q.= 'INNER JOIN '.TBL_INTERVENTION_REPORTS.' ir on iei.intervention_id=ir.id ';
		$q.= 'INNER JOIN '.TBL_CUSTOMERS.' c on c.id=ir.customer_id WHERE ';

		if (isset($filter['get_peding']))
		{
			$q.= '(ie.status='.INTERVENTION_EXPORT_STAT_REQUESTED.' OR ie.status='.INTERVENTION_EXPORT_STAT_SENT.' OR ';
			$q.= 'ie.status='.INTERVENTION_EXPORT_STAT_FILE_CONFIRMED.') AND ';
		}
		$q.=' c.account_manager='.$manager.' AND ';

		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);

		$q.= 'ORDER BY ie.created DESC ';
		$ids = DB::db_fetch_vector ($q);

		foreach ($ids as $id) $ret[] = new InterventionsExport ($id);
		return $ret;
	}
}
?>
