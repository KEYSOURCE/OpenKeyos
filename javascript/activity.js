

/**
* Class for representing activities linked to tickets details.
*
*/

function Activity (activity_id, is_continuation, billable, time_in, work_time, time_out, location_id)
{
	this.activity_id = activity_id;
	this.is_continuation = is_continuation;
	this.billable = billable;
	this.time_in = time_in;
	this.work_time = work_time;
	this.time_out = time_out;
	this.location_id = location_id;
	
	this.activity_name = '';
	this.location_name = '';
	
	this.time_start_travel_to = 0;
	this.time_end_travel_to = 0;
	this.time_start_travel_from = 0;
	this.time_end_travel_from = 0;

	this.set_times = function (time_in, work_time, time_out)
	{
		this.time_in = time_in;
		this.work_time = work_time;
		this.time_out = time_out;
	}
	
	this.set_travel_times = function (start_travel_to, end_travel_to, start_travel_from, end_travel_from)
	{
		this.time_start_travel_to = start_travel_to;
		this.time_end_travel_to = end_travel_to;
		this.time_start_travel_from = start_travel_from;
		this.time_end_travel_from = end_travel_from;
	}
	
	this.set_activity_name = function (activity_name)
	{
		this.activity_name = activity_name;
	}
	
	this.set_location_name = function (location_name)
	{
		this.location_name = location_name;
	}
	
	this.get_time_in_time_string = function ()
	{
		return ts_to_time_string (this.time_in);
	}
	
	this.get_time_in_date_string = function ()
	{
		return ts_to_date_string (this.time_in);
	}
	
	this.get_time_out_date_string = function()
	{
		return ts_to_date_string(this.time_out);
	}
	this.get_time_out_time_string = function()
	{
		return ts_to_time_string(this.time_out);
	}
	
	this.get_duration_string = function ()
	{
		return duration_to_string (this.work_time);
	}
	
	
}


// Gets the time (hh:mm) as a string from a UNIX timestamp 
function ts_to_time_string (timestamp)
{
	date = new Date (timestamp * 1000);
	minutes = date.getMinutes();
	if (minutes < 10) minutes = '0' + minutes;
	
	return date.getHours()+':'+minutes;
}

// Gets the date (d/m/y) as a string from a UNIX timestamp 
function ts_to_date_string (timestamp)
{
	date = new Date (timestamp * 1000);
	day = date.getDate();
	if (day < 10) day = '0' + day;
	month = date.getMonth()+1;
	if (month < 10) month = '0' + month;
	year = date.getFullYear() + '';
	year = year.replace (/^20/, '');
	
	return day+'/'+month+'/'+year;
}

// Gets a Unix timestamp from a date (d/m/y) and a time (hh:mm) string
function date_time_to_ts (date_string, time_string)
{
	ts = 0;
	if (date_string != '' && time_string != '')
	{
		d = get_date (date_string, time_string); // Creates a Javascript Date object
		ts = parseInt (Date.parse(d) / 1000);
	}

	return ts; 
}

// Gets a duration string (hh:mm) from a number of minutes
function duration_to_string (minutes)
{
	hours = parseInt (minutes/60);
	minutes = (minutes % 60);
	if (minutes < 0) minutes = minutes * -1;
	if (minutes < 10) minutes = '0'+minutes;
	
	return hours+':'+minutes;
}



// Functions for pop-up
function date_in_changed ()
{
	frm = document.forms[frm_name];
	date_element = frm.elements[date_in_name];
	date_string = date_element.value;
	date_out_element = frm.elements[date_out_name];
	date_out_string = date_out_element.value;
	
	if (date_string != '')
	{
		if (is_valid_date(date_string))
		{
			// If the date for the time out is not defined, set it the same as the time in date
			if (!is_valid_date(date_out_string)) date_out_element.value = date_element.value;
			
			time_in_changed();
		}
		else
		{
			alert ('The date in is invalid, it should be in dd/mm/yyyy format. Please try again.');
			date_element.value = '';
			date_element.focus ();
		}
	}
}

