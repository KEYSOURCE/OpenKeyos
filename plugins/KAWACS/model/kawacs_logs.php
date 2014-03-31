<?php

/** Generic class for the maintenance of monthly computers logs */

class KawacsLogs
{

	function comp_unc ($a, $b)
	{
		if ($a['unc'] == $b['unc']) return 0;
		else return ($a['unc'] < $b['unc'] ? -1 : 1);
	}
	
	function get_disk_space_stats ($tbl)
	{
		$ret = array ('month'=>$tbl, 'total'=>0, 'total_keep'=>0, 'total_delete'=>0, 'time'=>0);
		$t1 = time ();
		
		$limit = 5; // The limit, in MB
		
		if ($tbl)
		{
			$tbl = 'computers_items_log_'.$tbl;
			
			$q = 'SELECT DISTINCT (computer_id) FROM '.$tbl.' WHERE item_id=1013 AND field_id=9 AND nrc=0 ';
			//$q.= 'AND computer_id=445 ';
			//$q.= 'LIMIT 100';
			$computers_ids = DB::db_fetch_vector ($q);
			
			foreach ($computers_ids as $id)
			{
				// Fetch in an array all the reported values
				$hist = array ();
				$q = 'SELECT field_id, nrc, value, reported FROM '.$tbl.' WHERE item_id=1013 AND (field_id=9 or field_id=13) ';
				$q.= 'AND computer_id='.$id.' ORDER BY reported, field_id, nrc ';
				$h = DB::db_query ($q);
				while ($d = mysql_fetch_object ($h))
				{
					if ($d->field_id == 9) $hist[$d->reported][$d->nrc]['unc'] = $d->value;
					else $hist[$d->reported][$d->nrc]['free'] = round ($d->value / ($limit*1024*1024), 2);
				}
				
				// See now which set of values are redundant
				$last_data = array ();
				foreach ($hist as $time => $data)
				{
					// Sort each set of values by unc
					if (count($data)>0) usort ($data, array ('KawacsLogs', 'comp_unc'));
					
					if ($data != $last_data)
					{
						$last_data = $data;
						$ret['total_keep']++;
					}
					else $ret['total_delete']++;
				}
			}
			
			$ret['total'] = $ret['total_keep']+$ret['total_delete'];
			$ret['time'] = time()-$t1;
			
		}
		return $ret;
	}
	
