<?php
class_load ('Timesheet');
class_load ('InterventionLocation');

/**
* Class for managing export batches of timesheets
*
* Every time a request comes from ERP to fetch a new batch of timesheets,
* it should be first checked with the class method has_export() if there are any
* interventions ready to be exported and only after that, if there are, a new export
* object should be created.
*/

class TimesheetsExport extends Base
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
	
	/** The number of timesheets exported (used for control)
	* @var int */
	var $cnt_timesheets = 0;
	
	/** The total of work time from all timesheet details NOT linked to tickets (used for control)
	* @var int */
	var $work_time_sum = 0;
	
	/** The MD5 checksum of the exported XML file 
	* @var string */
	var $md5_file = '';
	
	/** The IP address of the computer from which the export was requested
	* @var string */
	var $requester_ip = '';
	
	
	/** Array with the IDs of the timesheets which were included in this export
	* @var array */
	var $timesheets_ids = array ();
	
	/** Array with the actions/confirmation received for this export
	* @var array */
	var $actions = array ();
	
	
	/** Table storing objects data */
	var $table = TBL_TIMESHEETS_EXPORTS;
	
	/** The fields to be used when loading or saving data to database */
	var $fields = array ('id', 'created', 'status', 'cnt_timesheets', 'work_time_sum', 'md5_file', 'requester_ip');


	/**
	* Constructor. Also loads the data if an ID has been specified
	* @param	int	$id		The ID of the object to load
	*/
	function TimesheetsExport ($id = null)
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
			// Load the list of action for this export
			$q = 'SELECT * FROM '.TBL_TIMESHEETS_EXPORTS_ACTIONS.' WHERE export_id='.$this->id.' ORDER BY created DESC ';
			$this->actions = $this->db_fetch_array ($q);
			
			// Load the list of timesheet IDs for this export
			$q = 'SELECT timesheet_id FROM '.TBL_TIMESHEETS_EXPORTS_IDS.' WHERE export_id='.$this->id.' ORDER BY timesheet_id ';
			$this->timesheets_ids = $this->db_fetch_vector ($q);
		}
	}
	
	/** Saves the object data */
	function save_data ()
	{
		if (!$this->created) $this->created = time ();
		parent::save_data ();
		
		if ($this->id)
		{
			// Save the list of timesheets IDs
			$this->db_query ('DELETE FROM '.TBL_TIMESHEETS_EXPORTS_IDS.' WHERE export_id='.$this->id);
			foreach ($this->timesheets_ids as $ts_id) 
			{
				$q = 'INSERT INTO '.TBL_TIMESHEETS_EXPORTS_IDS.'(export_id,timesheet_id) VALUES ';
				$q.= '('.$this->id.','.$ts_id.')';
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
	function make_export ($requester_ip = '')
	{
		$ret = false;
		if ($this->id)
		{
			// Fetch the list of intervention reports waiting to be exported
			$filter = array ('status' => TIMESHEET_STAT_PENDING_CENTRALIZE);
			$timesheets = Timesheet::get_timesheets ($filter, $no_count);
			$filter = array ('status' => TIMESHEET_STAT_APPROVED);
			$timesheets = array_merge ($timesheets, Timesheet::get_timesheets ($filter, $no_count));
			
			$total_work_hours = 0;
			$this->timesheets_ids = array ();
			for ($i=0; $i<count($timesheets); $i++)
			{
				$timesheet = &$timesheets[$i];
				$this->timesheets_ids[] = $timesheet->id;
				$timesheet->load_user ();
				for ($j=0; $j<count($timesheet->details); $j++)
				{
					if ($timesheet->details[$j]->customer_id) $timesheet->details[$j]->customer = new Customer ($timesheet->details[$j]->customer_id);
					if (!$timesheet->details[$j]->ticket_detail_id)
					{
						$total_work_hours+= $timesheet->details[$j]->get_duration_hours();
						$timesheet->details[$j]->activity->load_users_codes();
					}
				}
				
				// Mark the timesheet as "Centralized"
				$timesheet->status = TIMESHEET_STAT_PENDING_CENTRALIZE;
				$timesheet->save_data ();
			}
			
			$this->requester_ip = $requester_ip;
			$this->work_time_sum = $total_work_hours;
			$this->cnt_timesheets = count($timesheets);
			
			$parser = new BaseDisplay ();
			$xml_tpl = 'erp/timesheets_export.xml';
			
			$parser->assign ('export', $this);
			$parser->assign ('timesheets', $timesheets);
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
			$q = "INSERT INTO ".TBL_TIMESHEETS_EXPORTS_ACTIONS."(export_id, created, request_url, requester_ip) ";
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
	
	
	/** Called when the ERP wants to communicate import results */
	function is_import_confirmation_ok ($cnt_timesheets, $work_time_sum, $request_url, $remote_ip)
	{
		$ret = false;
		if ($this->id)
		{
			$q = "INSERT INTO ".TBL_TIMESHEETS_EXPORTS_ACTIONS." (export_id, created, request_url, requester_ip) ";
			$q.= "VALUES ($this->id, ".time().", '".mysql_escape_string ($request_url)."', '".mysql_escape_string($remote_ip)."') ";
			$this->db_query ($q);
			
			if ($this->cnt_timesheets == $cnt_timesheets and round($this->work_time_sum,2) == round($work_time_sum,2))
			{
				$this->status = INTERVENTION_EXPORT_STAT_IMPORT_CONFIRMED;
				$ret = true;
				
				// Mark the interventions as centralized
				foreach ($this->timesheets_ids as $id)
				{
					$ts = new Timesheet ($id);
					$ts->status = TIMESHEET_STAT_CENTRALIZED;
					$ts->save_data ();
				}
			}
			
			$this->save_data ();
		}
		
		return $ret;
	}
	
	
	/** Returns the full path and fname of the XML file for this export */
	function get_fname ()
	{
		return DIR_EXPORT_XML_TIMESHEETS.'/'.FILE_PREFIX_EXPORT_XML_TIMESHEETS.$this->id.'.xml';
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
			$ret = $parser->mk_redir ('timesheets_confirm_file', array('id' => $this->id, 'md5' => ''), 'erp');
		}
		return $ret;
	}
	
	
	function get_confirmation_import_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$parser = new BaseDisplay ();
			$ret = $parser->mk_redir ('timesheets_confirm_import', array('id' => $this->id), 'erp');
		}
		return $ret;
	}
	
	
	function get_retransfer_url ()
	{
		$ret = '';
		if ($this->id)
		{
			$parser = new BaseDisplay ();
			$ret = $parser->mk_redir ('timesheets_retransfer', array('id' => $this->id), 'erp');
		}
		return $ret;
	}
	
	
	/** [Class Method] Tells if there are any timesheets waiting to be exported
	* @param	bool					True or false if there are any intervention reports ready 
	*							to be exported.
	*/
	function has_export ()
	{
		$ret = false;
		$q = 'SELECT id FROM '.TBL_TIMESHEETS.' WHERE ';
		$q.= '(status='.TIMESHEET_STAT_APPROVED.' OR status='.TIMESHEET_STAT_PENDING_CENTRALIZE.') LIMIT 1';
		$id = DB::db_fetch_field ($q, 'id');
		if ($id) $ret = true;
		
		return $ret;
	}
	
	
	function get_exports ($filter = array ())
	{
		$q = 'SELECT id FROM '.TBL_TIMESHEETS_EXPORTS.' WHERE ';
		
		if (isset($filter['get_peding']))
		{
			$q.= '(status='.INTERVENTION_EXPORT_STAT_REQUESTED.' OR status='.INTERVENTION_EXPORT_STAT_SENT.' OR ';
			$q.= 'status='.INTERVENTION_EXPORT_STAT_FILE_CONFIRMED.') AND ';
		}
		
		$q = preg_replace ('/AND\s*$/', ' ', $q);
		$q = preg_replace ('/WHERE\s*$/', ' ', $q);
		
		$q.= 'ORDER BY created DESC ';
		$ids = DB::db_fetch_vector ($q);
		
		foreach ($ids as $id) $ret[] = new TimesheetsExport ($id);
		return $ret;
	}
}
?>