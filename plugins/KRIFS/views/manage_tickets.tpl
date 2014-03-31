{assign var="paging_titles" value="KRIFS"}
{include file="paging.html"}

{literal}

<script type="text/javascript" language="JavaScript">

function selectAll ()
{
	frm = document.forms['tickets_frm']
	if (frm.elements['filter[user_id][]'])
	{
		users_list = frm.elements['filter[user_id][]']
		customers_list = frm.elements['filter[customer_id][]']

		for (i=0; i<users_list.options.length; i++)
		{
			users_list.options[i].selected = true
		}
		for (i=0; i<customers_list.options.length; i++)
		{
			customers_list.options[i].selected = true
		}
	}
}

function addUser ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[user_id][]']
	users_list = frm.elements['available_users']

	if (users_list.selectedIndex >= 0)
	{
		opt = new Option (users_list.options[users_list.selectedIndex].text, users_list.options[users_list.selectedIndex].value, false, false)

		sel_list.options[sel_list.options.length] = opt
		users_list.options[users_list.selectedIndex] = null
	}
}

function removeUser ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[user_id][]']
	users_list = frm.elements['available_users']

	if (sel_list.selectedIndex >= 0)
	{
		opt = new Option (sel_list.options[sel_list.selectedIndex].text, sel_list.options[sel_list.selectedIndex].value, false, false)

		users_list.options[users_list.options.length] = opt
		sel_list.options[sel_list.selectedIndex] = null
	}
}

function addCustomer ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[customer_id][]']
	users_list = frm.elements['available_customers']

	if (users_list.selectedIndex >= 0)
	{
		if (users_list.options[users_list.selectedIndex].value != " ")
		{
			opt = new Option (users_list.options[users_list.selectedIndex].text, users_list.options[users_list.selectedIndex].value, false, false)

			sel_list.options[sel_list.options.length] = opt
			users_list.options[users_list.selectedIndex] = null
		}
	}
}

function removeCustomer ()
{
	frm = document.forms['tickets_frm']
	sel_list = frm.elements['filter[customer_id][]']
	users_list = frm.elements['available_customers']

	if (sel_list.selectedIndex >= 0)
	{
		opt = new Option (sel_list.options[sel_list.selectedIndex].text, sel_list.options[sel_list.selectedIndex].value, false, false)

		users_list.options[users_list.options.length] = opt
		sel_list.options[sel_list.selectedIndex] = null
	}
}

function checkRunSearch()
{
	var frm = document.forms['tickets_frm']
	var srcs_list = frm.elements['lst_search_id']
	var ret = true;

	var src_id = srcs_list.options[srcs_list.selectedIndex].value;

	if (src_id == '')
	{
		ret = false;
		alert ('Please select a saved search from the list');
	}

	var elm_src_id = frm.elements['search_id'];
	if (elm_src_id) elm_src_id.value = src_id;

	return ret;
}

// Called when a new type is selected, will make sure that the "main types only" checkbox is unchecked
function checkTypeChanged()
{
	frm = document.forms['tickets_frm'];
	lst_types = frm.elements['filter[type]'];
	ck_main = frm.elements['filter[types_main_only]'];

	if (lst_types.options[lst_types.selectedIndex].value != '') ck_main.checked = false;
}

function man_sel_all()
{
    var gen_mansel = document.tickets_frm.chkselAllManager;
    var mansel = document.tickets_frm.elements['man_sel[]'];
    
    for(i=0; i<mansel.length;i++)
    {
        mansel[i].checked = gen_mansel.checked;
    }    

}


</script>

{/literal}


<h1>Tickets {if $advanced}: Advanced Search{/if} {if $filter.escalated_only} : Escalated Only{/if}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="tickets_frm" onSubmit="selectAll();">
{$form_redir}

