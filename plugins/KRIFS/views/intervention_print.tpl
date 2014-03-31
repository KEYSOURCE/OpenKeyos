{assign var="paging_titles" value="KRIFS, Print Intervention Report"}
{assign var="paging_urls" value="/krifs"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function check_email ()
{
	frm = document.forms['filter_frm'];
	elm_row_subject = document.getElementById ('row_subject');
	elm_row_recipient = document.getElementById ('row_recipient');
	elm_row_body = document.getElementById ('row_body');
	elm_button_email = document.getElementById ('button_email');
	
	if (frm.elements['filter[show_email]'].checked)
	{
		elm_row_subject.style.display = '';
		elm_row_recipient.style.display = '';
		elm_row_body.style.display = '';
		elm_button_email.style.display = '';
	}
	else
	{
		elm_row_subject.style.display = 'none';
		elm_row_recipient.style.display = 'none';
		elm_row_body.style.display = 'none';
		elm_button_email.style.display = 'none';
	}
}

{/literal}
//]]>
</script>

<div class="no_print">
{include file="paging.html"}

<div class="border_box" style="width: 96%; padding-left: 10px; padding-bottom: 10px;">
<h1 style="width:98%; margin-top: 10px;">Print Intervention Report</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="4">Options</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%">Show:</td>
		<td width="85%">
			<select name="filter[show]" onchange="document.forms['filter_frm'].submit();">
				<option value="detailed">Detailed</option>
				<option value="summary" {if $filter.show=="summary"}selected{/if}>Summary</option>
			</select>
			<select name="filter[view]" onchange="document.forms['filter_frm'].submit();">
				<option value="customer">Customer view</option>
				<option value="keysource" {if $filter.view=="keysource"}selected{/if}>Keysource view</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>E-mail:</td>
		<td>
			<input type="checkbox" class="checkbox" name="filter[show_email]" 
				{if $filter.show_email}checked{/if} 
				onclick="check_email();"
			/>
			</td>
	</tr>
	<tr id="row_recipient" style="display:none;">
		<td>E-mail recipients:</td>
		<td>
			<input type="text" name="filter[email_recipients]" value="{$filter.email_recipients|escape}" size="60"/>
		</td>
	</tr>
	<tr id="row_subject" style="display:none;">
		<td>E-mail subject:</td>
		<td>
			<input type="text" name="filter[email_subject]" value="{$filter.email_subject|escape}" size="60"/>
		</td>
	</tr>
	<tr id="row_body" style="display:none;">
		<td>E-mail text:</td>
		<td>
			<textarea name="filter[email_body]" rows="5" cols="60">{$filter.email_body|escape}</textarea>
		</td>
	</tr>
	
</table>
<p/>

<input type="submit" name="save" value="Refresh" />
<input type="submit" name="cancel" value="Close" />&nbsp;&nbsp;&nbsp;
<input type="submit" name="do_pdf" value="Generate PDF" />
<input type="submit" name="do_email" value="Send e-mail" id="button_email" style="display: none;"/>
</form>
</div>
</div>

<table width="98%" class="list">
	<tr>
		<td width="40%">
			<img src="/images/logo.gif" width="240px" height="55px" alt="" title=""/>
		</td>
		<td width="60%" align="right">
			<h2 style="border:0px">Intervention Report<br/>
			<i>
			{if $filter.show=="detailed"}Detailed Report
			{else}Summary Report
			{/if}
			{if $filter.view!='customer'}- Keysource{/if}
			</i>
			</h2>
		</td>
	</tr>
</table>
<table width="98%">
	<tr>
		<td width="60%">
			<h2>Reference: #{$intervention->id}<br/>
			<i>{$intervention->subject|escape}</i>
			</h2>
		</td>
		<td width="40%" align="right">
			<div class="border_box_hard" style="width:80%; height: 70px; text-align: left; margin-top: 25px; margin-right: 0px;">
				<b>Customer: {$customer->name} (#{$customer->id})</b>
			</div>
		</td>
	</tr>
</table>
<br/><br/><br/>

{if $filter.show == 'summary'}

	<!-- Show the summary version of the intervention report -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10%">Ticket #</td>
			
			{if $filter.view=='customer'}
				<td width="60%">Subject</td>
				<td width="15%">First intervention</td>
				<td width="15%">Last intervention</td>
			{else}
				<td width="50%">Subject</td>
				<td width="15%">First intervention</td>
				<td width="15%">Last intervention</td>
				<td width="10%" align="right">Work time</td>
			{/if}
		</tr>
		</thead>
		
		{foreach from=$intervention->tickets item=ticket key=ticket_id}
		<tr>
			<td>{$ticket->id}</td>
			<td>{$ticket->subject|escape}</td>
			
			<td>
				{if $ticket->time_in}
					{$ticket->time_in|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{else}--
				{/if}
			</td>
			<td>
				{if $ticket->time_out}
					{$ticket->time_out|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{else}--
				{/if}
			</td>
			
			{if $filter.view!='customer'}
				<td align="right">
					{if $ticket->work_time}
						{$ticket->work_time|@format_interval_minutes}
					{else}
						--
					{/if}
				</td>
			{/if}
			
		</tr>
		{/foreach}
		
		{if $filter.view!='customer'}
		<tr class="head">
			<td>TOTAL:</td>
			<td colspan="3"> </td>
			
			<td align="right">
				{if $intervention->work_time}
					{$intervention->work_time|@format_interval_minutes}
				{else}
					--
				{/if}
			</td>
		</tr>
		{/if}
	</table>
	
{else}

	<!-- Show the detailed version of the intervention report -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="20%">Intervenant</td>
			<td width="10%">Location</td>
			{if $filter.view=='customer'}
				<td width="45%">Action type</td>
				<td width="10%">Time in</td>
				<td width="10%">Time out</td>
				<td width="5%">Pricing</td>
			{else}
				<td width="35%">Action type</td>
				<td width="15%">Time in</td>
				<td width="10%" nowrap="nowrap">Billable/Pricing</td>
				<td width="10%" align="right" nowrap="nowrap">Work time</td>
			{/if}
		</tr>
		</thead>
		
		{foreach from=$intervention->details item=detail}
		{assign var="price_type" value=$detail->action_type->price_type}
		<tr class="no_bottom_border">
			<td>
				{if $detail->user_id}
					{$detail->user->get_name()}
				{/if}
			</td>
			<td>
				{assign var="location_id" value=$detail->location_id}
				{$locations_list.$location_id}
			</td>
			<td>
				[{$detail->action_type->erp_id}]
				{assign var="action_type_id" value=$detail->activity_id}
				{$action_types.$action_type_id}
			</td>
			<td nowrap="nowrap">
				{if $detail->time_in}
					{$detail->time_in|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
				{else}--
				{/if}
			</td>
			{if $filter.view=='customer'}
				<td nowrap="nowrap">
					{if $detail->time_out}
						{$detail->time_out|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
					{else}--
					{/if}
				</td>
				<td nowrap="nowrap">
					{if $price_type==$smarty.const.PRICE_TYPE_HOURLY}Hourly
					{else}Fixed
					{/if}
				</td>
			{else}
				<td nowrap="nowrap">
					{if $detail->billable and ($price_type==$smarty.const.PRICE_TYPE_HOURLY or ($price_type==$smarty.const.PRICE_TYPE_FIXED and !$detail->is_continuation))}Yes,
					{else}No,
					{/if}
					{if $price_type==$smarty.const.PRICE_TYPE_HOURLY}Hourly
					{else}Fixed
					{/if}
				</td>
				<td align="right">
					{if $detail->work_time}
						{$detail->work_time|@format_interval_minutes}
					{else}
						--
					{/if}
				</td>
			{/if}
		</tr>
		<tr>
			<td colspan="6" style="padding-bottom: 4px; padding-left: 30px;">
				<i>
				{assign var="ticket_id" value=$detail->ticket_id}
				{assign var="ticket" value=$intervention->tickets.$ticket_id}
				Ticket #{$ticket->id}: {$ticket->subject|escape}
				</i>
				<br/>
				{if $detail->private}
					{if $filter.view=='customer'}Technical support
					{else}<b>[Private]</b> {*{$detail->comments|escape|nl2br}*}{$detail->comments|nl2br}
					{/if}
				{else}
					{*{$detail->comments|escape|nl2br}*}
					{$detail->comments|nl2br}
				{/if}
			</td>
		</tr>
		{/foreach}
		
		{if $filter.view!='customer'}
		<tr class="head">
			<td colspan="5">TOTAL:</td>
			<td align="right">{$intervention->work_time|@format_interval_minutes}</td>
		</tr>
		{/if}
	</table>

{/if}
<br/><br/><br/><br/>

<table width="98%">
	<tr>
		<td width="60%">
			<div class="border_box_hard" style="width:90%; height: 90px;">
			<table width="100%">
				<tr>
					<td width="50%">
						Keysource scrl<br/>
						Av. de la Couronne 480<br/>
						1050 Brussels<br/>
						Belgium<br/>
						T +32-2-644.96.53<br/>
						F +32-3-649.18.11
					</td>
					<td>
						info@keysource.be<br/>
						www.keysource.be<br/>
						TVA: BE 435 019 363<br/>
						RCB: 508.360<br/>
						BBL: 310-0808309-94<br/>
						FORTIS: 210-0533549-04
					</td>
				</tr>
			</table>
			</div>
		</td>
		<td width="40%" align="right">
			<div class="border_box_hard" style="width:80%; height: 90px; text-align: left;">
				<b>Customer signature:</b>
			</div>
		</td>
	</tr>
</table>


<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_email ();
//]]>
</script>