function hour_in_changed ()
{
	frm = document.forms[frm_name];
	hour_element = frm.elements[hour_in_name];
	hour_string = hour_element.value;
	
	if (hour_string != '')
	{
		if (is_valid_hour(hour_string)) time_in_changed();
		else
		{
			alert ('The hour in is invalid, it should be in hh:mm format. Please try again.');
			hour_element.value = '';
			hour_element.focus ();
		}
	}
}

// Called by date_in_changed() or hour_in_changed(), it will try to see if it can determine
// the duration or time out. If the duration is defined, it will calculate the time out.
// If the duration is not defined but the time out is defined, it will calculate the duration
function time_in_changed ()
{
	frm = document.forms[frm_name];
	date_in_element = frm.elements[date_in_name];
	date_in_string = date_in_element.value;
	hour_in_element = frm.elements[hour_in_name];
	hour_in_string = hour_in_element.value;
	duration_element = frm.elements[duration_name];
	duration_string = duration_element.value;
	date_out_element = frm.elements[date_out_name];
	date_out_string = date_out_element.value;
	hour_out_element = frm.elements[hour_out_name];
	hour_out_string = hour_out_element.value;
	
	if (is_valid_date (date_in_string) && is_valid_hour (hour_in_string))
	{
		// Check if the duration is defined
		if (is_valid_duration (duration_string))
		{
			set_time_out (date_out_element, hour_out_element, date_in_string, hour_in_string, duration_string);
		}
		// Check if the time out is defined
		else if (is_valid_date (date_out_string) && is_valid_hour (hour_out_string))
		{
			set_duration (duration_element, date_in_string, hour_in_string, date_out_string, hour_out_string);
		}
		
	}
}


// Called when the duration is modified. If the time in is set, it will update the time out. If time in
// is not set and the time out is set, it will update the time out
function duration_changed ()
{
	frm = document.forms[frm_name];
	date_in_element = frm.elements[date_in_name];
	date_in_string = date_in_element.value;
	hour_in_element = frm.elements[hour_in_name];
	hour_in_string = hour_in_element.value;
	duration_element = frm.elements[duration_name];
	duration_string = duration_element.value;
	date_out_element = frm.elements[date_out_name];
	date_out_string = date_out_element.value;
	hour_out_element = frm.elements[hour_out_name];
	hour_out_string = hour_out_element.value;
	
	if (duration_string != '')
	{
		if (is_valid_duration (duration_string))
		{
			// Check if the time in is defined
			if (is_valid_date (date_in_string) &&  is_valid_hour(hour_in_string))
			{
				set_time_out (date_out_element, hour_out_element, date_in_string, hour_in_string, duration_string);
			}
			// Check if the time out is defined
			else if (is_valid_date (date_out_string) && is_valid_hour (hour_out_string))
			{
				set_time_in (date_in_element, hour_in_element, date_out_string, hour_out_string, duration_string);
			}
		}
		else
		{
			alert ('The duration is invalid, it should be in hh:mm format. Please try again.');
			duration_element.value = '';
			duration_element.focus ();
		}
	}
}

function date_out_changed ()
{
	frm = document.forms[frm_name];
	date_element = frm.elements[date_out_name];
	date_string = date_element.value;
	
	if (date_string != '')
	{
		if (is_valid_date(date_string)) time_out_changed();
		else
		{
			alert ('The date out is invalid, it should be in dd/mm/yyyy format. Please try again.');
			date_element.value = '';
			date_element.focus ();
		}
	}
}

function hour_out_changed ()
{
	frm = document.forms[frm_name];
	hour_element = frm.elements[hour_out_name];
	hour_string = hour_element.value;
	
	if (hour_string != '')
	{
		if (is_valid_hour(hour_string)) time_out_changed();
		else
		{
			alert ('The hour in is invalid, it should be in hh:mm format. Please try again.');
			hour_element.value = '';
			hour_element.focus ();
		}
	}
}

