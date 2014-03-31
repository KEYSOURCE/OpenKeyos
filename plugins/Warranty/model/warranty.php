<?php

class_load ('Computer');
class_load ('Peripheral');
class_load ('ADPrinter');
class_load ('Supplier');

/** Class fore representing warranty information for Computers, AD Printers and
* Peripherals in an unified way.
*
* Note that warranty information is loaded from different sources depending on
* object types.

*/

class Warranty
{
	/** Defines the type of object to which this warranty applies
	* @var int */
	var $obj_class = WAR_OBJ_COMPUTER;

	/** The ID of the object to which this refers to
	* @var int */
	var $id = null;

	/** The asset number of the object
	* @var string */
	var $asset_no = '';

	/** The secondary ID of the object.
	* For computers and AD printers, this represents the index of the warranty item (nrc).
	* For peripherals, this represents the peripheral class
	* @var int */
	var $id2 = 0;

	/** Canonical name, needed for AD printers where the canonical name is primary key for the
	* ad_printers_warranties tables
	* @var string */
	var $canonical_name = '';

	/** The name of the product for which the warranty applies
	* @var string */
	var $product = '';

	/** The serial number of the product
	* @var string */
	var $sn = '';

	/** The start date of the warranty
	* @var timestamp */
	var $warranty_starts = 0;

	/** The end date of the warranty
	* @var timestamp */
	var $warranty_ends = 0;

	/** The ID of the service package
	* @var int */
	var $service_package_id = 0;

	/** The ID of the service level
	* @var int */
	var $service_level_id = 0;

	/** The service contract number
	* @var string */
	var $contract_number = '';

	/** A product-specific ID (e.g. a part number)
	* @var string */
	var $hw_product_id = '';

	/** Specifies if notifications should be raised for this warranty
	* @var bool */
	var $raise_alert = true;

	/** Specifies if this warranty has been replaced by another or if it is to be ignored.
	* @var bool */
	var $replaced_ignored = false;


	/** The number of remaining months in the warranty - if it hasn't expired yet
	* @var int */
	var $months_remaining = 0;

	/** The number of remaining days in the warranty - if it hasn't expired yet
	* @var int */
	var $days_remaining = 0;


	function Warranty ($obj_class, $id, $id2 = 0, $canonical_name = '')
	{
		if ($obj_class) $this->obj_class = $obj_class;
		if ($id) $this->id = $id;
		if ($id2) $this->id2 = $id2;
		if ($canonical_name) $this->canonical_name = $canonical_name;

		if ($obj_class and $id)
		{

			$this->load_data ();
		}
	}


