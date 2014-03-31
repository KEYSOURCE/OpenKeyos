{assign var="paging_titles" value="KRIFS, Intervention Print Console"}
{assign var="paging_urls" value="/krifs, /krifs/interventions_print_console"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}

//the paths to the generated pdfs for multiple printing;
function set_status (status)
{
	frm = document.forms['filter_frm'];
	elm = frm.elements['filter[status]'];
	for (i=0; i<elm.options.length; i++)
	{
		if (elm.options[i].value == status)
		{
			elm.options[i].selected = true;
		}
	}
	frm.submit ();
	return false;
}

function tpdf_preview(intervention_id, customer_id)
{
	var view = document.getElementById("filter[view]").value;
	var show = document.getElementById("filter[show]").value;
	pdf_preview(intervention_id, customer_id, view, show);
}
function pdfPreviewSelected()
{
	var irs = new Array();
	//get the id's of the IR's we have to print
	var sel_irs = document.filter_frm['prev_sel'];
	//alert('tot len '+sel_irs.length);
	j=0;
	for(i=0; i< sel_irs.length; i++)
	{
		if(sel_irs[i].checked)
		{
			//the value was found now split everything in two arrays
			irs[j] = new Array(2);
			irs[j] = sel_irs[i].value.split("&");
			j++;
		}
	}

	//get the IR generation settings
	var view = document.getElementById("filter[view]").value;
	var show = document.getElementById("filter[show]").value;

	pdf_multiple_preview(irs, view, show);

}

{/literal}

//]]>
</script>

<h1>Intervention Print Console</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="20%">Customer</td>
		<td width="15%">Account Manager</td>
		<td width="10%">Status</td>
		<td width="10%">User</td>
		<td width="10%">Per page</td>
		<td width="15%" align="right">Totals</td>
	</tr>
	</thead>
	<tr>
		<td>
			<select name="filter[customer_id]" style="width: 250px;" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		<td>
			<select name="filter[manager]" style="width: 150px;" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$ACCOUNT_MANAGERS selected=$filter.manager}
			</select>
		</td>
		<td>
			<select name="filter[status]" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$INTERVENTION_STATS selected=$filter.status}
		</td>
		<td>
			<select name="filter[user_id]" onchange="document.forms['filter_frm'].submit();">
				<option value="">[All]</option>
				{html_options options=$users_list selected=$filter.user_id}
			</select>
		</td>
		<td>
			<select name="filter[limit]" onchange="document.forms['filter_frm'].submit();">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
			</select>
		</td>
		<td align="right" nowrap="nowrap">
			{foreach from=$totals key=status item=total}
				<a href="#" onclick="return set_status({$status});">{$INTERVENTION_STATS.$status}</a>:&nbsp;{$total}<br/>
			{/foreach}
		</td>
	</tr>
</table>
<p/>
<div id='console_contents' style="width: 98%; float: center;">
<div id="interventions_display" style="float: left; width: 59%; display: inline; border: 0px solid black;">
<table width="98%">
	<tr>
		<td width="80%" align="left">
			{if $tot_interventions > $filter.limit}
				{if $filter.start > 0}
					<a href="" onClick="document.forms['filter_frm'].elements['go'].value='prev'; document.forms['filter_frm'].submit(); return false;">&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				<select name="filter[start]" onChange="document.forms['filter_frm'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tot_interventions}
					<a href="" onClick="document.forms['filter_frm'].elements['go'].value='next'; document.forms['filter_frm'].submit(); return false;">Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		</td>
		<td width="20%" align="right">
			<a href="#" name="anchor_preview_selected" id="anchor_preview_selected" onclick="pdfPreviewSelected()">Preview selected IRs</a>
		</td>
	</tr>
</table>
<input type="hidden" name="go" value="" />

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td {if $filter.customer_id > 0} width="49%" {else} width="29%" {/if}>Subject</td>
		{if !$filter.customer_id > 0}
			<td width="20%">Customer</td>
		{/if}
		<td width="10%">Status</td>
		<td width="10%">Created</td>
		<td></td>
		<td></td>
	</tr>
	</thead>


	{foreach from=$interventions item=intervention}
	<tr {if $intervention->tickets}class="no_bottom_border"{/if}>
        {assign var="p" value="id:"|cat:$intervention->id|cat:",do_filter:"|cat:"1"}
		<td><a href="{'krifs'|get_link:'intervention_edit':$p:'template'}"
		>{$intervention->id}</a>{if !$intervention->has_complete_info()}&nbsp;<font class="warning">!</font>{/if}</td>

		<td>
            {assign var="p" value="id:"|cat:$intervention->id|cat:",do_filter:"|cat:"1"}
			<a href="{'krifs'|get_link:'intervention_edit':$p:'template'}">{$intervention->subject}</a>
		</td>

		{if !$filter.customer_id > 0}
			<td>
				{assign var="customer_id" value=$intervention->customer_id}
                {assign var="p" value="id:"|cat:$customer_id}
				<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$customer_id}</a>
			</td>
		{/if}

		<td>
			{assign var="status" value=$intervention->status}
			{$INTERVENTION_STATS.$status}
		</td>
		<td nowrap="nowrap">{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
		<td align="center" nowrap="nowrap">
			<input type="checkbox" name="prev_sel" id="prev_sel" value="{$intervention->id}&{$intervention->customer_id}" />
		</td>
		<td align="right" nowrap="nowrap">
		{assign var="anchor_name" value=$intervention->id|string_format:"preview_anchor_%s"}
			<a id='{$anchor_name}' href="" target="pdf_view" onclick="tpdf_preview({$intervention->id}, {$intervention->customer_id})">Preview &#0187;</a>
		</td>
	</tr>

	{if $intervention->tickets}
	<tr>
		<td> </td>
		<td {if $filter.customer_id > 0} colspan="4" {else} colspan="5" {/if}>
			<ul style="margin-top: 0px; margin-bottom: 2px">
				{foreach from=$intervention->tickets item=ticket}
				<li>
                    {assign var="p" value="id:"|cat:$ticket->id}
				    <a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">#{$ticket->id}</a>:
				    {$ticket->subject|escape}
				</li>
				{/foreach}
			</ul>
		</td>
	</tr>
	{/if}

	{foreachelse}
	<tr>
		<td class="light_text" {if $filter.customer_id > 0} colspan="5" {else} colspan="6" {/if}>[No matching intervention reports]</td>
	</tr>
	{/foreach}
</table>


</div>
<div id='pdf_view_div' style="border: 0px solid black; float: right; width: 39%; display: inline;">
	<table width="98%">
		<tr>
			<td width="15%">Show:</td>
			<td width="85%">
				<select id="filter[show]" name="filter[show]" onchange="changeShow()">
					<option value="detailed">Detailed</option>
					<option value="summary" {if $filter.show=="summary"}selected{/if}>Summary</option>
				</select>
				<select id="filter[view]" name="filter[view]" onchange="changeView()">
					<option value="customer">Customer view</option>
					<option value="keysource" {if $filter.view=="keysource"}selected{/if}>Keysource view</option>
				</select>
			</td>
		</tr>
	</table>
	<div id="init" style="display: inline; width: 100%; display: block; float: center; align: center;">
		<h3>Press preview to generate a printable pdf report...</h3>
	</div>
	<div id="progress" style="display: inline; width: 100%; display: none; float: center;">
		<img src="/images/ajax-loader.gif">&nbsp;<h3>Generating report...</h3>
	</div>
	<iframe src="" name="pdf_view" id="pdf_view" style="width: 100%; height: 600px; display: none;">
	</iframe>
</div>
</div>
</form>