// Called by date_out_changed() or hour_out_changed(), it will try to see if it can determine
// the duration or time in. If the time in is defined, it will compute the duration. If the 
// time in is not defined but the interval is, then the time in will be computed.
function time_out_changed ()
{
	frm = document.forms[frm_name];
	date_in_element = frm.elements[date_in_name];
	date_in_string = date_in_element.value;
	hour_in_element = frm.elements[hour_in_name];
	hour_in_string = hour_in_element.value;
	duration_element = frm.elements[duration_name];
	duration_string = duration_element.value;
	date_out_element = frm.elements[date_out_name];
	date_out_string = date_out_element.value;
	hour_out_element = frm.elements[hour_out_name];
	hour_out_string = hour_out_element.value;
	
	if (is_valid_date (date_out_string) && is_valid_hour (hour_out_string))
	{
		// Check if the time out is defined
		if (is_valid_date (date_in_string) && is_valid_hour (hour_in_string))
		{
			set_duration (duration_element, date_in_string, hour_in_string, date_out_string, hour_out_string);
		}
		// Check if the duration is defined
		else if (is_valid_duration (duration_string))
		{
			set_time_in (date_in_element, hour_in_element, date_out_string, hour_out_string, duration_string);
		}
	}
}


// Sets the time in (date and hour) based on the time out and duration
// Note: it is assumed that all the strings have been already checked and represent valid dates/times
function set_time_in (date_in_element, hour_in_element, date_out_string, hour_out_string, duration_string)
{
	d_out = get_date (date_out_string, hour_out_string);
	d_in = new Date (d_out.getTime() - (get_minutes(duration_string)*60*1000));
	
	day = d_in.getDate();
	if (day < 10) day = '0' + day;
	month = d_in.getMonth()+1;
	if (month < 10) month = '0' + month;
	year = d_in.getFullYear() + '';
	year = year.replace (/^20/, '');
	
	date_in_element.value = day+'/'+month+'/'+year;
	minutes = d_in.getMinutes();
	if (minutes < 10) minutes = '0' + minutes;
	hours = d_in.getHours();
	if (hours < 10) hours = '0' + hours;
	hour_in_element.value = hours+':'+minutes;
}

// Sets the duration based on the time in and time out
// Note: it is assumed that all the strings have been already checked and represent valid dates/times
function set_duration (duration_element, date_in_string, hour_in_string, date_out_string, hour_out_string)
{
	d_in = get_date (date_in_string, hour_in_string);
	d_out = get_date (date_out_string, hour_out_string);
	
	minutes = (((d_out - d_in) / (1000*60))); 
	
	hours = parseInt (minutes/60);
	minutes = (minutes % 60);
	if (minutes < 0) minutes = minutes * -1;
	if (minutes < 10) minutes = '0'+minutes;
	
	duration_element.value = hours+':'+minutes;
}

// Sets the time out (date and hour) based on the time in and duration
// Note: it is assumed that all the strings have been already checked and represent valid dates/times
function set_time_out (date_out_element, hour_out_element, date_in_string, hour_in_string, duration_string)
{
	d_in = get_date (date_in_string, hour_in_string);
	d_out = new Date (d_in.getTime() + (get_minutes(duration_string)*60*1000));
	
	day = d_out.getDate();
	if (day < 10) day = '0' + day;
	month = d_out.getMonth()+1;
	if (month < 10) month = '0' + month;
	year = d_out.getFullYear() + '';
	year = year.replace (/^20/, '');
	
	date_out_element.value = day+'/'+month+'/'+year;
	minutes = d_out.getMinutes();
	if (minutes < 10) minutes = '0' + minutes;
	hours = d_out.getHours();
	if (hours < 10) hours = '0' + hours;
	hour_out_element.value = hours+':'+minutes;
}



// Checks if a string (dd/mm/yyyy or dd.mm.yyyy) represents a valid date
function is_valid_date (date_string)
{
	ret = false;
	if (date_string.match(/[0-9]{1,2}[\/\.][0-9]{1,2}[\/\.][0-9]{2,4}/))
	{
		arr = date_string.split (/[\/\.]/);
		arr[0] = arr[0].replace (/^0*/, '');
		arr[1] = arr[1].replace (/^0*/, '');
		
		if (arr[2].length==2) arr[2] = '20'+arr[2];
		arr[0] = parseInt(arr[0]);
		arr[1] = parseInt(arr[1])-1;
		arr[2] = parseInt(arr[2]);
		d = new Date (arr[2], arr[1], arr[0]);
		
		ret = (d.getFullYear() == arr[2] && d.getMonth() == arr[1] && d.getDate() == arr[0]);
	}
	
	return ret;
}