	function load_data ()
	{
		if ($this->obj_class and $this->id)
		{
			switch ($this->obj_class)
			{
				case WAR_OBJ_COMPUTER:
					$q = 'SELECT field_id, value FROM '.TBL_COMPUTERS_ITEMS.' ';
					$q.= 'WHERE computer_id='.$this->id.' AND item_id='.WARRANTY_ITEM_ID.' AND nrc='.$this->id2;
					$data = DB::db_fetch_list ($q);
					foreach ($data as $field_id=>$value)
					{
						$field = $GLOBALS['WARRANTY_ITEM_FIELDS'][$field_id];
						if($field)
						$this->$field = $value;
					}
					$computer_type = DB::db_fetch_field ('SELECT type FROM '.TBL_COMPUTERS.' WHERE id='.$this->id, 'type');
					$this->asset_no = get_asset_no_comp ($this->id, $computer_type);

					break;

				case WAR_OBJ_REMOVED_COMPUTER:
					$q = 'SELECT field_id, value FROM '.TBL_REMOVED_COMPUTERS_ITEMS.' ';
					$q.= 'WHERE computer_id='.$this->id.' AND item_id='.WARRANTY_ITEM_ID.' AND nrc='.$this->id2;
					$data = DB::db_fetch_list ($q);
					foreach ($data as $field_id=>$value)
					{
						$field = $GLOBALS['WARRANTY_ITEM_FIELDS'][$field_id];
						$this->$field = $value;
					}
					$computer_type = DB::db_fetch_field ('SELECT type FROM '.TBL_REMOVED_COMPUTERS.' WHERE id='.$this->id, 'type');
					$this->asset_no = get_asset_no_comp ($this->id, $computer_type);

					break;

				case WAR_OBJ_AD_PRINTER:
					// Get the common name for the printer
					$q = 'SELECT value FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id.' AND ';
					$q.= 'item_id='.ADPRINTERS_ITEM_ID.' AND nrc='.$this->id2.' AND field_id='.FIELD_ID_AD_PRINTER_CN;
					$this->product = DB::db_fetch_field ($q, 'value');

					// If canonical name was not set, fetch it
					if (!$this->canonical_name)
					{
						$q = 'SELECT value FROM '.TBL_COMPUTERS_ITEMS.' WHERE computer_id='.$this->id.' AND ';
						$q.= 'item_id='.ADPRINTERS_ITEM_ID.' AND nrc='.$this->id2.' AND field_id='.FIELD_ID_AD_PRINTER_CANONICAL_NAME;
						$this->canonical_name = DB::db_fetch_field ($q, 'value');
					}

					// Get the actual warranty information
					$q ='SELECT * FROM '.TBL_AD_PRINTERS_WARRANTIES.' WHERE canonical_name="'.mysql_escape_string($this->canonical_name).'"';
					$d = DB::db_fetch_row ($q);
					foreach ($GLOBALS['AD_PRINTERS_FIELDS'] as $f1 => $f2) $this->$f1 = $d[$f2];

					// Load the asset number
					$this->asset_no = get_asset_no_ad_printer ($this->canonical_name);

					break;

				case WAR_OBJ_REMOVED_AD_PRINTER:
					$ad_printer = new RemovedAD_Printer ($this->id);
					$this->product = $ad_printer->name;
					$this->canonical_name = $ad_printer->canonical_name;
					$this->asset_no = $ad_printer->asset_number;
					foreach ($GLOBALS['AD_PRINTERS_FIELDS'] as $f1 => $f2) $this->$f1 = $ad_printer->$f2;
					break;

				case WAR_OBJ_PERIPHERAL:
					// Load the peripheral warranties fields definitions if they don't exist already
					if (!isset($GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2]))
					{
						$class = new PeripheralClass ($this->id2);
						$GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2] = array (
							$class->warranty_start_field => 'warranty_starts',
							$class->warranty_end_field => 'warranty_ends',
							$class->warranty_service_package_field => 'service_package_id',
							$class->warranty_service_level_field => 'service_level_id',
							$class->warranty_contract_number_field => 'contract_number',
							$class->warranty_hw_prodct_id_field => 'hw_product_id',
							$class->sn_field => 'sn'
						);
					}

					// Load the fields from which the warranty information will be extracted
					$q = 'SELECT field_id, value FROM '.TBL_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$this->id;
					$data = DB::db_fetch_list ($q);
					foreach ($data as $f1 => $val)
					{
						if ($GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2][$f1])
						{
							$field = $GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2][$f1];
							$this->$field = $val;
						}
					}

					// Set the asset number
					$this->asset_no = get_asset_no_periph ($this->id);

					break;

				case WAR_OBJ_REMOVED_PERIPHERAL:
					// Load the peripheral warranties fields definitions if they don't exist already
					if (!isset($GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2]))
					{
						$class = new PeripheralClass ($this->id2);
						$GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2] = array (
							$class->warranty_start_field => 'warranty_starts',
							$class->warranty_end_field => 'warranty_ends',
							$class->warranty_service_package_field => 'service_package_id',
							$class->warranty_service_level_field => 'service_level_id',
							$class->warranty_contract_number_field => 'contract_number',
							$class->warranty_hw_prodct_id_field => 'hw_product_id',
							$class->sn_field => 'sn'
						);
					}

					// Load the fields from which the warranty information will be extracted
					$q = 'SELECT field_id, value FROM '.TBL_REMOVED_PERIPHERALS_FIELDS.' WHERE peripheral_id='.$this->id;
					$data = DB::db_fetch_list ($q);
					foreach ($data as $f1 => $val)
					{
						if ($GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2][$f1])
						{
							$field = $GLOBALS['PRINTERS_WARRANTY_FIELDS'][$this->id2][$f1];
							$this->$field = $val;
						}
					}

					// Set the asset number
					$this->asset_no = get_asset_no_periph ($this->id);

					break;
			}

			$this->months_remaining = $this->months_remaining ();
			$this->days_remaining = $this->days_remaining ();
			$this->is_expired = $this->is_expired ();
		}
	}


	/** Specifies if start and end dates have been specified for this warranty */
	function has_dates ()
	{
		return ($this->warranty_starts > 0 and $this->warranty_ends > 0);
	}

	/** Specifies if this warranty is expired. If the dates are not defined, it always returns false (not expired) */
	function is_expired ()
	{
		$ret = false;
		if ($this->has_dates()) $ret = ($this->warranty_ends < time());
		return $ret;
	}

	/** Specifies how many more months the warranty has remaining. If expired, always returns 0 */
	function months_remaining ()
	{
		$ret = 0;
		if ($this->has_dates() and !$this->is_expired ())
		{
			$ret = 0;
			while (strtotime('+ '.$ret.' months') < $this->warranty_ends) $ret++;
		}
		return $ret;
	}

	/** Specifies how many more days the warranty has remaining. If expired, always returns 0 */
	function days_remaining ()
	{
		$ret = 0;
		if ($this->has_dates() and !$this->is_expired ())
		{
			$ret = intval(($this->warranty_ends - time())/(24 * 3600));
		}
		return $ret;
	}

	/** Returns a string with the months or days till expiration, or '--' if already expired or no dates set */
	function get_expiration_str ()
	{
		$ret = '--';
		if ($this->has_dates())
		{
			if ($this->is_expired()) $ret = 'Expired';
			elseif ($this->months_remaining > 1) $ret = $this->months_remaining.' months';
			elseif ($this->months_remaining == 1) $ret = '1 month';
			elseif ($this->days_remaining > 1) $ret = $this->days_remaining.' days';
			elseif ($this->days_remaining == 1) $ret = '1 day';
		}
		return $ret;
	}

	/** Specifies if the warranty is valid in the specifies month
	* @param	Object					$month		Generic object with the month information. Must
	*									contain the attributes month_start and month_end.
	*									Such an object can come from a list generated by get_months()
	*/
	function has_month ($month)
	{
		$ret = false;
		if ($this->has_dates()) $ret = ($this->warranty_starts<=$month->month_end and $this->warranty_ends>=$month->month_start);
		return $ret;

	}

	/** Returns the color CODE that should be used for displaying this warranty, depending on expiration status */
	function get_color_code ()
	{
		$ret = '';
		if ($this->replaced_ignored) $ret = WAR_COL_CODE_REPLACED;
		elseif ($this->is_expired()) $ret = WAR_COL_CODE_EXPIRED;
		else
		{
			if ($this->months_remaining>3 and $this->months_remaining<=6) $ret = WAR_COL_CODE_6_MONTHS;
			elseif ($this->months_remaining>1 and $this->months_remaining<=3) $ret = WAR_COL_CODE_3_MONTHS;
			elseif ($this->days_remaining>0 and $this->months_remaining<=1) $ret = WAR_COL_CODE_1_MONTH;
			else $ret = WAR_COL_CODE_OK;
		}
		return $ret;
	}

	/** Returns the color that should be used for displaying this warranty, depending on expiration status */
	function get_color ()
	{
		$ret = '';
		if ($this->replaced_ignored) $ret = WAR_COL_REPLACED;
		elseif ($this->is_expired()) $ret = WAR_COL_EXPIRED;
		else
		{
			if ($this->months_remaining>3 and $this->months_remaining<=6) $ret = WAR_COL_6_MONTHS;
			elseif ($this->months_remaining>1 and $this->months_remaining<=3) $ret = WAR_COL_3_MONTHS;
			elseif ($this->days_remaining>0 and $this->months_remaining<=1) $ret = WAR_COL_1_MONTH;
			else $ret = WAR_COL_OK;
		}
		return $ret;
	}


	/** [Class Method] Returns an array with all the months covered by the specified list of warranties
	* @param	array(Warranty)					$warranties	Array of Warranty objects for which to
	*										determine the encompansing months
	* @return	array								Array of generic month objects, like those generated by
	*										get_months(), with all the months for which the specified
	*										warranties have coverage
	*/
	function get_warranties_months ($warranties = array ())
	{
		$date_min = 0;
		$date_max = 0;

		if (is_array($warranties))
		{
			// Determine the minimum and maximum dates
			foreach ($warranties as $warranty)
			{
				if ($warranty->warranty_starts>0 and $warranty->warranty_ends>0)
				{
					if ($date_min==0 or $date_min > $warranty->warranty_starts) $date_min = $warranty->warranty_starts;
					if ($date_max==0 or $date_max < $warranty->warranty_ends) $date_max = $warranty->warranty_ends;
				}
			}
		}

		// If we don't have start and end dates, use the current year or the year where we have at least one value
		if ($date_min==0 and $date_max==0)
		{
			$date_min = strtotime('1 Jan '.date('Y').' 00:00:00');
			$date_max = strtotime('31 Dec '.(date('Y')+1).' 23:59:00');
		}
		elseif ($date_min==0 and $date_max>0) $date_min = strtotime('1 Jan '.(date('Y',$date_max)-1).' 00:00:00');
		elseif ($date_min>0 and $date_max==0) $date_max = strtotime('31 Dec '.(date('Y',$date_min)+1).' 23:59:00');

		// Build the list of months in the specified interval
		$months = get_months ($date_min, $date_max);

		return $months;
	}


	/** [Class Method] Returns an array with the years and months grouped in such a way that the total number of groups is
	* under a specified limit. This is useful, for example, when generating WordML reports, as it has limitations on the total
	* number of columns in a table (63).
	* The groups are generated so that no group extends over 2 years. This means that for a year the last group might have a
	* different number of months than the rest.
	* The function will try to use the minimum size of groups and then will keep increasing it until the total number of
	* groups is under the specified limit or until no further reduction is possible.
	* @param	array						$months		Array with the months, e.g. as returned by get_warranties_months() method
	* @param	int						$groups_limit	The total number of "groups" (see below) that the result should contain
	* @return	array								Associative array with the result. The keys are years numbers, and
	*										the values are arrays of "groups" of months. Each such "group" is
	*										a generic object with the following attributes:
	*										- month_start, month_end: The start date of the first month and the date
	*										  of the last month in the group.
	*										- months_count: The number of months in the group.
	*										- is_current: True/False if the current time is inside this group
	*										- is_year_start: True/False if this group is the first in
	*										  the year OR if it is the first group is the first year from the
	*										  result.
	*										- month_str: String representation of the group (e.g. "Apr 02 - May 02")
	*/
	function get_warranties_months_grouped ($months, $groups_limit = 62)
	{
		$ret = array ();

		if (count($months) > 0)
		{
			// First group the months in years, to simplify things
			$years = array ();
			foreach ($months as $month) $years[date('Y',$month->month_start)][] = $month;
			$c_time = time ();

			$group_size = ceil(count($months)/$groups_limit);
			do
			{
				$ret = array ();
				$tot_groups = 0;

				foreach ($years as $year => $year_months)
				{
					$cnt = 1;
					for ($i=0; $i<count($year_months); $i++)
					{
						$month = &$year_months[$i];
						if ($cnt==1)
						{
							// Group start
							$group = null;
							$group->month_start = $month->month_start;
						}
						if ($cnt==$group_size or $i==count($year_months)-1)
						{
							// Group end or year end
							$group->month_end = $month->month_end;
							$group->months_count = $cnt;
							$group->is_current = ($group->month_start<=$c_time and $group->month_end>=$c_time);
							if ($cnt==1) $group->month_str = date('M y', $group->month_start);
							else $group->month_str = date('M y', $group->month_start).' - '.date('M y', $group->month_end);
							// The first overall group is always marked as "year_start" too
							$group->is_year_start = (date('m',$group->month_start)=='01' or $tot_groups==0);
							$ret[$year][] = $group;
							$cnt = 0;
							$tot_groups++;
						}
						$cnt++;
					}
				}
				$group_size++;
			} while ($tot_groups > $groups_limit and $tot_groups > count($years));


		}

		return $ret;
	}

	/** [Class Method] Helper function for arranging headers of tables showing warranties.
	* @param	array						$months		Array with the months in the shown interval,
	*										e.g. such as generated by the get_warranties_months() mehtod
	* @param	int						$group_size	The number of months to show in a group. Default is 4, min. is 2
	* @param	timestamp					$month_start	If specified, start the header with this month, regardless of
	*										what's specified in $months
	* @param	timestamp					$month_end	If specified, end the header with this month, regardless of what's
	*										specified in $months
	* @return	array								Associative array of generic objects. The keys as the years in interval,
	*										the values are generic objects with the following attributes:
	*										- months: Associative array with the months in interval for that
	*										  year, keys being month number and value the short month name.
	*										- months_count: The number of months in interval for this year
	*										- groups: Array with groups of months, containing generic objects
	*										  with the following fields:
	*										  - month_num, month_str: The number and short name of the first
	*										    month in the group.
	*										  - months_count: The number of months in the group
	*/
	function get_warranties_months_header ($months, $group_size = 4, $month_start = 0, $month_end = 0)
	{
		$ret = array ();
		if ($group_size < 2) $group_size = 2;

		// Count the months for each year
		foreach ($months as $month)
		{
			if ((!$month_start or ($month_start <= $month->month_start)) and (!$month_end or ($month_end >= $month->month_start)))
			{
				$year = date('Y',$month->month_start);
				$month_num = date('m',$month->month_start);
				$month_str = date('M',$month->month_start);
				$ret[$year]->months[$month_num] = $month_str;
				$ret[$year]->months_count++;
			}
		}

		// Now for each year try and group the months
		foreach ($ret as $year=>$d)
		{
			$obj = null;
			$cnt = 1;
			foreach ($d->months as $month_num => $month_str)
			{
				if ($cnt==1)
				{
					// Store the first month in the group
					$month_num_first = $month_num;
					$month_str_first = $month_str;
					$cnt++;
				}
				elseif ($cnt >= $group_size)
				{
					// Group end, store the group details
					$obj = null;
					$obj->month_num = $month_num_first;
					$obj->month_str = $month_str_first;
					$obj->months_count = $cnt;
					$obj->is_year_start = false;
					$ret[$year]->groups[] = $obj;
					$cnt = 1;
				}
				else $cnt++;
			}
			if ($cnt != 1)
			{
				// There is one incomplete group at the end
				$obj = null;
				$obj->month_num = $month_num_first;
				$obj->month_str = $month_str_first;
				$obj->months_count = $cnt-1;
				$ret[$year]->groups[] = $obj;
			}
		}

		// Put the year start marker in all first groups for each year
		foreach ($ret as $year=>$y)
		{
			$ret[$year]->groups[0]->is_year_start = true;
		}

		return $ret;
	}

	/** [Class Method] Returns all servers with missing warranties dates or serial numbers */
	function get_servers_missing_warranties_sn ()
	{
		$ret = array ();

		$q = 'SELECT DISTINCT c.id, cust.id as customer_id, cust.name as customer_name, ci_name.value as computer_name ';
		$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		$q.= 'INNER JOIN '.TBL_COMPUTERS_ITEMS.' ci_name ON c.id=ci_name.computer_id AND ci_name.item_id='.NAME_ITEM_ID.' ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_ITEMS.' ci_brand ON c.id=ci_brand.computer_id AND ci_brand.item_id='.BRAND_ITEM_ID.' ';

		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_ITEMS.' ci_warranty ON c.id=ci_warranty.computer_id AND ci_warranty.item_id='.WARRANTY_ITEM_ID.' AND ';
		$q.= '(ci_warranty.field_id='.FIELD_ID_WARRANTY_SN.' OR ci_warranty.field_id='.FIELD_ID_WARRANTY_START.' OR ci_warranty.field_id='.FIELD_ID_WARRANTY_END.') ';

		$q.= 'WHERE c.type='.COMP_TYPE_SERVER.' AND cust.active=1 AND cust.has_kawacs=1 AND ';
		if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $this->current_user->get_assigned_customers_list();
			$q.= 'c.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q.=") AND ";
		}
		$q.= '(ci_brand.value IS NULL OR (ci_brand.value IS NOT NULL AND ci_brand.value <> "'.VMWARE_BRAND_MARKER.'")) AND ';
		$q.= '(ci_warranty.value IS NULL or ci_warranty.value="" or ci_warranty.value="0") ';
		$q.= 'ORDER BY cust.name, ci_name.value ';

		$ret = DB::db_fetch_array ($q);
		for ($i=0; $i<count($ret); $i++)
		{
			$ret[$i]->warranties = Computer::get_warranties (array('customer_id'=>$ret[$i]->customer_id, 'computer_id'=>$ret[$i]->id));
		}

		return $ret;
	}

	/** [Class Method] Returns all non-servers with missing warranties dates or serial numbers */
	function get_workstations_missing_warranties_sn ()
	{
		$ret = array ();

		$q = 'SELECT DISTINCT c.id, cust.id as customer_id, cust.name as customer_name, ci_name.value as computer_name ';
		$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN '.TBL_CUSTOMERS.' cust ON c.customer_id=cust.id ';
		$q.= 'INNER JOIN '.TBL_COMPUTERS_ITEMS.' ci_name ON c.id=ci_name.computer_id AND ci_name.item_id='.NAME_ITEM_ID.' ';
		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_ITEMS.' ci_brand ON c.id=ci_brand.computer_id AND ci_brand.item_id='.BRAND_ITEM_ID.' ';

		$q.= 'LEFT OUTER JOIN '.TBL_COMPUTERS_ITEMS.' ci_warranty ON c.id=ci_warranty.computer_id AND ci_warranty.item_id='.WARRANTY_ITEM_ID.' AND ';
		$q.= '(ci_warranty.field_id='.FIELD_ID_WARRANTY_SN.' OR ci_warranty.field_id='.FIELD_ID_WARRANTY_START.' OR ci_warranty.field_id='.FIELD_ID_WARRANTY_END.') ';

		$q.= 'WHERE c.type<>'.COMP_TYPE_SERVER.' AND cust.active=1 AND cust.has_kawacs=1 AND ';
		if($this->current_user->is_customer_user() and $this->current_user->administrator and $this->current_user->type==USER_TYPE_CUSTOMER)
		{
			$cc = $this->current_user->get_assigned_customers_list();
			$q.= 'c.customer_id in (';
			$i=0;
			foreach($cc as $k=>$name)
			{
				if($i!=count($cc)-1) $q.=$k.", ";
				else $q.=$k;
			}
			$q.=") AND ";
		}
		$q.= '(ci_brand.value IS NULL OR (ci_brand.value IS NOT NULL AND ci_brand.value <> "'.VMWARE_BRAND_MARKER.'")) AND ';
		$q.= '(ci_warranty.value IS NULL or ci_warranty.value="" or ci_warranty.value="0") ';
		$q.= 'ORDER BY cust.name, ci_name.value ';

		$ret = DB::db_fetch_array ($q);
		for ($i=0; $i<count($ret); $i++)
		{
			$ret[$i]->warranties = Computer::get_warranties (array('customer_id'=>$ret[$i]->customer_id, 'computer_id'=>$ret[$i]->id));
		}

		return $ret;
	}

	/**
	 * [Class Method] Receive warranty information for the specified service tag directly from the producer's site
	 *
	 **/
	function get_warranty_info($computer_id, $service_tag = null)
	{
		$link = "http://supportapj.dell.com/support/topics/topic.aspx/ap/shared/support/my_systems_info/en/details?c=in&cs=inbsd1&l=en&s=gen&~tab=1&ServiceTag=".$service_tag;
		$oldSetting = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$html = new DOMDocument();
		$html->loadHtmlFile($link);
		$xpath = new DOMXPath( $html );
		$elements = $xpath->query( '//table[@class="contract_table"]/tr' );
		$count_rows = $elements->length;
		if($count_rows == 2)
		{
			$cells = $xpath->query("./td", $elements->item(1));
			$warranty_array = array(
				"Service Tag" => $service_tag,
				"Description" => $cells->item(0)->nodeValue,
				"Provider" => $cells->item(1)->nodeValue,
				"Start Date" => $cells->item(2)->nodeValue,
				"End Date" => $cells->item(3)->nodeValue,
				"Days left" => $cells->item(4)->nodeValue
			);

			//TODO: insert the data in the specified warranty...
			//if the warranty exists update it, otherwise create it 
			//$warranty = new Warranty(WAR_OBJ_COMPUTER, $computer_id);
			//$warranty->warranty_starts
		}
		libxml_clear_errors();
		libxml_use_internal_errors( $oldSetting );
	}
}

?>
