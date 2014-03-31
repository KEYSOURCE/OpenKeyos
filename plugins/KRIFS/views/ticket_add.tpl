{assign var="paging_titles" value="KRIFS, Create Ticket"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/activity.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax_krifs.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax_users.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/tiny_mce/tiny_mce.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.easing-1.3.pack.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.fancybox-1.3.4.js" type="text/javascript"></script>

<script language="JavaScript" type="text/javascript">
//<![CDATA[

billable_types = new Array ();
cnt = 0;
{foreach from=$TICKET_TYPES_BILLABLE key=type_id item=type_name}
billable_types[cnt++] = {$type_id};
{/foreach}

{literal}

tinyMCE.init
(
	{
		//General options
		mode: "exact",
		elements : "ticket_detail[comments]",
		theme: "advanced",
		plugins: "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect", 
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor", 
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen", 
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage", 
		theme_advanced_toolbar_location : "top", 
		theme_advanced_toolbar_align : "left", 
		theme_advanced_statusbar_location : "bottom", 
		theme_advanced_resizing : true,
        force_br_newlines: true,
        force_p_newlines: false,
        forced_root_block: ''
	}
);

function check_type ()
{
	frm = document.forms['frm_t'];
	elm_type = frm.elements['ticket[type]'];
	elm_billable = frm.elements['ticket[billable]'];
	
	if (elm_type.selectedIndex > 0)
	{
		current_type = elm_type.options[elm_type.selectedIndex].value;
	
		is_billable = false;
		for (i=0; i<billable_types.length && !is_billable; i++)
		{
			is_billable = (billable_types[i] == current_type);
		}
		
		if (is_billable)
		{
			elm_billable.options[1].selected = true;
		}
		else
		{
			elm_billable.options[0].selected = true;
		}
	}
	
}
{/literal}


//-----------------------------------------------
// Functions for CC users handling
//-----------------------------------------------

// This will store the list of CC users for the ticket about to be created
var cc_users = new UserInfosList ();
var cc_emails = new Array();
// Add any previously selected CC users
{foreach from=$cc_users item=usr_info}
cc_users.appendUser ({$usr_info.user_id}, '{$usr_info.user_name}', {if $usr_info.is_customer_user}true{else}false{/if});
{/foreach}
{assign var="i" value=0}
{foreach from=$cc_emails item=emlx}
	cc_emails[$i] = $emlx;
	{assign var="i" value=$i+1}
{/foreach}

// The XML requester object
var requester = false;

{literal}
// Show the CC users
function showCCUsers ()
{
	var elm_none = document.getElementById ('div_no_cc_users')
	var elm = document.getElementById ('div_cc_users');
	clearAllChildren (elm);
	var elm_input = null;
	var obj = null;
	var i = 0;
	
	if (cc_users.users.length == 0)
	{
		// There are no CC users, so clear the display
		elm.style.display = 'none';
		elm_none.style.display = '';
	}
	else
	{
		// There are CC users, display them
		elm_none.style.display = 'none';
		elm.style.display = '';
		for (i=0; i<cc_users.users.length; i++)
		{
			obj = cc_users.users[i];
			elm.appendChild (document.createTextNode(obj.user_name));
			elm.appendChild (document.createElement ('br'));
			
			elm_input = document.createElement ('input');
			elm_input.name = 'cc_users['+i+'][user_id]';
			elm_input.type = 'hidden';
			elm_input.value = obj.user_id;
			elm.appendChild (elm_input);
			
			elm_input = document.createElement ('input');
			elm_input.name = 'cc_users['+i+'][user_name]';
			elm_input.type = 'hidden';
			elm_input.value = obj.user_name;
			elm.appendChild (elm_input);
			
			elm_input = document.createElement ('input');
			elm_input.name = 'cc_users['+i+'][is_customer_user]';
			elm_input.type = 'hidden';
			elm_input.value = obj.is_customer_user;
			elm.appendChild (elm_input);
		}
	}
	if (cc_emails.length != 0)	
	{
		// There are CC users, display them
		elm_none.style.display = 'none';
		elm.style.display = '';
		for (i=0; i<cc_emails.length; i++)
		{
			obj = cc_emails[i];
			elm.appendChild (document.createTextNode(obj));
			elm.appendChild (document.createElement ('br'));
			
			elm_input = document.createElement ('input');
			elm_input.name = 'cc_emails['+i+']';
			elm_input.type = 'hidden';
			elm_input.value = obj;
			elm.appendChild (elm_input);			
		}
	}
}


// Fetch the default CC recipients for the currently selected customer
function fetchDefaultCCRecipients ()
{
	// Make sure there is no other operation already in progress
	if (requester) return;
	requester = getXmlRequester ();
	
	// Clear the previously selected recipients
	cc_users.clearList ();
	showCCUsers ();
	
	// Get the ID of the current customer
	var frm = document.forms['frm_t'];
	var customers_list = frm.elements['ticket[customer_id]']; 
	var c_customer_id = customers_list.options[customers_list.selectedIndex].value;
	c_customer_id = parseInt (c_customer_id);
	
	if (!isNaN(c_customer_id))
	{
		// Build the request URL and send the request to server
		url = '/krifs/xml_get_customer_cc_recipients?customer_id='+c_customer_id;
		
		requester.open ('GET', url);
		requester.send ('');
		requester.onreadystatechange = stateHandler;
	}
}

// Handler for receiving the XML data with the default CC recipients for the currently selected customer
function stateHandler ()
{
	if (requester)
	{
		if (requester.readyState == 4)
		{
			try
			{
				if (requester.status == 200 && requester.responseXML)
				{
					// Data received OK, convert the XML to UserInfosList and show the new list of recipients
					cc_users = XMLToUserInfosList (requester.responseXML);
					showCCUsers ();
				}
				else
				{
					alert ('ERROR: Failed reading CC recipients data from server');
				}
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error. (Error message: '+error+')');
			}
			requester = false;
		}
	}
	return true;
}


// Displays the pop-up window for selecting CC users
var last_users_window = false;
function showCCUsersPopup ()
{
	var frm = document.forms['frm_t'];
	var customers_list = frm.elements['ticket[customer_id]']; 
	var c_customer_id = customers_list.options[customers_list.selectedIndex].value;
	
	if (c_customer_id == '' || c_customer_id == ' ') alert ('Please select a customer first');
	else
	{
		if (last_users_window) last_users_window.close ();
		var popup_url = '/krifs/popup_ticket_add_cc_users?customer_id=' + c_customer_id;
		last_users_window = window.open (popup_url, 'TicketCCUsers', 'dependent, scrollbars=yes, width=100, height=150, resizable=yes');
	}
	
	return false;
}


// Callback function used by the pop-up window for getting the current list of CC Users
function getCCUsers ()
{
	return cc_users;
}
function trim(str)
{
	a = str.replace(/^\s+/, '');
	return a.replace(/\s+$/, '');
}
// Call-back function used by the pop-up window for passing back the selected objects
function setCCUsers (new_cc_users, emails)
{
	var eml_array = emails.split(";");
	if(eml_array.length!=0)
	{
		for(i=0;i<eml_array.length;i++)
		{
			cc_emails[i] = trim(eml_array[i]);
		}
	}
	cc_users = new_cc_users;	
	showCCUsers ();
}


{/literal}


//-----------------------------------------------
// Functions for linked objects handling
//-----------------------------------------------

// This will store the list of linked objects for the ticket about to be created
var linked_objects = new TicketObjectsList ();
// Add the object passed in the URL for ticket creation, if any
{if $object_id}
linked_objects.appendObject ({$object_class}, '{$TICKET_OBJECT_CLASSES.$object_class}', '{$object_id}', '{$object_name}');
{/if}

// Add objects previously saved in the session
{foreach from=$existing_linked_objects item=obj}
{assign var='obj_class' value=$obj.class}
linked_objects.appendObject ({$obj_class}, '{$TICKET_OBJECT_CLASSES.$obj_class}', '{$obj.id}', '{$obj.object_name}');
{/foreach}

{literal}
// Show the ticket linked objects, if any
function showLinkedObjects ()
{
	var elm_none = document.getElementById ('div_no_linked_objects')
	var elm = document.getElementById ('div_linked_objects');
	clearAllChildren (elm);
	var elm_input = null;
	var obj = null;
	var i = 0;
	
	if (linked_objects.objects.length == 0)
	{
		// There are no linked objects, so clear the display
		elm.style.display = 'none';
		elm_none.style.display = '';
	}
	else
	{
		// There are linked objects, display them
		elm_none.style.display = 'none';
		elm.style.display = '';
		for (i=0; i<linked_objects.objects.length; i++)
		{
			obj = linked_objects.objects[i];
			elm.appendChild (document.createTextNode(obj.object_class_name+': '));
			elm.appendChild (document.createTextNode('#'+obj.object_id+': '+obj.object_name));
			elm.appendChild (document.createElement ('br'));
			
			elm_input = document.createElement ('input');
			elm_input.name = 'linked_objects['+i+'][class]';
			elm_input.type = 'hidden';
			elm_input.value = obj.object_class;
			elm.appendChild (elm_input);
			
			elm_input = document.createElement ('input');
			elm_input.name = 'linked_objects['+i+'][id]';
			elm_input.type = 'hidden';
			elm_input.value = obj.object_id;
			elm.appendChild (elm_input);
			
			elm_input = document.createElement ('input');
			elm_input.name = 'linked_objects['+i+'][object_name]';
			elm_input.type = 'hidden';
			elm_input.value = obj.object_name;
			elm.appendChild (elm_input);
		}
	}
}

// Clears all selected linked objects. This is called automatically when a new customer is selected
function clearLinkedObjects ()
{
	linked_objects = new TicketObjectsList ();
	showLinkedObjects ();
}

// Displays the pop-up window for selecting linked objects
var last_objects_window = false;
function showObjectsPopup ()
{
	var frm = document.forms['frm_t'];
	var customers_list = frm.elements['ticket[customer_id]']; 
	var c_customer_id = customers_list.options[customers_list.selectedIndex].value;
	c_customer_id = parseInt (c_customer_id);
	
	if (isNaN(c_customer_id)) alert ('Please select a customer first');
	else
	{
		if (last_objects_window) last_objects_window.close ();
		var popup_url = '/krifs/popup_ticket_add_objects?customer_id=' + c_customer_id;
		last_objects_window = window.open (popup_url, 'TicketObjects', 'dependent, scrollbars=yes, width=100, height=100, resizable=yes');
	}
	
	return false;
}
$(document).ready(function () {
    
    $("#show_objects_popup").click(function() {
        var frm = document.forms['frm_t'];
        var customers_list = frm.elements['ticket[customer_id]'];
        var c_customer_id = customers_list.options[customers_list.selectedIndex].value;
        c_customer_id = parseInt (c_customer_id);

        if (isNaN(c_customer_id))
        {
            alert ('Please select a customer first');
        } else
        {
                var link_url = '/krifs/popup_ticket_add_objects?customer_id=' + c_customer_id;

                $.fancybox({
                        'transitionIn'	:	'elastic',
                        'transitionOut'	:	'elastic',
                        'type'          :       'iframe',
                        'href'          :       link_url
                });
        }
        return false;
    });
    
});


// Displays the pop-up window for selecting computers/AD Users using users search
function showSearchByUserPopup()
{
	var frm = document.forms['frm_t'];
	var customers_list = frm.elements['ticket[customer_id]']; 
	var c_customer_id = customers_list.options[customers_list.selectedIndex].value;
	c_customer_id = parseInt (c_customer_id);
	
	if (isNaN(c_customer_id)) alert ('Please select a customer first');
	else
	{
		if (last_objects_window) last_objects_window.close ();
		var popup_url = '/krifs/popup_ticket_search_by_user?customer_id=' + c_customer_id;
		var popup_url = '/krifs/popup_ticket_search_by_user?customer_id=' + c_customer_id;
		last_objects_window = window.open (popup_url, 'TicketObjects', 'dependent, scrollbars=yes, width=100, height=100, resizable=yes');
	}
	
	return false;
}

// Callback function used by the pop-up window for getting the current list of objects
function getLinkedObjects ()
{
	return linked_objects;
}

// Call-back function used by the pop-up window for passing back the selected objects
function setLinkedObjects (new_objects)
{
	linked_objects = new_objects;
	showLinkedObjects ();
}

// Call-back function used by the pop-window for adding a single object to the list
function appendLinkedObject (object_class, object_class_name, object_id, object_name)
{
	if (!linked_objects.hasObject (object_class, object_id))
	{
		linked_objects.appendObject (object_class, object_class_name, object_id, object_name);
		showLinkedObjects ();
	}
}

{/literal}
//]]
</script>


<h1>Create Ticket</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="4">Ticket information</td>
	</tr>
	</thead>

	<tr>
		<td class="highlight">Customer: </td>
		<td class="post_highlight" colspan="3">
			<select name="ticket[customer_id]" onchange="clearLinkedObjects(); fetchDefaultCCRecipients();">
				<option value="">[Select customer]</option>
				{html_options options=$customers selected=$ticket->customer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Subject: </td>
		<td class="post_highlight" colspan="3"><input type="text" name="ticket[subject]" value="{$ticket->subject}" size="100"></td>
	</tr>

		
	<tr>
		<td width="15%" class="highlight">Status: </td>
		<td width="40%" class="post_highlight">
			{assign var="status" value=$ticket->status}
			<select name='ticket[status]'>
				{html_options options=$TICKET_STATUSES selected=$ticket->status}
			</select>
		</td>
		
		<td width="10%" class="highlight">Source: </td>
		<td width="35%" class="post_highlight">
			<select name="ticket[source]">
			{html_options options=$TICKET_SOURCES selected=$ticket->source}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Type: </td>
		<td class="post_highlight">
			<select name="ticket[type]" onchange="check_type();">
				<option value="">[Select type]</option>
				{html_options options=$TICKET_TYPES selected=$ticket->type}
			</select>
		</td>
		
		<td class="highlight">Priority: </td>
		<td class="post_highlight">
			<select name="ticket[priority]">
			{html_options options=$TICKET_PRIORITIES selected=$ticket->priority}
			</select>
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Assigned to:</td>
		<td class="post_highlight">
			<select name="ticket[assigned_id]">
				<option value="0">[None]</option>
				{html_options options=$users selected=$ticket->assigned_id}
			</select>
		</td>
		
		<td class="highlight">Owner:</td>
		<td class="post_highlight">
			<select name="ticket[owner_id]">
			{html_options options=$users selected=$ticket->owner_id}
			</select>
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Private</td>
		<td class="post_highlight">
			<select name="ticket[private]">
				<option value="0">Public</option>
				<option value="1" {if $ticket->private}selected{/if}>Private</option>
			</select>
		</td>
		<td class="highlight">Deadline: </td>
		<td class="post_highlight">
			<input type="text" size="12" name="ticket[deadline]" 
				value="{if $ticket->deadline}{$ticket->deadline|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
			>
			
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm_t', 'ticket[deadline]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
	</tr>
	<tr>
		<td class="highlight">Billable</td>
		<td class="post_highlight">
			<select name="ticket[billable]">
				<option value="0">No</option>
				<option value="1" {if $ticket->billable}selected{/if}>Yes</option>
			</select>
		</td>
		<td class="highlight">PO Code</td>
		<td class="post_highlight">
			<input type="text" size=12 name="ticket[po]" value="{$ticket->po}" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Order/Subscr.:</td>
		<td class="post_highlight" colspan="3">
			<select name="ticket[customer_order_id]" style="width:280px">
				<option value="">[None]</option>
				{html_options options=$available_orders_list selected=$ticket->customer_order_id} 
			</select>
		</td>
	</tr>
	
	<tr class="head">
		<td>Linked objects</td>
		<td class="post_highlight">
			{*<a href="#" onclick="return showObjectsPopup();">[ Edit &#0187; ]</a>*}
                        <a href="#" id="show_objects_popup" class="iframe">[ Edit &#0187; ]</a>
			<a href="#" onclick="return showSearchByUserPopup();">[ Search by user &#0187; ]</a>
		</td>
		<td>CC Users:</td>
		<td class="post_highlight"><a href="#" onclick="return showCCUsersPopup();">[ Edit &#0187; ]</a></td>
	</tr>
	<tr>
		<td> </td>
		<td class="post_highlight">
			<div id="div_no_linked_objects" class="light_text">[No linked objects]</div>
			<div id="div_linked_objects" style="display:none;"></div>
			
			<script language="JavaScript" type="text/javascript">showLinkedObjects();</script>
		</td>
		<td> </td>
		<td class="post_highlight">
			<div id="div_no_cc_users" class="light_text">[No CC users]</div>
			<div id="div_cc_users" style="display:none;"></div>
			
			<script language="JavaScript" type="text/javascript">showCCUsers();</script>
		</td>
	</tr>

	
	
<!-- Create a new entry for this ticket -->

<script language="JavaScript" type="text/javascript">
//<![CDATA[

// The name of last selected activity
{assign var="activity_id" value=$ticket_detail->activity_id}
var activity_name = "{$action_types.$activity_id}";

// The name of last selected location
{assign var="location_id" value=$ticket_detail->location_id}
var location_name = "{$locations_list.$location_id}";



{literal}
var last_browse_window = false;
function show_duration_popup (anchor_name)
{
	frm = document.forms['frm_t'];
	custs_list = frm.elements['ticket[customer_id]'];
	selected_customer_id = custs_list.options[custs_list.selectedIndex].value;
	
	if (selected_customer_id)
	{
		if (last_browse_window) last_browse_window.close ();
		
		// If an work time has not been previously set, mark this as billable
		if (frm.elements['ticket_detail[work_time]'].value == 0 && (frm.elements['ticket[billable]'].selectedIndex>0))
		{
			frm.elements['ticket_detail[billable]'].value = 1;
		}
		
		popup_url = '/krifs/popup_activity?show_location=1&title=' + escape('Action type') +'&customer_id='+selected_customer_id;
		pass_vars = new Array ('activity_id', 'is_continuation', 'billable', 'intervention_report_id', 'time_in', 'work_time', 'time_out', 'location_id', 'time_start_travel_to', 'time_end_travel_to', 'time_start_travel_from', 'time_end_travel_from');
		for (i=0; i<pass_vars.length; i++)
		{
			popup_url = popup_url + '&'+pass_vars[i]+'=' + escape(frm.elements['ticket_detail['+pass_vars[i]+']'].value);
		}
		
		position = getAnchorPosition (anchor_name);
		x = position.x;
		y = position.y - 500;
		if (!isNaN(window.screenX)) x = x+window.screenX;
		x = x - 200;
		last_browse_window = window.open (popup_url, 'Duration', 'dependent, scrollbars=yes, width=100, height=100, resizable=yes, left='+x+', top='+y);
	}
	else
	{
		alert ('Please select a customer first.');
	}
	return false;
}

$(document).ready(function () {

    $("#anchor_new_worktime_fb").click(function() {
        frm = document.forms['frm_t'];
	custs_list = frm.elements['ticket[customer_id]'];
	selected_customer_id = custs_list.options[custs_list.selectedIndex].value;

	if (selected_customer_id)
	{
		if (last_browse_window) last_browse_window.close ();

		// If an work time has not been previously set, mark this as billable
		if (frm.elements['ticket_detail[work_time]'].value == 0 && (frm.elements['ticket[billable]'].selectedIndex>0))
		{
			frm.elements['ticket_detail[billable]'].value = 1;
		}

		popup_url = '/krifs/popup_activity?show_location=1&title=' + escape('Action type') +'&customer_id='+selected_customer_id;
		pass_vars = new Array ('activity_id', 'is_continuation', 'billable', 'intervention_report_id', 'time_in', 'work_time', 'time_out', 'location_id', 'time_start_travel_to', 'time_end_travel_to', 'time_start_travel_from', 'time_end_travel_from');
		for (i=0; i<pass_vars.length; i++)
		{
			popup_url = popup_url + '&'+pass_vars[i]+'=' + escape(frm.elements['ticket_detail['+pass_vars[i]+']'].value);
		}

                $.fancybox({
                        'transitionIn'	:	'elastic',
                        'transitionOut'	:	'elastic',
                        'type'          :       'iframe',
                        'href'          :       popup_url
                });
        } else
        {
            alert ('Please select a customer first.');
        }
	return false;
    });

});

// Needed to receive data from the child duration popup window
function pass_data_duration (activity)
{
	load_frm_activity (activity);
}


// Loads a form and the display fields with data from an Activity object
function load_frm_activity (activity)
{
	frm = document.forms['frm_t'];
	frm.elements['ticket_detail[activity_id]'].value = activity.activity_id;
	frm.elements['ticket_detail[is_continuation]'].value = activity.is_continuation;
	frm.elements['ticket_detail[billable]'].value = activity.billable;
	frm.elements['ticket_detail[time_in]'].value = activity.time_in;
	frm.elements['ticket_detail[work_time]'].value = activity.work_time;
	frm.elements['ticket_detail[time_out]'].value = activity.time_out;
	frm.elements['ticket_detail[location_id]'].value = activity.location_id;

        frm.elements['ticket_detail[time_start_travel_to]'].value = activity.time_start_travel_to;
	frm.elements['ticket_detail[time_end_travel_to]'].value = activity.time_end_travel_to;
	frm.elements['ticket_detail[time_start_travel_from]'].value = activity.time_start_travel_from;
	frm.elements['ticket_detail[time_end_travel_from]'].value = activity.time_end_travel_from;
	
	elm = document.getElementById ('action_type_div');
	if (activity.activity_id > 0) elm.lastChild.nodeValue = activity.activity_name;
	else elm.lastChild.nodeValue = '--';
	
	elm = document.getElementById ('is_continuation_div');
	if (activity.is_continuation == 1) elm.style.display = 'block';
	else elm.style.display = 'none';
	
	elm = document.getElementById ('work_time_div');
	if (activity.work_time > 0)
	{
		str = activity.get_duration_string () + ' hrs., on ';
		str = str + activity.get_time_in_date_string() + ' ' + activity.get_time_in_time_string() + '; ';
		str = str + activity.location_name;
		elm.lastChild.nodeValue = str;
	}
	else elm.lastChild.nodeValue = '--';
	
	elm = document.getElementById ('billable_div');
	if (activity.billable == 1) elm.lastChild.nodeValue = 'Yes';
	else elm.lastChild.nodeValue = 'No';
	
	//alert("time_in: "+eval(activity));
	if(eval(activity.time_in) != 0 && eval(activity.time_out) != 0)
	{
		frm.elements['tdt[time_in_date]'].value = activity.get_time_in_date_string();
		frm.elements['tdt[time_in_hour]'].value = activity.get_time_in_time_string();
		
		frm.elements['tdt[time_out_date]'].value = activity.get_time_out_date_string();
		frm.elements['tdt[time_out_hour]'].value = activity.get_time_out_time_string();
		
		frm.elements['tdt[work_time]'].value = activity.get_duration_string ();		
	}
	if(activity.activity_id!=frm.elements['tdt[activity_id]'].value || activity.location_id != frm.elements['tdt[location_id]'].value)
	{
		document.getElementById('act_defaults').style.display = 'none';
	}
	else
	{
		elm = document.getElementById ('action_type_div');
		elm.lastChild.nodeValue = {/literal}'{$acttype->erp_code} {$acttype->erp_name}'{literal};
		elm = document.getElementById ('work_time_div');
		elm.lastChild.nodeValue += {/literal}'{$location->name}'{literal};
	}

        elm = document.getElementById ('travel_to_div');
	if (activity.time_start_travel_to > 0)
	{
		str = '- Travel to customer: ';
		str = str + ts_to_time_string (activity.time_start_travel_to) + ' - ' + ts_to_time_string (activity.time_end_travel_to);
		elm.lastChild.nodeValue = str;
		elm.style.display = 'block';
	}
	else elm.style.display = 'none';

	elm = document.getElementById ('travel_from_div');
	if (activity.time_start_travel_from > 0)
	{
		str = '- Travel from customer: ';
		str = str + ts_to_time_string (activity.time_start_travel_from) + ' - ' + ts_to_time_string (activity.time_end_travel_from);
		elm.lastChild.nodeValue = str;
		elm.style.display = 'block';
	}
	else elm.style.display = 'none';
}

// Initializes an Activity object with the data from the form
function load_activity_obj ()
{
	activity = new Activity ();
	frm = document.forms['frm_t'];
	activity.activity_id = frm.elements['ticket_detail[activity_id]'].value;
	activity.activity_name = activity_name;
	activity.set_times (frm.elements['ticket_detail[time_in]'].value, frm.elements['ticket_detail[work_time]'].value, frm.elements['ticket_detail[time_out]'].value);
        activity.set_travel_times (
		frm.elements['ticket_detail[time_start_travel_to]'].value, frm.elements['ticket_detail[time_end_travel_to]'].value,
		frm.elements['ticket_detail[time_start_travel_from]'].value, frm.elements['ticket_detail[time_end_travel_from]'].value
	);
	activity.location_id = frm.elements['ticket_detail[location_id]'].value;
	activity.location_name = location_name;
	activity.is_continuation = frm.elements['ticket_detail[is_continuation]'].value;
	activity.billable = frm.elements['ticket_detail[billable]'].value;
	return activity;
}

//sets quick defaults and passes all the data to needed values
function set_quick_defaults()
{	
	document.getElementById('act_defaults').style.display = "block";
	var frm = document.forms['frm_t'];
	frm.elements['ticket_detail[activity_id]'].value = frm.elements['tdt[activity_id]'].value;	
	frm.elements['ticket_detail[billable]'].value = 1;
	
	
	if(is_valid_date(frm.elements['tdt[time_in_date]'].value) && is_valid_hour(frm.elements['tdt[time_in_hour]'].value))
	{
		frm.elements['ticket_detail[time_in]'].value = date_time_to_ts(frm.elements['tdt[time_in_date]'].value, frm.elements['tdt[time_in_hour]'].value);
	}
	if(is_valid_duration(frm.elements['tdt[work_time]'].value))
	{
		frm.elements['ticket_detail[work_time]'].value = get_minutes(frm.elements['tdt[work_time]'].value);
	}
	if(is_valid_date(frm.elements['tdt[time_out_date]'].value) && is_valid_hour(frm.elements['tdt[time_out_hour]'].value))
	{
		frm.elements['ticket_detail[time_out]'].value = date_time_to_ts(frm.elements['tdt[time_out_date]'].value, frm.elements['tdt[time_out_hour]'].value);
	}
	frm.elements['ticket_detail[location_id]'].value = frm.elements['tdt[location_id]'].value;
	
	var act = load_activity_obj();
	act.time_start_travel_to = 0;
	act.time_end_travel_to = 0;
	act.time_start_travel_from = 0;
	act.time_end_travel_from = 0;
	load_frm_activity(act);
	
}

var cal_activity = new CalendarPopup(); 
cal_activity.setReturnFunction('setDateStringActivityQuick'); 
function showCalendarSelectorActivityQuick (name_form, name_element, anchor_name)
{
	elname = name_element;
	frm_name = name_form;
	cal_activity.showCalendar(anchor_name,getDateString());
}

{/literal}
//]]>
</script>

	<tr class="head">
		<td colspan="2">
			Detail:
			<input type="hidden" name="ticket_detail[activity_id]" value="{$ticket_detail->activity_id}" />
			<input type="hidden" name="ticket_detail[is_continuation]" value="{$ticket_detail->is_continuation}" />
			<input type="hidden" name="ticket_detail[billable]" value="{$ticket_detail->billable}" />
			<input type="hidden" name="ticket_detail[intervention_report_id]" value="{$ticket_detail->intervention_report_id}" />
			<input type="hidden" name="ticket_detail[time_in]" value="{$ticket_detail->time_in}" />
			<input type="hidden" name="ticket_detail[work_time]" value="{$ticket_detail->work_time}" />
			<input type="hidden" name="ticket_detail[time_out]" value="{$ticket_detail->time_out}" />
			<input type="hidden" name="ticket_detail[location_id]" value="{$ticket_detail->location_id}" />
			<input type="hidden" name="ticket_detail[customer_order_id]" value="{$ticket_detail->customer_order_id}"/>
			<input type="hidden" name="ticket_detail[for_subscription]" value="{$ticket_detail->for_subscription}"/>

            <input type="hidden" name="ticket_detail[time_start_travel_to]" value="{$ticket_detail->time_start_travel_to}" />
            <input type="hidden" name="ticket_detail[time_end_travel_to]" value="{$ticket_detail->time_end_travel_to}" />
            <input type="hidden" name="ticket_detail[time_start_travel_from]" value="{$ticket_detail->time_start_travel_from}" />
            <input type="hidden" name="ticket_detail[time_end_travel_from]" value="{$ticket_detail->time_end_travel_from}" />
		</td>
		
		<td>Private:</td>
		<td class="post_highlight">
			<input type="checkbox" name="ticket_detail[private]" class="checkbox" value="1" {if $ticket_detail->private}checked{/if}> {$ticket_detail->private}
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Work time:</td>
		<td class="post_highlight">
			<div id="work_time_div" style="display:inline;">--</div>
                        <div id="travel_to_div" style="display:none;">&nbsp;</div>
			<div id="travel_from_div" style="display:none;">&nbsp;</div>
		</td>
		<td class="highlight">Billable:</td>
		<td class="post_highlight">
			<div id="billable_div" style="display:inline;">--</div>
		</td>
	</tr>
	<tr>
		<td class="highlight" nowrap="nowrap">
			Action type:&nbsp;&nbsp;
			{* <a href="" onclick="return show_duration_popup('anchor_new_worktime');" id="anchor_new_worktime">[ Edit &#0187; ]</a> *}
                        <a href="#" id="anchor_new_worktime_fb">[ Edit &#0187; ]</a>
		</td>
		<td class="post_highlight" colspan="3">
			<div id="action_type_div" style="display:block;">--</div>
			<div id="is_continuation_div" style="display:none; font-style:italic;">[Continuation]</div>
		</td>
	</tr>
	<tr>
		<td class="highlight" nowrap="nowrap">
			Quick activity edit: &nbsp;&nbsp;
		</td>
		<td class="post_highlight" colspan="3">
			<ul style="list-style-type: none; display: block;">
				<li>
					Time in:
					<input type="text" size="12" name="tdt[time_in_date]" onchange="date_in_changed_quick();"
						value="{$time_in|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
					/>
					
					{literal}
					<a HREF="#" onClick="showCalendarSelectorActivityQuick ('frm_t', 'tdt[time_in_date]', 'anchor_calendar_in'); return false;" 
						name="anchor_calendar_in" id="anchor_calendar_in"
						><img src="/images/icon_cal.gif" alt="calendar" border="0" /></a>
					{/literal}
					
					<input type="text" name="tdt[time_in_hour]" size="6" onchange="hour_in_changed_quick();"
						value="{$time_in|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}"
					/> (h:m)
				</li>
				<li>
					Duration:					
					<input type="text" name="tdt[work_time]" size="8" onchange="duration_changed_quick();"
					value="{$duration|format_interval_minutes}"
					/> (h:m)
				</li>
				<li>
					Time out:
					<input type="text" size="12" name="tdt[time_out_date]" onchange="date_out_changed_quick();"
						value="{$time_out|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
					/>
					
					{literal}
					<a HREF="#" onClick="showCalendarSelectorActivityQuick ('frm_t', 'tdt[time_out_date]', 'anchor_calendar_out'); return false;" 
						name="anchor_calendar_out" id="anchor_calendar_out"
						><img src="/images/icon_cal.gif" alt="calendar" border="0" /></a>
					{/literal}
					
					<input type="text" name="tdt[time_out_hour]" size="6" onchange="hour_out_changed_quick();"
						value="{$time_out|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}"
					/> (h:m)
				</li>
			</ul>
			<ul id="act_defaults" style="list-style-type: none; display: none;">
				<li>										
					<b>Location:</b> {$location->name}
					<input type="hidden" name="tdt[location_id]" value="{$location->id}" />
					
				</li>
				<li>
					<b>Action type:</b> [{$acttype->erp_code}] {$acttype->erp_name}
					<input type="hidden" name="tdt[activity_id]" value="{$acttype->id}" />
				</li>
			</ul>			
			<input type="button" value="Set" onclick="set_quick_defaults()" />
		</td>
	</tr>
	<tr>
		<td class="highlight" nowrap="nowrap">
			Work marker: &nbsp;&nbsp;
		</td>
		<td class="post_highlight" colspan="3">
			<input type="checkbox" name="work_marker" class="checkbox" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Interv. report:</td>
		<td class="post_highlight" colspan="3">
			{if $available_interventions_list}
				<select name="ticket_detail[intervention_report_id]" style="width:500px;">
					<option value="">[None]</option>
					{html_options options=$available_interventions_list selected=$ticket_detail->intervention_report_id}
				</select>
			{else}
				<font class="light_text">[None available]</font>
			{/if}
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Comments: </td>
		<td class="post_highlight" colspan="3">
			<textarea id="ticket_detail[comments]" name="ticket_detail[comments]" rows="10" cols="100">{$ticket_detail->comments|escape}</textarea>
		</td>
	</tr>
	
</table>
<p/>


<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">

</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_type ();
load_frm_activity (load_activity_obj ());
//]]>
</script>