// Checks if a string (hh:mm) represents a valid hour, being between 00:00 and 23:59
function is_valid_hour (hour_string)
{
	ret = false;
	
	if (hour_string.match(/[0-9]{1,2}\s*:\s*[0-9]{1,2}/))
	{
		arr = hour_string.split (/\s*:\s*/);
		arr[0] = parseInt(arr[0]);
		arr[1] = parseInt(arr[1]);
		
		ret = (arr[0]>=0 && arr[0]<=23 && arr[1]>=0 && arr[1]<=59);
	}
	
	return ret;
}

// Checks if a string (hh:mm) represents a valid duration
function is_valid_duration (duration_string)
{
	ret = false;
	
	if (duration_string.match(/[0-9]{1,}\s*:\s*[0-9]{1,2}/))
	{
		arr = duration_string.split (/\s*:\s*/);
		arr[0] = parseInt(arr[0]);
		arr[1] = parseInt(arr[1]);
		
		ret = (arr[0]>=0 && arr[1]>=0 && arr[1]<=59);
	}
	return ret;
}

// Takes a date string and an hour string and returns a Date object
// Note: it is assumed that the string are properly formatted
function get_date (date_string, hour_string)
{
	ret = null;
	
	arr = date_string.split (/[\/\.]/);
	arr[0] = arr[0].replace (/^0*/, '');
	arr[1] = arr[1].replace (/^0*/, '');
	
	if (arr[2].length==2) arr[2] = '20'+arr[2];
	arr[0] = parseInt(arr[0]);
	arr[1] = parseInt(arr[1])-1;
	arr[2] = parseInt(arr[2]);
	
	arr_hr = hour_string.split (/\s*:\s*/);
	// The leading 0 need to be deleted, otherwise parseInt('09') will return 0, not 9
	arr_hr[0] = arr_hr[0].replace (/^0/, '');
	arr_hr[1] = arr_hr[1].replace (/^0/, '');
	arr_hr[0] = parseInt(arr_hr[0]);
	arr_hr[1] = parseInt(arr_hr[1]);
	
	
	ret = new Date (arr[2], arr[1], arr[0], arr_hr[0], arr_hr[1], 0 , 0);
	
	return ret;
}


// Returns the number of minutes in an interval string
// Note: it is assumed that the string is properly formatted
function get_minutes (duration_string)
{
	ret = 0;
	
	arr = duration_string.split (/\s*:\s*/);
	if (arr.length == 2)
	{
		arr[0] = parseInt(arr[0], 10);
		arr[1] = parseInt(arr[1], 10);
		ret = arr[0]*60 + arr[1];
	}
        
	return ret;
}

// Overloading of setDateString in CalendarPopup.js, to allow updating
// the date fields after the calendar popup is selected
function setDateStringActivity (y,m,d)
{
	fels = document.forms[frm_name].elements;
	for (i = 0; i < fels.length; i++)
	{
		if (fels[i].name == elname)
		{
			el = fels[i];
		}
	}
	if (m < 10) m = '0'+m;
	y = y + '';
	y = y.replace (/^20/, '');
	
	el.value=d+"/"+m+"/"+y;

	if (elname == date_in_name)
	{
		date_in_changed ();
	}
	else if (elname == date_out_name)
	{
		date_out_changed ();
	}
}