<div class="no_print">
{if !$advanced}
	<!-- Show simple filtering -->
	<table width="98%" class="list">
		<tr class="head">
			<td>View tickets</td>
			<td>Customer</td>
			<td>Account Manager</td>
			<td>Status</td>
			<td>Type</td>
			<td>Columns</td>
			<td>Per page</td>
			<td align="right">
				<a href="{$sort_url}&advanced=1">Advanced &#0187;</a>
			</td>
		</tr>
		<tr>
			<td>
				<select name="filter[view]"  style="width: 100px;">
					<option value="1" {if $filter.view==1}selected{/if}>[Any involvment]</option>
					<option value="2" {if $filter.view==2}selected{/if}>Assigned to</option>
					<option value="3" {if $filter.view==3}selected{/if}>Owned by</option>
					<option value="4" {if $filter.view==4}selected{/if}>Created by</option>
				</select>
				<br>
				<select name="filter[user_id]" style="width: 100px;">
					<option value="">[All]</option>
					<option value="{$self_uid}" {if $self_uid==$filter.user_id}selected{/if}>[Myself]</option>
					{html_options options=$users_list selected=$filter.user_id}
				</select>
			</td>

			<td>
				<select name="filter[customer_id]" style="width: 120px;">
					<option value="" selected>[All customers]</option>
					{html_options options=$customers_list selected=$filter.customer_id}
				</select>
			</td>

			<td>
				<select name="filter[account_manager]" style="width: 100px;" onchange="tickets_frm.submit()">
					<option value="">[All]</option>
					{html_options options=$ACCOUNT_MANAGERS selected=$filter.account_manager}
				</select>
			</td>

			<td nowrap="nowrap">
				<select name="filter[status]" style="width: 100px;">
					<option value="-2">[All]</option>
					<option value="-1" {if $filter.status==-1}selected{/if}>[Not closed]</option>
					{html_options options=$TICKET_STATUSES selected=$filter.status}
				</select>
				<br/>
				<input type="checkbox" class="checkbox" name="filter[escalated_only]" {if $filter.escalated_only}checked{/if}> Escalated only<br/>
				<input type="checkbox" class="checkbox" name="filter[unscheduled_only]" {if $filter.unscheduled_only}checked{/if}> Unscheduled only<br/>
				<input type="checkbox" class="checkbox" name="filter[not_linked_ir]" {if $filter.not_linked_ir}checked{/if}> Not linked to IR<br/>
				<input type="checkbox" class="checkbox" name="filter[not_seen_manager]" value="1"
					{if $filter.not_seen_manager}checked{/if}
				/> Not seen by manager<br/>

				<input type="checkbox" class="checkbox" name="filter[not_seen_manager_or_not_ir]" value="1"
					{if $filter.not_seen_manager_or_not_ir}checked{/if}
					{literal}
					onclick="if (this.checked) {
						document.forms['tickets_frm'].elements['filter[not_linked_ir]'].checked=false;
						document.forms['tickets_frm'].elements['filter[not_seen_manager]'].checked=false;
					};"
					{/literal}
				/> Not seen by manager<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OR not linked to IR
			</td>

			<td>
				<select name="filter[type]" style="width: 100px;" onchange="checkTypeChanged();">
					<option value="">[All]</option>
					{html_options options=$TICKET_TYPES selected=$filter.type}
				</select>
				<br/>
				<input type="checkbox" name="filter[types_main_only]" value="1" {if $filter.types_main_only}checked{/if}/> Main types only<br/>
			</td>

			<td nowrap>
				<input type="checkbox" name="filter[show_owner]" value="1" {if $filter.show_owner}checked{/if}/> Owner<br/>
				<input type="checkbox" name="filter[show_created]" value="1" {if $filter.show_created}checked{/if}/> Created<br/>
				<input type="checkbox" name="filter[show_escalated]" value="1" {if $filter.show_escalated}checked{/if}/> Escalated<br/>
				<input type="checkbox" name="filter[show_scheduled]" value="1" {if $filter.show_scheduled}checked{/if}/> Scheduled
			</td>

			<td>
				<select name="filter[limit]">
					{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
				</select>
			</td>

			<td align="right" style="vertical-align: middle">
				<input type="submit" name="do_filter" value="Apply filter">
			</td>
		</tr>
		<tr class="head">
			<td>
				Saved searches:
			</td>
			<td colspan="7">
				<select name="lst_search_id">
					{html_options options=$favorites_searches selected=$search_id}
					<option value="">-----------------</option>
					{html_options options=$other_searches selected=$search_id}
				</select>
				<input type="submit" value="Run search &#0187;" onClick="return checkRunSearch();" name="load_search">
			</td>
		</tr>

	</table>

{elseif $advanced and !$do_search}

	<!-- Show the advanced search options -->

	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="2">Advanced Search </td>
			<td  width="10%" nowrap align="right">
				<a href="?cl=krifs/manage_tickets">Simple filtering &#0187;</a>
			</td>
		</tr>
		</thead>

		<tr>
			<td rowspan="4" width="10%"><b>Search</b></td>
			<td colspan="2">
				<input type="text" name="filter[keywords]" size="80" value="{$filter.keywords}">
			</td>
		</tr>
		<tr>
			<td width="15%">By:</td>
			<td>
				<input type="radio" class="radio" name="filter[keywords_phrase]" value="0" {if !$filter.keywords_phrase}checked{/if}>Keywords &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" class="radio" name="filter[keywords_phrase]" value="1" {if $filter.keywords_phrase}checked{/if}>Phrase
			</td>
		</tr>
		<tr>
			<td width="15%">Use (for keywords):</td>
			<td>
				<input type="radio" class="radio" name="filter[keywords_and]" value="0" {if !$filter.keywords_and}checked{/if}>OR &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" class="radio" name="filter[keywords_and]" value="1" {if $filter.keywords_and}checked{/if}>AND

			</td>
		</tr>
		<tr>
			<td width="15%">Search in:</td>
			<td>
				<input type="checkbox" class="checkbox" name="filter[in_subject]" {if $filter.in_subject}checked{/if}>
				Subject &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="checkbox" class="checkbox" name="filter[in_comments]" {if $filter.in_comments}checked{/if}>
				Comments
			</td>
		</tr>

		<tr>
			<td>
				<b>Users</b><br>
				<select name="filter[view]">
					<option value="1" {if $filter.view==1}selected{/if}>[Any involvment]</option>
					<option value="2" {if $filter.view==2}selected{/if}>Assigned to</option>
					<option value="3" {if $filter.view==3}selected{/if}>Owned by</option>
					<option value="4" {if $filter.view==4}selected{/if}>Created by</option>
				</select>
			</td>
			<td colspan="2">
				<table class="no_borders">
					<tr>
						<td>
							Selected users:<br>
							<select name="filter[user_id][]" size="5" style="width: 180px;" multiple onDblClick="removeUser();">
								{foreach from=$filter.user_id item=user_id}
									<option value="{$user_id}">{$users_list.$user_id}</option>
								{/foreach}
							</select>
						</td>
						<td>
							Users:<br>
							<select size="5" style="width: 180px;" name="available_users" onDblClick="addUser();">
								{foreach from=$users_list key=user_id item=user_name}
									{if !in_array($user_id, $filter.user_id)}
										<option value="{$user_id}">{$user_name}</option>
									{/if}
								{/foreach}
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr>
			<td><b>Customers</b></td>
			<td colspan="2">
				<table class="no_borders">
					<tr>
						<td>
							Selected customers:<br>
							<select name="filter[customer_id][]" size="5" style="width: 180px;" multiple onDblClick="removeCustomer();">
								{foreach from=$filter.customer_id item=customer_id}
									<option value="{$customer_id}">{$customers_list.$customer_id}</option>
								{/foreach}
							</select>
						</td>
						<td>
							Customers:<br>
							<select size="5" style="width: 180px;" name="available_customers" onDblClick="addCustomer();">
								{foreach from=$customers_list key=customer_id item=customer_name}
									{if !in_array($customer_id, $filter.customer_id)}
										<option value="{$customer_id}">{$customer_name}</option>
									{/if}
								{/foreach}
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr>
			<td><b>Type</b></td>
			<td colspan="2">
				{assign var="cols_no" value="4"}
				{assign var="col" value="1"}
				<table class="no_borders" width="90%">
					{foreach from=$TICKET_TYPES key=type_id item=type_name}
						{if $col++==1}<tr>{/if}
						<td width="25%">
							<input type="checkbox" class="checkbox" name="filter[type][]" value="{$type_id}"
								{if in_array($type_id, $filter.type)}checked{/if}
							>
							{$type_name}
						</td>
						{if $col>$cols_no}
							</tr>
							{assign var="col" value="1"}
						{/if}
					{/foreach}
				</table>
			</td>
		</tr>

		<tr>
			<td><b>Status</b></td>
			<td colspan="2">
				{assign var="cols_no" value="4"}
				{assign var="col" value="1"}
				<table class="no_borders" width="90%">
					{foreach from=$TICKET_STATUSES key=status_id item=status_name}
						{if $col++==1}<tr>{/if}
						<td width="25%">
							<input type="checkbox" class="checkbox" name="filter[status][]" value="{$status_id}"
								{if in_array($status_id, $filter.status)}checked{/if}
							>
							{$status_name}
						</td>
						{if $col>$cols_no}
							</tr>
							{assign var="col" value="1"}
						{/if}
					{/foreach}
				</table>
				<br/>


				<input type="checkbox" class="checkbox" name="filter[escalated_only]" value="1" {if $filter.escalated_only}checked{/if} />
				<b>Escalated only</b><br/>
				<input type="checkbox" class="checkbox" name="filter[unscheduled_only]" value="1" {if $filter.unscheduled_only}checked{/if} />
				<b>Unschedule only</b><br/>
				<input type="checkbox" class="checkbox" name="filter[not_linked_ir]" {if $filter.not_linked_ir}checked{/if} />
				<b>Not linked to IR</b><br/>
				<input type="checkbox" class="checkbox" name="filter[not_seen_manager]" {if $filter.not_seen_manager}checked{/if}>
				<b>Not seen by manager</b><br/>

				<input type="checkbox" class="checkbox" name="filter[not_seen_manager_or_not_ir]" value="1"
					{if $filter.not_seen_manager_or_not_ir}checked{/if}
					{literal}
					onclick="if (this.checked) {
						document.forms['tickets_frm'].elements['filter[not_linked_ir]'].checked=false;
						document.forms['tickets_frm'].elements['filter[not_seen_manager]'].checked=false;
					};"
					{/literal}
				/> <b>Not seen by manager OR not linked to IR</b>
			</td>
		</tr>
		<tr>
			<td><b>Not updated in:</b></td>
			<td colspan="2">
				<input type="text" name="filter[days_no_update]" value="{$filter.days_no_update|escape}" size="6" /> days
			</td>
		</tr>
		<tr>
			<td><b>Results display</b></td>
			<td colspan="2">
				Results per page:
				<select name="filter[limit]">
					{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
				</select>

				&nbsp;&nbsp;&nbsp;&nbsp;
				Columns:
				<input type="checkbox" name="filter[show_owner]" value=1 {if $filter.show_owner}checked{/if}> Owner
				<input type="checkbox" name="filter[show_created]" value=1 {if $filter.show_created}checked{/if}> Created
				<input type="checkbox" name="filter[show_escalated]" value=1 {if $filter.show_escalated}checked{/if}> Escalated
				<input type="checkbox" name="filter[show_scheduled]" value=1 {if $filter.show_scheduled}checked{/if}> Escalated
			</td>
		</tr>
		<tr>
			<td><b>Sort by</b></td>
			<td colspan="2">
				<select name="filter[order_by]">
					{html_options options=$order_by_options selected=$filter.order_by}
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<select name="filter[order_dir]">
					<option value="ASC">Ascending</option>
					<option value="DESC" {if $filter.order_dir=='DESC'}selected{/if}>Descending</option>
				</select>
			</td>
		</tr>

	</table>
	<p>
	<input type="submit" name="do_search_button" value="Search">
	<input type="submit" name="cancel" value="Cancel">
{else}
	<!-- Displaying results of an advanced search -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="20%">Keywords</td>
			<td width="20%">User</td>
			<td width="20%">Customer</td>
			<td width="15%">Type</td>
			<td width="15%">Status</td>
			<td  width="10%" nowrap align="right">
				<a href="?cl=krifs/manage_tickets">Simple filtering &#0187;</a>
			</td>
		</tr>
		</thead>

		<tr>
			<td>
				{if $filter.keywords}
					<b>
					{if $filter.keywords_phrase}By phrase,
					{else}
						By word ({if $filter.keywords_and}AND{else}OR{/if})
					{/if}
					<br>
					In:
					{if $filter.in_subject}subject, {/if}
					{if $filter.in_comments}comments{/if}
					</b><br>
					{$filter.keywords}
				{else}
					[None]
				{/if}
			</td>
			<td>
				{if $filter.user_id}
					<b>
					{if $filter.view==2}Assigned to
					{elseif $filter.view==3}Owned by
					{elseif $filter.view==4}Created by
					{else}[Any involvment]
					{/if}
					:</b><br>

					{foreach from=$filter.user_id item=user_id name="list"}
						{$users_list.$user_id}{if !$smarty.foreach.list.last},{/if}
					{/foreach}
				{else}
					[All]
				{/if}
			</td>
			<td>
				{if $filter.customer_id}
					{foreach from=$filter.customer_id item=customer_id name="list"}
						{$customers_list.$customer_id}{if !$smarty.foreach.list.last},{/if}
					{/foreach}
				{else}
					[All]
				{/if}
			</td>
			<td>
				{if $filter.type}
					{foreach from=$filter.type item=type_id name="list"}
						{$TICKET_TYPES.$type_id}{if !$smarty.foreach.list.last},{/if}
					{/foreach}
				{else}
					[All]
				{/if}
			</td>
			<td>
				{if $filter.status}
					{foreach from=$filter.status item=status_id name="list"}
						{$TICKET_STATUSES.$status_id}{if !$smarty.foreach.list.last},{/if}
					{/foreach}
				{else}
					[All]
				{/if}

				{if $filter.escalated_only}<br/><b>Escalated only</b>{/if}
				{if $filter.unschedule_only}<br/><b>Unscheduled only</b>{/if}
				{if $filter.not_linked_ir}<br/><b>Not linked to IR</b>{/if}
				{if $filter.not_seen_manager}<br/><b>Not seen by manager</b>{/if}
				{if $filter.not_seen_manager_or_not_ir}<br/><b>Not seen by manager OR not linked to IR</b>{/if}
				{if $filter.days_no_update}<br/><b>Not updated in {$filter.days_no_update} days</b>{/if}
			</td>

			<td nowrap align="right">
				<a href="?cl=krifs/manage_tickets&advanced=1{if $search_id}&search_id={$search_id}{/if}">Edit search &#0187;</a><br>
				<a href="?cl=krifs/saved_search_add{if $search_id}&search_id={$search_id}{/if}">Save search &#0187;</a>
			</td>
		</tr>
		<tr class="head">
			<td>
				Saved searches:
			</td>
			<td colspan="6">
				<select name="lst_search_id">
					{html_options options=$favorites_searches selected=$search_id}
					<option value="">-----------------</option>
					{html_options options=$other_searches selected=$search_id}
				</select>
				<input type="submit" value="Run search &#0187;" onClick="return checkRunSearch();" name="load_search">
			</td>
		</tr>
	</table>
	<input type="hidden" name="filter[limit]" value="{$filter.limit}">

{/if}

<p>


{if !$advanced or ($advanced and $do_search)}
	<table width="98%">
		<tr>
			<td colspan="2" width="50%">

                {* <a href="?cl=krifs/ticket_add{if $filter.customer_id>0}&customer_id={$filter.customer_id}{/if}">Create new ticket &#0187;</a> *}
				{assign var="cid" value=0}
				{assign var="is_arr" value=$filter.customer_id|is_array}
				{if $is_arr}
				    {assign var="cnt" value=$filter.customer_id|@count}
				    {if $cnt==1}
					{assign var="cid" value=$filter.customer_id.0}
				    {/if}
				{/if}
                {assign var="p" value="customer_id:"|cat:$cid}
				<a href="{'krifs'|get_link:'ticket_add':$p:'template'}">Create new ticket &#0187;</a>
			</td>
			<td align="right">
				{if $tot_tickets > $filter.limit}
					{if $filter.start > 0}
						<a href="{'krifs'|get_link:'manage_tickets_submit'}"
							onClick="document.forms['tickets_frm'].elements['go'].value='prev'; document.forms['tickets_frm'].submit(); return false;"
						>&#0171; Previous</a>
					{else}
						<font class="light_text">&#0171; Previous</font>
					{/if}
					<select name="filter[start]" onChange="document.forms['tickets_frm'].submit()">
						{html_options options=$pages selected=$filter.start}
					</select>
					{if $filter.start + $filter.limit < $tot_tickets}
						<a href="{'krifs'|get_link:'manage_tickets_submit'}"
							onClick="document.forms['tickets_frm'].elements['go'].value='next'; document.forms['tickets_frm'].submit(); return false;"
						>Next &#0187;</a>
					{else}
						<font class="light_text">Next &#0187;</font>
					{/if}
				{/if}
			</td>
		</tr>
	</table>
	<input type="hidden" name="go" value="">
	</div>

	<!--<div style="display:block; border: 1px solid red; width:100%; height: 300px; overflow:scroll; scrollbars:yes;">-->
	<table class="list" width="98%">
		<thead>
		<tr>

			<td class="sort_text" style="width: 16px; text-align: left;">
                {if $filter.order_by=='priority' and $filter.order_dir=='ASC'}{assign var="priority_sort" value="DESC"}{else}{assign var="priority_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"priority"|cat:",order_dir:"|cat:$priority_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				><img src="/images/logo_icon_16.gif" style="width:16px; height:16px; vertical-align: middle;" alt="Priority" title="Priority"
				>{if $filter.order_by=='priority'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}</a>
			</td>
			<td width="1"></td>

			<td class="sort_text" nowrap width="30" style="width: 30px; white-space: no-wrap;">
                {if $filter.order_by=='id' and $filter.order_dir=='ASC'}{assign var="id_sort" value="DESC"}{else}{assign var="id_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"id"|cat:",order_dir:"|cat:$id_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"
                >ID</a>&nbsp;{if $filter.order_by=='id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
			</td>

			<td nowrap class="sort_text" style="width: 30%">
                {if $filter.order_by=='subject' and $filter.order_dir=='ASC'}{assign var="subject_sort" value="DESC"}{else}{assign var="subject_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"subject"|cat:",order_dir:"|cat:$subject_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Subject
				{if $filter.order_by=='subject'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			<td nowrap class="sort_text" style="width: 30px">
                {if $filter.order_by=='type' and $filter.order_dir=='ASC'}{assign var="type_sort" value="DESC"}{else}{assign var="type_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"type"|cat:",order_dir:"|cat:$type_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Type
				{if $filter.order_by=='type'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			<td nowrap class="sort_text" style="width: 30px">
                {if $filter.order_by=='customer' and $filter.order_dir=='ASC'}{assign var="customer_sort" value="DESC"}{else}{assign var="customer_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"customer"|cat:",order_dir:"|cat:$customer_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Customer
				{if $filter.order_by=='customer'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			<td nowrap style="width: 30px">
				TBB time in IR's
			</td>
			<td nowrap style="width: 30px">
				Tot. TBB time / Diff
			</td>

			<td class="sort_text" style="width: 20px; text-align: center;">
                {if $filter.order_by=='private' and $filter.order_dir=='ASC'}{assign var="private_sort" value="DESC"}{else}{assign var="private_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"private"|cat:",order_dir:"|cat:$private_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Priv.
				{if $filter.order_by=='private'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			<td nowrap class="sort_text" style="width: 30px">
                {if $filter.order_by=='assigned_id' and $filter.order_dir=='ASC'}{assign var="assigned_id_sort" value="DESC"}{else}{assign var="assigned_id_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"assigned_id"|cat:",order_dir:"|cat:$assigned_id_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Assigned
				{if $filter.order_by=='assigned_id'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			{if $filter.show_owner}
				<td nowrap class="sort_text" style="width: 30px">
                    {if $filter.order_by=='owner' and $filter.order_dir=='ASC'}{assign var="owner_sort" value="DESC"}{else}{assign var="owner_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"owner"|cat:",order_dir:"|cat:$owner_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"

					>Owner
					{if $filter.order_by=='owner'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}</a>
				</td>
			{/if}

			<td nowrap class="sort_text" style="width: 30px">
                {if $filter.order_by=='status' and $filter.order_dir=='ASC'}{assign var="status_sort" value="DESC"}{else}{assign var="status_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"status"|cat:",order_dir:"|cat:$status_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Status
				{if $filter.order_by=='status'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			{if $filter.show_created}
				<td nowrap class="sort_text" style="width: 30px">
                    {if $filter.order_by=='created' and $filter.order_dir=='ASC'}{assign var="created_sort" value="DESC"}{else}{assign var="created_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"created"|cat:",order_dir:"|cat:$created_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"

					>Created
					{if $filter.order_by=='created'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}</a>
				</td>
			{/if}

			<td nowrap class="sort_text" style="width: 30px">
                {if $filter.order_by=='last_modified' and $filter.order_dir=='ASC'}{assign var="last_modified_sort" value="DESC"}{else}{assign var="last_modified_sort" value="ASC"}{/if}
                {assign var="p" value="order_by:"|cat:"last_modified"|cat:",order_dir:"|cat:$last_modified_sort}
                <a href="{$sort_url|add_extra_get_params:$p:'template'}"

				>Updated
				{if $filter.order_by=='last_modified'}
				<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
				{/if}</a>
			</td>

			{if $filter.show_escalated}
				<td nowrap class="sort_text" style="width: 30px">
                    {if $filter.order_by=='escalated' and $filter.order_dir=='ASC'}{assign var="escalated_sort" value="DESC"}{else}{assign var="escalated_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"escalated"|cat:",order_dir:"|cat:$escalated_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"

					>Escalated
					{if $filter.order_by=='escalated'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}</a>
				</td>
			{/if}

			{if $filter.show_scheduled}
				<td nowrap class="sort_text" style="width: 30px">
                    {if $filter.order_by=='scheduled' and $filter.order_dir=='ASC'}{assign var="scheduled_sort" value="DESC"}{else}{assign var="scheduled_sort" value="ASC"}{/if}
                    {assign var="p" value="order_by:"|cat:"scheduled"|cat:",order_dir:"|cat:$scheduled_sort}
                    <a href="{$sort_url|add_extra_get_params:$p:'template'}"

					>Scheduled
					{if $filter.order_by=='scheduled'}
					<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">
					{/if}</a>
				</td>
			{/if}
                        <td style="width: 20px; white-space: nowrap">
                            <input type="checkbox" id="chkselAllManager" name="chkselAllManager" onchange="man_sel_all();">&nbsp;|&nbsp;
                            <img alt="Mark seen by manager" src="/images/chk_icon.jpg" with="16" height="16" onclick="document.tickets_frm.mark_seen.value = 1; return document.tickets_frm.submit();" />
                            <input type="hidden" name="mark_seen" id="mark_seen" value="0" />
                        </td>

		</tr>
		</thead>

		{foreach from=$tickets item=ticket}
			<tr>
				<td style="text-align: left; width: 16px;" class="no_print">
					{assign var="priority_color" value=$ticket->priority}
					{if $priority_color}
						<img src="/images/logo_icon_16.gif" style="background: {$TICKETS_PRIORITIES_COLORS.$priority_color}" width="16" height="16"
						alt="Priority: {$TICKET_PRIORITIES.$priority_color}" title="Priority: {$TICKET_PRIORITIES.$priority_color}"
						>
					{/if}
				</td>

				<td width="5" style="padding:0px; ">{if $ticket->escalated}<font class="error" style="font-size:12pt">!</font>{/if}</td>

				<td class="print_only">{$TICKET_PRIORITIES.$priority_color}</td>

				</td>
                {assign var="p" value="id:"|cat:$ticket->id}
				<td><a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->id}</a></td>
				<td><a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">{$ticket->subject}</a></td>
				<td style="width: 30px">
					{assign var="ticket_type" value=$ticket->type}
					{$TICKET_TYPES.$ticket_type}
				</td>
				<td>
					{assign var="customer_id" value=$ticket->customer_id}
					{$all_customers_list.$customer_id}
				</td>
				<td>
					{assign var="tid" value=$ticket->id}
					{assign var="tbb_time" value=$tickets_tbb.$tid}
					{$tbb_time.ir}
				</td>
				<td>
					{$tbb_time.tot} / <font color="{$tbb_time.color}">({$tbb_time.dif})</font>
				</td>
				<td style="width: 20px; text-align: center;">
					{if $ticket->private} Y {else} N {/if}
				</td>
				<td>
					{if $ticket->assigned_id}
						{$ticket->assigned->get_short_name()}

						{if $ticket->assigned->customer_id}
							{assign var="user_customer_id" value=$ticket->assigned->customer_id}
							({$customers_list.$user_customer_id})
						{/if}
					{/if}
				</td>
				{if $filter.show_owner}
					<td>
						{if $ticket->owner_id}
							{$ticket->owner->get_short_name()}

							{if $ticket->owner->customer_id}
								{assign var="owner_customer_id" value=$ticket->owner->customer_id}
								({$customers_list.$owner_customer_id})
							{/if}
						{/if}
					</td>
				{/if}
				<td style="width: 30px">
					{assign var="ticket_status"  value=$ticket->status}
					{$TICKET_STATUSES.$ticket_status}
				</td>

				{if $filter.show_created}
					<td>{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
				{/if}

				<td>{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>

				{if $filter.show_escalated}
					<td>
						{if $ticket->escalated}
							{$ticket->escalated|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
						{else}
							-
						{/if}
					</td>
				{/if}

				{if $filter.show_scheduled}
					<td>
						{if $ticket->scheduled_date}
							{$ticket->scheduled_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
						{else}
							-
						{/if}
					</td>
				{/if}
                                <td>
                                    {if $ticket->seen_manager_id}
                                        <img src="/images/chk_icon.jpg" with="16" height="16" />
                                    {else}
                                    <input type="checkbox" id="man_sel[]" value="{$ticket->id}" name="man_sel[]" />
                                    {/if}
                                </td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="10">[No tickets found]</td>
			</tr>
		{/foreach}

	</table>
{/if}

<p>
</form>
