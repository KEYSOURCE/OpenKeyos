{assign var="paging_titles" value="Customers, Customer Report"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

{literal}
<script language="JavaScript" src="/javascript/CalendarPopup.js"></script>


<script language="JavaScript">
//<![CDATA[

var rep_names = new Array ('report_computers', 'report_peripherals', 'report_warranties', 'report_software', 'report_licenses', 'report_users', 'report_free_space', 'report_backups', 'report_av_status', 'report_av_hist');

// Check and select all the reports
function check_all_reports ()
{
	frm = document.forms['report_frm'];
	for (i=0; i<rep_names.length; i++)
	{
		frm.elements['filter[selected_report]['+rep_names[i]+']'].checked = true;
		change_selected (rep_names[i]);
	}
	return false;
}

// Un-check and un-select all the reports
function uncheck_all_reports ()
{
	frm = document.forms['report_frm'];
	for (i=0; i<rep_names.length; i++)
	{
		frm.elements['filter[selected_report]['+rep_names[i]+']'].checked = false;
		change_selected (rep_names[i]);
	}
	return false;
}

function change_selected (report_name)
{
	frm = document.forms['report_frm']
	report_div = document.getElementById (report_name)
	report_enabled = frm.elements['filter[selected_report]['+report_name+']'].checked

	if (report_enabled)
	{
		report_div.style.display = 'block'
		report_div.style.visibility = 'visible'
	}
	else
	{
		report_div.style.visibility = 'hidden'
		report_div.style.display = 'none'
	}
}

function move_item (src_list_name, dest_list_name)
{
	frm = document.forms['report_frm']
	src_list = frm.elements[src_list_name]
	dest_list = frm.elements[dest_list_name]
	
	for (i=src_list.options.length-1; i>=0; i--)
	{
		if (src_list.options[i].selected)
		{
			opt = new Option (src_list.options[i].text, src_list.options[i].value, false, false)
			dest_list.options[dest_list.options.length] = opt
			src_list.options[i] = null
		}
	}
}

function move_all (src_list_name, dest_list_name)
{
	frm = document.forms['report_frm']
	src_list = frm.elements[src_list_name]
	
	for (i=0; i<src_list.options.length; i++)
	{
		src_list.options[i].selected = true;
	}
	move_item (src_list_name, dest_list_name)
	
	return false;
}

function prepare_submit ()
{
	frm = document.forms['report_frm']
	lists_list = new Array ('filter[report_free_space][partitions][]', 'filter[report_backups][computers][]', 'filter[report_av_hist][computers][]');
	
	for (i=0; i<lists_list.length; i++)
	{
		element = frm.elements[lists_list[i]]
		if (element)
		{
			for (j=0; j<element.options.length; j++)
			{
				element.options[j].selected = true;
			}
		}
	}
	
	return true;
}
//]]>
</script>
{/literal}


<h1>Customer Report</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="report_frm" onSubmit="return prepare_submit();">
{$form_redir}


{if !$customer}
	<!-- No customer selected -->
	Customer:<br>
	<select name="filter[customer_id]">
		<option value="">[Select]</option>
		{html_options options=$customers_list selected=$current_locked_customer_id}
	</select>
	
	<p>
	<input type="submit" name="select" value="Select">

{else}
	<!-- There is a customer selected -->

	<table width="98%" class="list">
		<thead>
		<tr>
			<td>Customer:</td>
			<td class="post_highlight" colspan="3">
				{$customer->name} ({$customer->id})
				&nbsp;&nbsp;&nbsp;
				<a href="/?cl=customer&op=customer_report&change_customer=1">Change &#0187;</a>
			</td>
		</tr>
		</thead>
		
		<tr>
			<td width="15%" class="highlight">Title:</td>
			<td width="35%" class="post_highlight">
				<input type="text" name="filter[title]" value="{$customer->name} Report" size="30">
			</td>
			<td width="15%" class="highlight">Cover page:</td>
			<td width="35%" class="post_highlight">
				<select name="filter[show_cover_page]">
					<option value="yes">Yes</option>
					<option value="no" {if $filter.show_cover_page=='no'}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">Output:</td>
			<td class="post_highlight">
				<select name="filter[format]">
					<option value="wordml">MS WordML</option>
					<option value="pdf" {if $filter.format=='pdf'}selected{/if}>PDF</option>
					<option value="xml" {if $filter.format=='xml'}selected{/if}>XML</option>
				</select>
			</td>
			<td class="highlight">Table of contents:</td>
			<td class="post_highlight">
				<select name="filter[show_contents]">
					<option value="yes">Yes</option>
					<option value="no" {if $filter.show_contents=='no'}selected{/if}>No</option>
				</select>
				(PDF only)
			</td>
		</tr>
		<tr>
			<td class="highlight">Interval:</td>
			<td class="post_highlight">
				<select name="filter[interval][month_start]">
				<option value="-1">[This month]</option>
				{html_options values=$months_interval output=$months_interval selected=$filter.report_free_space.month_start}
				</select>
				-
				<select name="filter[interval][month_end]">
				<option value="-1">[This month]</option>
				{html_options values=$months_interval output=$months_interval selected=$filter.report_free_space.month_end}
				</select>
			</td>
			<td class="highlight">Sections cover pages:</td>
			<td class="post_highlight">
				<select name="filter[show_section_cover_pages]">
					<option value="yes">Yes</option>
					<option value="no" {if $filter.show_section_cover_pages=='no'}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">Order by:</td>
			<td class="post_highlight">
				<select name="filter[order_by]">
					<option value="name">Name</option>
					<option value="asset_no" {if $filter.order_by=='asset_no'}selected{/if}>Asset number</option>
				</select>
				(Computers and peripherals)
			</td>
			<td class="highlight">Charts size:</td>
			<td class="post_highlight">
				<select name="filter[charts_size]">
					<option value="1">600 x 300</option>
					<option value="2" {if $filter.charts_size==2}selected{/if}>600 x 400</option>
					<option value="3" {if $filter.charts_size==3}selected{/if}>480 x 280</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">Reports:</td>
			<td class="post_highlight" colspan="3">
				[<a href="#" onclick="return check_all_reports();">Check all</a>]
				[<a href="#" onclick="return uncheck_all_reports();">Un-check all</a>]
			</td>
		</tr>
	</table>
	<p>
	
	<table class="list" width="95%">
		<tr>
			<td colspan="2">
				<h2>Technical Information</h2>
			</td>
		</tr>
		
		<tr>
			<!-- Computers -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_computers]" 
					onClick="change_selected ('report_computers')" style="border:none;"
					{if $filter.selected_report.report_computers}checked{/if}
				>
				Computers
			</td>
			<td>
				<div id="report_computers" class="light_text">
				[No options]
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_computers');
				</script>
				{/literal}
			</td>
		</tr>
		<tr>
			<!-- Peripherals -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_peripherals]" 
					onClick="change_selected ('report_peripherals')" style="border:none;"
					{if $filter.selected_report.report_peripherals}checked{/if}
				>
				Peripherals
			</td>
			<td>
				<div id="report_peripherals">
					<input type="checkbox" name="filter[report_peripherals][summary]"
						{if $filter.report_peripherals.summary}checked{/if}
					> Include summary<br>
					<input type="checkbox" name="filter[report_peripherals][details]"
						{if $filter.report_peripherals.details}checked{/if}
					> Include details
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_peripherals');
				</script>
				{/literal}
			</td>
		</tr>
		<tr>
			<!-- Warranties -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_warranties]" 
					onClick="change_selected ('report_warranties')" style="border:none;"
					{if $filter.selected_report.report_warranties}checked{/if}
				>
				Warranties
			</td>
			<td>
				<div id="report_warranties">
				<table class="no_borders">
					<tr><td width="160">
						<input type="checkbox" name="filter[report_warranties][charts]"
							{if $filter.report_warranties.charts}checked{/if}
						> Include lifecycle charts<br>
						<input type="checkbox" name="filter[report_warranties][details]"
							{if $filter.report_warranties.details}checked{/if}
						> Include details
					</td><td>
						<input type="checkbox" name="filter[report_warranties][computers]"
							{if $filter.report_warranties.computers}checked{/if}
						> Computers<br/>
						<input type="checkbox" name="filter[report_warranties][ad_printers]"
							{if $filter.report_warranties.ad_printers}checked{/if}
						> AD Printers<br/>
						<input type="checkbox" name="filter[report_warranties][peripherals]"
							{if $filter.report_warranties.peripherals}checked{/if}
						> Peripherals<br/>
					</td></tr>
				</table>
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_warranties'); 
				</script>
				{/literal}
			</td>
		</tr>
		<tr>
			<!-- Installed Software -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_software]" 
					onClick="change_selected ('report_software')" style="border:none;"
					{if $filter.selected_report.report_software}checked{/if}
				>
				Installed software
			</td>
			<td>
				<div id="report_software" class="light_text">
				[No options]
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_software');
				</script>
				{/literal}
			</td>
		</tr>
		<tr>
			<!-- All installed software by computer-->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_all_software]" 
					onClick="change_selected ('report_all_software')" style="border:none;"
					{if $filter.selected_report.report_all_software}checked{/if}
				>
				All installed software by computer
			</td>
			<td>
				<div id="report_all_software" class="light_text">
				[No options]
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_all_software');
				</script>
				{/literal}
			</td>
		</tr>
		<tr>
			<!-- Software Licenses -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_licenses]" 
					onClick="change_selected ('report_licenses')" style="border:none;"
					{if $filter.selected_report.report_licenses}checked{/if}
				>
				Software licenses
			</td>
			<td>
				<div id="report_licenses" class="light_text">
				[No options]
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_licenses');
				</script>
				{/literal}
			</td>
		</tr>
		<tr>
			<!-- Users -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_users]" 
					onClick="change_selected ('report_users')" style="border:none;"
					{if $filter.selected_report.report_users}checked{/if}
				>
				Users
			</td>
			<td>
				<div id="report_users" class="light_text">
				[No options]
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_users');
				</script>
				{/literal}
			</td>
		</tr>
		
		<tr>
			<td colspan="2">
				<h2>Statistics</h2>
			</td>
		</tr>
		
		<tr>
			<!-- Free disk space -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_free_space]"
					onClick="change_selected ('report_free_space')" style="border:none;"
					{if $filter.selected_report.report_free_space}checked{/if}
				>
				Free disk space
			</td>
			<td>
				<div id="report_free_space">
					<table>
						<tr>
							<td colspan="2">
								<input type="checkbox" name="filter[report_free_space][show_charts]"
									{if $filter.report_free_space.show_charts}checked{/if}
								value="1" class="checkbox"/> Show charts<br/>
								<input type="checkbox" name="filter[report_free_space][show_numbers]"
									{if $filter.report_free_space.show_numbers}checked{/if}
								value="1" class="checkbox"/> Show values<br/>
							</td>
						</tr>
						<tr>
							<td width="50%">
								Included partitions:<br>
								<select name="filter[report_free_space][partitions][]" multiple size="8" style="width: 280px;"
								onDblClick="move_item('filter[report_free_space][partitions][]', 'filter[report_free_space][partitions_list][]')"
								>
									{foreach from=$selected_disks_list key=id item=name}
										<option value="{$id}">{$name}</option>
									{/foreach}
								</select>
							</td>
							<td width="50%">
								Available partitions:<br>
								<select name="filter[report_free_space][partitions_list][]" multiple size="8" style="width: 280px;" 
								onDblClick="move_item('filter[report_free_space][partitions_list][]', 'filter[report_free_space][partitions][]')"
								>
									{foreach from=$available_disks_list key=id item=name}
										<option value="{$id}">{$name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					</table>
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_free_space');
				</script>
				{/literal}
			</td>
		</tr>
		
		<tr>
			<!-- Backups age -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_backups]"
					onClick="change_selected ('report_backups')" style="border:none;"
					{if $filter.selected_report.report_backups}checked{/if}
				>
				Backups:
			</td>
			<td>
				<div id="report_backups">
					<table width="100%">
						<tr>
							<td>
							<input type="checkbox" name="filter[report_backups][rep_age]" {if $filter.report_backups.rep_age}checked{/if}
							value="1" class="checkbox"/> Backups age<br/>
							<input type="checkbox" name="filter[report_backups][rep_size]" {if $filter.report_backups.rep_size}checked{/if}
							value="1" class="checkbox"/> Backups sizes<br/>
							</td>
							<td>
								<input type="checkbox" name="filter[report_backups][show_charts]" {if $filter.report_backups.show_charts}checked{/if}
								value="1" class="checkbox"/> Show charts<br/>
								<input type="checkbox" name="filter[report_backups][show_numbers]" {if $filter.report_backups.show_numbers}checked{/if}
								value="1" class="checkbox"/> Show values<br/>
							</td>
						</tr>
						<tr>
							<td width="50%">
								Included computers:<br>
								<select name="filter[report_backups][computers][]" multiple size="4" style="width: 280px;"
								onDblClick="move_item('filter[report_backups][computers][]', 'filter[report_backups][computers_list][]')"
								>
									{foreach from=$selected_backup_computers key=id item=name}
										<option value="{$id}">{$name}</option>
									{/foreach}
								</select>
							</td>
							<td width="50%">
								Available computers:<br>
								<select name="filter[report_backups][computers_list][]" multiple size="4" style="width: 280px;" 
								onDblClick="move_item('filter[report_backups][computers_list][]', 'filter[report_backups][computers][]')"
								>
									{foreach from=$available_backup_computers key=id item=name}
										<option value="{$id}">{$name}</option>
									{/foreach}
								</select>
							</td>
						</tr> 
					</table>
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_backups');
				</script>
				{/literal}
			</td>
		</tr>
		
		<tr>
			<!-- AV Updates Status -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_av_status]"
					onClick="change_selected ('report_av_status')" style="border:none;"
					{if $filter.selected_report.report_av_status}checked{/if}
				>
				AV updates status:
			</td>
			<td>
				<div id="report_av_status">
					Date: <input type="text" size="12" name="filter[report_av_status][date]" 
					value="{if $filter.report_av_status.date}{$filter.report_av_status.date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}">
						
					<a HREF="#"
					onClick="showCalendarSelector('report_frm', 'filter[report_av_status][date]', 'anchor_av_status'); return false;" 
					name="anchor_av_status" id="anchor_av_status"
					><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"/></a>
					(Leave empty for current date)
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_av_status');
				</script>
				{/literal}
			</td>
		</tr>
		
		<tr>
			<!-- AV Updates History -->
			<td class="head" width="20%" nowrap>
				<input type="checkbox" name="filter[selected_report][report_av_hist]"
					onClick="change_selected ('report_av_hist')" style="border:none;"
					{if $filter.selected_report.report_av_hist}checked{/if}
				>
				AV updates history:
			</td>
			<td>
				<div id="report_av_hist">
					<table width="100%">
						<tr>
							<td width="50%">
								Included computers:<br>
								<select name="filter[report_av_hist][computers][]" multiple size="6" style="width: 280px;"
								onDblClick="move_item('filter[report_av_hist][computers][]', 'filter[report_av_hist][computers_list][]')"
								>
									{foreach from=$selected_av_computers key=id item=name}
										<option value="{$id}">{$name}</option>
									{/foreach}
								</select>
								<br/>
								<a href="" 
								onClick="return move_all('filter[report_av_hist][computers][]', 'filter[report_av_hist][computers_list][]');"
								>Remove all</a>
							</td>
							<td width="50%">
								Available computers:<br>
								<select name="filter[report_av_hist][computers_list][]" multiple size="6" style="width: 280px;" 
								onDblClick="move_item('filter[report_av_hist][computers_list][]', 'filter[report_av_hist][computers][]')"
								>
									{foreach from=$available_av_computers key=id item=name}
										<option value="{$id}">{$name}</option>
									{/foreach}
								</select>
								<br/>
								<a href="" 
								onClick="return move_all('filter[report_av_hist][computers_list][]', 'filter[report_av_hist][computers][]');"
								>Include all</a>
							</td>
						</tr> 
					</table>
				</div>
				
				{literal}
				<script language="JavaScript">
					change_selected('report_av_hist');
				</script>
				{/literal}
			</td>
		</tr>
		
		<tr>
			<td colspan="2">
				<h2>Support</h2>
			</td>
		</tr>
	
	</table>
	<p>
	<input type="submit" name="generate" value="Generate report">

{/if}
</form>