function validate_form ()
{
	if (do_validation)
	{
		ret = false;
		frm = document.getElementById(frm_name);//document.forms[frm_name];
		activity_element = frm.elements[activity_name];
		sel_activity_id = get_selected_action_type ();
		date_in_element = frm.elements[date_in_name];
		date_in_string = date_in_element.value;
		hour_in_element = frm.elements[hour_in_name];
		hour_in_string = hour_in_element.value;
		duration_element = frm.elements[duration_name];
		duration_string = duration_element.value;
		date_out_element = frm.elements[date_out_name];
		date_out_string = date_out_element.value;
		hour_out_element = frm.elements[hour_out_name];
		hour_out_string = hour_out_element.value;
		
		start_travel_to_element = frm.elements[start_travel_to_name];
		start_travel_to_string = start_travel_to_element.value;
		end_travel_to_element = frm.elements[end_travel_to_name];
		end_travel_to_string = end_travel_to_element.value;
		start_travel_from_element = frm.elements[start_travel_from_name];
		start_travel_from_string = start_travel_from_element.value;
		end_travel_from_element = frm.elements[end_travel_from_name];
		end_travel_from_string = end_travel_from_element.value;
		
		var travel_to_set = false;
		var travel_from_set = false;
		var time_in_ts = 0;
		var time_out_ts = 0;
		var start_travel_to_ts = 0;
		var end_travel_to_ts = 0;
		var start_travel_from_ts = 0;
		var end_travel_from_ts = 0;
		
		if (show_location)
		{
			location_element = frm.elements[location_name];
			location_id = location_element.options[location_element.selectedIndex].value;
			all_empty = (date_in_string=='' && hour_in_string=='' && duration_string=='' && date_out_string=='' && hour_out_string=='' && location_id=='');
		}
		else
		{
			all_empty = (date_in_string=='' && hour_in_string=='' && duration_string=='' && date_out_string=='' && hour_out_string=='');
			location_id = '';
		}
		
		if (all_empty)
		{
			ret = true;
		}
		else
		{
			if (sel_activity_id == 0) {alert ('Please specify the action type.');ret = false;}
			else if (!is_valid_date(date_in_string)) {alert ('Please enter a valid date for the time in.');ret = false;}
			else if (!is_valid_hour(hour_in_string)) {alert ('Please enter a valid hour for the time in.');ret = false;}
			else if (!is_valid_duration(duration_string)) {alert ('Please enter a valid duration.');ret = false;}
			else if (!is_valid_date(date_out_string)) {alert ('Please enter a valid date for the time out.');ret = false;}
			else if (!is_valid_hour(hour_out_string)) {alert ('Please enter a vaild hour for the time out.');ret = false;}
			else if (show_location && location_id=='') {alert ('Please specify the location.');ret = false;}
			else ret = true;
			
			if (ret)
			{
				time_in_ts = date_time_to_ts (date_in_string, hour_in_string);
				time_out_ts = date_time_to_ts (date_out_string, hour_out_string);
			}
			
			start_travel_to_ts = 0;
			end_travel_to_ts = 0;
			start_travel_from_ts = 0;
			end_travel_from_ts = 0;
			
			// If all fine so far, check "travel to" time (if it was set). Make sure that 
			// either both or none have been set, and that they are valid.
			if (ret)
			{
				if (start_travel_to_string != '' || end_travel_to_string != '')
				{
					// At least one of the times has been set
					if (start_travel_to_string=='' || end_travel_to_string=='')
					{
						ret = false;
						alert ('If you specify the "Travel to" time, you need to specify both the start and end time');
					}
					else if (!is_valid_hour(start_travel_to_string)) {alert ('Please enter a vaild start time for "Travel to".');ret = false;}
					else if (!is_valid_hour(end_travel_to_string)) {alert ('Please enter a vaild end time for "Travel to".');ret = false;}
					else
					{
						start_travel_to_ts = date_time_to_ts (date_in_string, start_travel_to_string);
						end_travel_to_ts = date_time_to_ts (date_in_string, end_travel_to_string);
						
						if (start_travel_to_ts >= end_travel_to_ts)
						{
							ret = false;
							alert ('The start time for "Travel to" must be smaller than the end time.');
						}
						else if (end_travel_to_ts > time_in_ts)
						{
							ret = false;
							alert ('The end time for "Travel to" must be smaller or equal with the "Time in".');
						}
						else travel_to_set = true;
					}
				}
			}
			
			// Repeat the same checks for the "Travel from" times.
			if (ret)
			{
				if (start_travel_from_string != '' || end_travel_from_string != '')
				{
					// At least one of the times has been set
					if (start_travel_from_string=='' || start_travel_from_string=='')
					{
						ret = false;
						alert ('If you specify the "Travel from" time, you need to specify both the start and end time');
					}
					else if (!is_valid_hour(start_travel_from_string)) {alert ('Please enter a vaild start time for "Travel from".');ret = false;}
					else if (!is_valid_hour(end_travel_from_string)) {alert ('Please enter a vaild end time for "Travel from".');ret = false;}
					else
					{
						start_travel_from_ts = date_time_to_ts (date_out_string, start_travel_from_string);
						end_travel_from_ts = date_time_to_ts (date_out_string, end_travel_from_string);
						
						if (start_travel_from_ts >= end_travel_from_ts)
						{
							ret = false;
							alert ('The start time for "Travel from" must be smaller than the end time.');
						}
						else if (start_travel_from_ts < time_out_ts)
						{
							ret = false
							alert ('The start time for "Travel from" must be higher or equal with the "Time out".');
						}
						else travel_from_set = true;
					}
				}
			}
		}

		if (ret)
		{
			// Data is ok, send it back to parent window
			// First, compose the Activity object for sending back
			is_continuation_element = frm.elements[is_continuation_name];
			is_continuation = is_continuation_element.options[is_continuation_element.selectedIndex].value;
			billable_element = frm.elements[billable_name];
			billable = billable_element.options[billable_element.selectedIndex].value;
			duration = get_minutes (duration_string);
			
			if (travel_to_set)
			{
				start_travel_to_ts = date_time_to_ts (date_in_string, start_travel_to_string);
				end_travel_to_ts = date_time_to_ts (date_in_string, end_travel_to_string);
			}
			if (travel_from_set)
			{
				start_travel_from_ts = date_time_to_ts (date_out_string, start_travel_from_string);
				end_travel_from_ts = date_time_to_ts (date_out_string, end_travel_from_string);
			}
			
			ret_activity = new Activity (sel_activity_id, is_continuation, billable, time_in_ts, duration, time_out_ts, location_id);
			ret_activity.set_activity_name (activity_element.options[activity_element.selectedIndex].text);
			ret_activity.set_travel_times (start_travel_to_ts, end_travel_to_ts, start_travel_from_ts, end_travel_from_ts);
			
			if (show_location)
			{
				ret_activity.set_location_name (location_element.options[location_element.selectedIndex].text);
			}
			
			do_save (ret_activity);
		}
		
	}
	else
	{
		do_close ();
	}
	
	return false;
}