	function update_disk_space_stats ($tbl)
	{
		$ret = array ('month'=>$tbl, 'total'=>0, 'total_keep'=>0, 'total_delete'=>0, 'time'=>0);
		$t1 = time ();
		
		$limit = 5; // The limit, in MB
		
		if ($tbl)
		{
			if ($tbl == 'current') $tbl = 'computers_items_log';
			else $tbl = 'computers_items_log_'.$tbl;
			$tbl_bk = $tbl.'_new';
		
			DB::db_query ('DROP TABLE IF EXISTS '.$tbl_bk);
			DB::db_query ('CREATE TABLE '.$tbl_bk.' (computer_id int not null, item_id int not null, nrc int not null, field_id int not null, value mediumtext not null, reported int not null)');
			
			$q = 'SELECT DISTINCT (computer_id) FROM '.$tbl.' WHERE item_id=1013 AND field_id=9 AND nrc=0 ';
			$computers_ids = DB::db_fetch_vector ($q);
			
			foreach ($computers_ids as $id)
			{
				// Fetch in an array all the reported values
				$hist = array ();
				$q = 'SELECT field_id, nrc, value, reported FROM '.$tbl.' WHERE item_id=1013 AND (field_id=9 or field_id=13) ';
				$q.= 'AND computer_id='.$id.' ORDER BY reported, field_id, nrc ';
				$h = DB::db_query ($q);
				while ($d = mysql_fetch_object ($h))
				{
					if ($d->field_id == 9) $hist[$d->reported][$d->nrc]['unc'] = $d->value;
					else $hist[$d->reported][$d->nrc]['free'] = round ($d->value / ($limit*1024*1024), 2);
				}
				
				// See now which set of values are redundant
				$last_data = array ();
				foreach ($hist as $time => $data)
				{
					// Sort each set of values by unc
					if (count($data)>0) usort ($data, array ('KawacsLogs', 'comp_unc'));
					
					if ($data != $last_data)
					{
						$last_data = $data;
						$ret['total_keep']++;
						// Copy the data to the new database
						$q = 'INSERT INTO '.$tbl_bk.' SELECT * FROM '.$tbl.' WHERE ';
						$q.= 'computer_id='.$id.' AND item_id=1013 AND reported='.$time;
						DB::db_query ($q);
					}
					else $ret['total_delete']++;
				}
			}
			
			$ret['total'] = $ret['total_keep']+$ret['total_delete'];
			$ret['process_time'] = time()-$t1;
			
			$t2 = time ();
			DB::db_query ('INSERT INTO '.$tbl_bk.' (computer_id, item_id, nrc, field_id, value, reported) SELECT computer_id, item_id, nrc, field_id, value, reported FROM '.$tbl.' WHERE item_id<>1013');
			$q = 'ALTER TABLE '.$tbl_bk.' add PRIMARY KEY  (nrc, computer_id, item_id, field_id, reported), ';
			$q.= 'ADD KEY(field_id), ADD KEY(computer_id), ADD KEY(item_id), ADD KEY(reported)';
			DB::db_query ($q);
			$ret['data_time'] = time()-$t2;
			$ret['time'] = time()-$t1;
			
			// Finally, rename the tables
			DB::db_query ('DROP TABLE '.$tbl);
			DB::db_query ('RENAME TABLE '.$tbl_bk.' TO '.$tbl);
		}
		
		return $ret;
	}
	
	
	function get_ad_computers_stats ($tbl)
	{
		$ret = array ('month'=>$tbl, 'total'=>0, 'total_keep_1046'=>0, 'total_delete_1046'=>0, 'total_keep_1047'=>0, 'total_delete_1047'=>0, 'time'=>0);
		$t1 = time ();
		
		if ($tbl)
		{
			$tbl = 'computers_items_log_'.$tbl;
			
			$q = 'SELECT DISTINCT (computer_id) FROM '.$tbl.' WHERE item_id=1030 AND field_id=88 AND nrc=0 ';
			//$q.= 'AND computer_id=445 ';
			//$q.= 'LIMIT 10';
			$computers_ids = DB::db_fetch_vector ($q);
			
			// Set the field translations
			$fields_1030_1046 = array (88=>214, 113=> 215, 116=>218, 117=>219, 118=>220, 119=>221, 123=>225, 124=>226, 125=>227, 126=>228, 128=>230, 129=>231, 130=>232);
			$fields_1030_1047 = array (88=>234, 114=>235 ,115=>236, 120=>237, 121=>238, 122=>239, 127=>240, 131=>241, 132=>242);
			
			foreach ($computers_ids as $id)
			{
				// Fetch in an array all the reported values
				$last_data_1046 = array ();
				$last_data_1047 = array ();
				$last_reported = 0;
				$q = 'SELECT field_id, nrc, value, reported FROM '.$tbl.' WHERE item_id=1030 ';
				$q.= 'AND computer_id='.$id.' ORDER BY reported, field_id, nrc ';
				$h = DB::db_query ($q);
				while ($d = mysql_fetch_object ($h))
				{
					if ($d->reported != $last_reported)
					{
						// This is a new data set
						if ($last_reported > 0)
						{
							$ret['total']++;
							if ($data_1046 != $last_data_1046)
							{
								$ret['total_keep_1046']++;
								$last_data_1046 = $data_1046;
							}
							else $ret['total_delete_1046']++;
							if ($data_1047 != $last_data_1047)
							{
								$ret['total_keep_1047']++;
								$last_data_1047 = $data_1047;
							}
							else $ret['total_delete_1047']++;
						}
						
						$data_1046 = array ();
						$data_1047 = array ();
						$last_reported = $d->reported;
					}
					if (isset($fields_1030_1046[$d->field_id])) $data_1046[$d->nrc][$d->field_id] = $d->value;
					if (isset($fields_1030_1047[$d->field_id])) $data_1047[$d->nrc][$d->field_id] = $d->value;
				}
			}
			
			$ret['total'] = $ret['total_keep_1046']+$ret['total_delete_1046'];
			$ret['time'] = time()-$t1;
			
		}
		return $ret;
	}
	