//ticket detail creation quick activity details creation function
//date changed in quick form
function date_in_changed_quick ()
{
	frm = document.forms['frm_t'];
	date_element = frm.elements['tdt[time_in_date]'];
	date_string = date_element.value;
	date_out_element = frm.elements['tdt[time_out_date]'];
	date_out_string = date_out_element.value;
	
	if (date_string != '')
	{
		if (is_valid_date(date_string))
		{
			// If the date for the time out is not defined, set it the same as the time in date
			if (!is_valid_date(date_out_string)) date_out_element.value = date_element.value;
			
			time_in_changed_quick();
		}
		else
		{
			alert ('The date in is invalid, it should be in dd/mm/yyyy format. Please try again.');
			date_element.value = '';
			date_element.focus ();
		}
	}
}
function hour_in_changed_quick ()
{
	frm = document.forms['frm_t'];
	hour_element = frm.elements['tdt[time_in_hour]'];
	hour_string = hour_element.value;
	
	if (hour_string != '')
	{
		if (is_valid_hour(hour_string)) time_in_changed_quick();
		else
		{
			alert ('The hour in is invalid, it should be in hh:mm format. Please try again.');
			hour_element.value = '';
			hour_element.focus ();
		}
	}
}
function time_in_changed_quick ()
{
	frm = document.forms['frm_t'];
	date_in_element = frm.elements['tdt[time_in_date]'];
	date_in_string = date_in_element.value;
	hour_in_element = frm.elements['tdt[time_in_hour]'];
	hour_in_string = hour_in_element.value;
	duration_element = frm.elements['tdt[work_time]'];
	duration_string = duration_element.value;
	date_out_element = frm.elements['tdt[time_out_date]'];
	date_out_string = date_out_element.value;
	hour_out_element = frm.elements['tdt[time_out_hour]'];
	hour_out_string = hour_out_element.value;
	
	if (is_valid_date (date_in_string) && is_valid_hour (hour_in_string))
	{
		// Check if the duration is defined
		if (is_valid_duration (duration_string))
		{
			set_time_out (date_out_element, hour_out_element, date_in_string, hour_in_string, duration_string);
		}
		// Check if the time out is defined
		else if (is_valid_date (date_out_string) && is_valid_hour (hour_out_string))
		{
			set_duration (duration_element, date_in_string, hour_in_string, date_out_string, hour_out_string);
		}
		
	}
}