	function update_ad_computers_stats ($tbl)
	{
		$ret = array ('month'=>$tbl, 'total'=>0, 'total_keep_1046'=>0, 'total_delete_1046'=>0, 'total_keep_1047'=>0, 'total_delete_1047'=>0, 'time'=>0);
		$t1 = time ();
		
		if ($tbl)
		{
			if ($tbl == 'current') $tbl = 'computers_items_log';
			else $tbl = 'computers_items_log_'.$tbl;
			$tbl_bk = $tbl.'_new';
		
			DB::db_query ('DROP TABLE IF EXISTS '.$tbl_bk);
			DB::db_query ('CREATE TABLE '.$tbl_bk.' (computer_id int not null, item_id int not null, nrc int not null, field_id int not null, value mediumtext not null, reported int not null)');
			
			$q = 'SELECT DISTINCT (computer_id) FROM '.$tbl.' WHERE ((item_id=1046 AND field_id=228) OR (item_id=1047 and field_id=235)) AND nrc=0';
			//$q.= 'AND computer_id=445 ';
			//$q.= 'LIMIT 10';
			$computers_ids = DB::db_fetch_vector ($q);
			
			foreach ($computers_ids as $id)
			{
				foreach (array(1046, 1047) as $item_id)
				{
					$last_data = array ();
					$last_reported = 0;
					$q = 'SELECT field_id, nrc, value, reported FROM '.$tbl.' WHERE item_id='.$item_id.' AND ';
					$q.= 'computer_id='.$id.' ORDER BY reported, field_id, nrc ';
					$h = DB::db_query ($q);
					while ($d = mysql_fetch_object ($h))
					{
						if ($d->reported != $last_reported)
						{
							// This is a new data set
							if ($last_reported > 0)
							{
								$ret['total']++;
								if ($data != $last_data)
								{
									$ret['total_keep_'.$item_id]++;
									$last_data = $data;
									$q = 'INSERT INTO '.$tbl_bk.' SELECT * FROM '.$tbl.' ';
									$q.= 'WHERE computer_id='.$id.' AND item_id='.$item_id.' AND reported='.$d->reported;
									DB::db_query ($q);
								}
								else $ret['total_delete_'.$item_id]++;
							}
							else
							{
								// Make sure to always copy the first value
								$q = 'INSERT INTO '.$tbl_bk.' SELECT * FROM '.$tbl.' ';
								$q.= 'WHERE computer_id='.$id.' AND item_id='.$item_id.' AND reported='.$d->reported;
								DB::db_query ($q);
							}
							$data = array ();
							$last_reported = $d->reported;
						}
						$data[$d->nrc][$d->field_id] = $d->value;
					}
				}
			}
			
			// Copy the rest of the data
			$t2 = time ();
			DB::db_query ('INSERT INTO '.$tbl_bk.' SELECT * FROM '.$tbl.' WHERE item_id<>1046 and item_id<>1047');
			$q = 'ALTER TABLE '.$tbl_bk.' add PRIMARY KEY  (nrc, computer_id, item_id, field_id, reported), ';
			$q.= 'ADD KEY(field_id), ADD KEY(computer_id), ADD KEY(item_id), ADD KEY(reported)';
			DB::db_query ($q);
			$ret['data_time'] = time()-$t2;
			
			$ret['total'] = $ret['total_keep_1046']+$ret['total_delete_1046'];
			$ret['time'] = time()-$t1;
			
			// Finally, rename the tables
			DB::db_query ('DROP TABLE '.$tbl);
			DB::db_query ('RENAME TABLE '.$tbl_bk.' TO '.$tbl);
		}
		return $ret;
	}
	
	
	/** Convers the old items 1030 to 1046 and 1047 */
	function update_1030_1046 ($tbl)
	{
		$ret = array ('month'=>$tbl, 'initial'=>0, 'final_1046'=>0, 'final_1047'=>0, 'time'=>0);
		$t1 = time ();
		
		if ($tbl)
		{
			if ($tbl == 'current') $tbl = 'computers_items_log';
			elseif ($tbl == 'real') $tbl = 'computers_items';
			else $tbl = 'computers_items_log_'.$tbl;
			$tbl_bk = $tbl.'_new';
			
			$ret['initial'] = DB::db_fetch_field ('SELECT count(*) as cnt FROM '.$tbl.' WHERE item_id=1030 AND field_id=88 AND nrc=0', 'cnt');
			
			DB::db_query ('DROP TABLE IF EXISTS '.$tbl_bk);
			DB::db_query ('CREATE TABLE '.$tbl_bk.' (computer_id int not null, item_id int not null, nrc int not null, field_id int not null, value mediumtext not null, reported int not null)');
			
			DB::db_query ('INSERT INTO '.$tbl_bk.' SELECT * FROM '.$tbl.' WHERE item_id=1030 AND field_id=117');
			DB::db_query ('UPDATE '.$tbl_bk.' SET item_id=1047, field_id=235');
			
			// Set the field translations
			$fields_1030_1046 = array(88=>225, 113=>226, 116=>227, 117=>228, 118=>229, 119=>230, 123=>231, 124=>232, 125=>233, 126=>234, 128=>244, 129=>245, 130=>246);
			$fields_1030_1047 = array (114=>236, 115=>237, 120=>238, 121=>239, 122=>240, 127=>241, 131=>242, 132=>243); //117=>235, 
			
			//array (88=>214, 113=>215, 116=>218, 117=>219, 118=>220, 119=>221, 123=>225, 124=>226, 125=>227, 126=>228, 128=>230, 129=>231, 130=>232);
			//array (114=>235 ,115=>236, 120=>237, 121=>238, 122=>239, 127=>240, 131=>241, 132=>242); // 88=>234
			foreach ($fields_1030_1047 as $old_id => $new_id)
			{
				$q = 'INSERT INTO '.$tbl_bk.' SELECT computer_id,1047 as item_id, nrc, '.$new_id.' as field_id, value, reported ';
				$q.= 'FROM '.$tbl.' WHERE item_id=1030 AND field_id='.$old_id;
				DB::db_query ($q);
			}
			foreach ($fields_1030_1046 as $old_id => $new_id)
			{
				$q = 'INSERT INTO '.$tbl_bk.' SELECT computer_id,1046 as item_id, nrc, '.$new_id.' as field_id, value, reported ';
				$q.= 'FROM '.$tbl.' WHERE item_id=1030 AND field_id='.$old_id;
				DB::db_query ($q);
			}
			
			DB::db_query ('INSERT INTO '.$tbl_bk.' SELECT * FROM '.$tbl.' WHERE item_id<>1030');
			$q = 'ALTER TABLE '.$tbl_bk.' add PRIMARY KEY  (nrc, computer_id, item_id, field_id, reported), ';
			$q.= 'ADD KEY(field_id), ADD KEY(computer_id), ADD KEY(item_id), ADD KEY(reported)';
			DB::db_query ($q);
			
			// Finally, rename the tables
			DB::db_query ('DROP TABLE '.$tbl);
			DB::db_query ('RENAME TABLE '.$tbl_bk.' TO '.$tbl);
			
			$ret['final_1046'] = DB::db_fetch_field ('SELECT count(*) as cnt FROM '.$tbl.' WHERE item_id=1046 AND field_id=214 AND nrc=0', 'cnt');
			$ret['final_1047'] = DB::db_fetch_field ('SELECT count(*) as cnt FROM '.$tbl.' WHERE item_id=1047 AND field_id=234 AND nrc=0', 'cnt');
			$ret['time'] = time()-$t1;
		}
		return $ret;
	}
}

?>