function duration_changed_quick ()
{
	frm = document.forms['frm_t'];
	date_in_element = frm.elements['tdt[time_in_date]'];
	date_in_string = date_in_element.value;
	hour_in_element = frm.elements['tdt[time_in_hour]'];
	hour_in_string = hour_in_element.value;
	duration_element = frm.elements['tdt[work_time]'];
	duration_string = duration_element.value;
	date_out_element = frm.elements['tdt[time_out_date]'];
	date_out_string = date_out_element.value;
	hour_out_element = frm.elements['tdt[time_out_hour]'];
	hour_out_string = hour_out_element.value;
	
	if (duration_string != '')
	{
		if (is_valid_duration (duration_string))
		{
			// Check if the time in is defined
			if (is_valid_date (date_in_string) &&  is_valid_hour(hour_in_string))
			{
				set_time_out (date_out_element, hour_out_element, date_in_string, hour_in_string, duration_string);
			}
			// Check if the time out is defined
			else if (is_valid_date (date_out_string) && is_valid_hour (hour_out_string))
			{
				set_time_in (date_in_element, hour_in_element, date_out_string, hour_out_string, duration_string);
			}
		}
		else
		{
			alert ('The duration is invalid, it should be in hh:mm format. Please try again.');
			duration_element.value = '';
			duration_element.focus ();
		}
	}
}

function date_out_changed_quick ()
{
	frm = document.forms['frm_t'];
	date_element = frm.elements['tdt[time_out_date]'];
	date_string = date_element.value;
	
	if (date_string != '')
	{
		if (is_valid_date(date_string)) time_out_changed_quick();
		else
		{
			alert ('The date out is invalid, it should be in dd/mm/yyyy format. Please try again.');
			date_element.value = '';
			date_element.focus ();
		}
	}
}

function hour_out_changed_quick ()
{
	frm = document.forms['frm_t'];
	hour_element = frm.elements['tdt[time_out_hour]'];
	hour_string = hour_element.value;
	
	if (hour_string != '')
	{
		if (is_valid_hour(hour_string)) time_out_changed_quick();
		else
		{
			alert ('The hour in is invalid, it should be in hh:mm format. Please try again.');
			hour_element.value = '';
			hour_element.focus ();
		}
	}
}

function time_out_changed_quick ()
{
	frm = document.forms['frm_t'];
	date_in_element = frm.elements['tdt[time_in_date]'];
	date_in_string = date_in_element.value;
	hour_in_element = frm.elements['tdt[time_in_hour]'];
	hour_in_string = hour_in_element.value;
	duration_element = frm.elements['tdt[work_time]'];
	duration_string = duration_element.value;
	date_out_element = frm.elements['tdt[time_out_date]'];
	date_out_string = date_out_element.value;
	hour_out_element = frm.elements['tdt[time_out_hour]'];
	hour_out_string = hour_out_element.value;
	
	if (is_valid_date (date_out_string) && is_valid_hour (hour_out_string))
	{
		// Check if the time out is defined
		if (is_valid_date (date_in_string) && is_valid_hour (hour_in_string))
		{
			set_duration (duration_element, date_in_string, hour_in_string, date_out_string, hour_out_string);
		}
		// Check if the duration is defined
		else if (is_valid_duration (duration_string))
		{
			set_time_in (date_in_element, hour_in_element, date_out_string, hour_out_string, duration_string);
		}
	}
}

function setDateStringActivityQuick (y,m,d)
{
	fels = document.forms['frm_t'].elements;
	for (i = 0; i < fels.length; i++)
	{
		if (fels[i].name == elname)
		{
			el = fels[i];
		}
	}
	if (m < 10) m = '0'+m;
	y = y + '';
	y = y.replace (/^20/, '');
	
	el.value=d+"/"+m+"/"+y;

	if (elname == 'tdt[time_in_date]')
	{
		date_in_changed_quick ();
	}
	else if (elname == 'tdt[time_out_date]')
	{
		date_out_changed_quick ();
	}
}