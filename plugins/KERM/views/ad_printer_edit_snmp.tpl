{assign var="computer_id" value=$ad_printer->computer_id}
{assign var="nrc" value=$ad_printer->nrc}
{assign var="paging_titles" value="KERM, AD Printers, View AD Printer, SNMP Settings"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_printers, /?cl=kerm&op=ad_printer_view&computer_id=$computer_id&nrc=$nrc"}
{include file="paging.html"}

<h1>Edit SNMP Settings: {$ad_printer->name} ({$ad_printer->asset_no})</h1>

<p class="error">{$error_msg}</p>

<p>
Specify below the monitoring profile to be used for this AD Printer
and the computer which should do the data gathering.
</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="600">
	<thead>
	<tr>
			<td width="140">Customer:</td>
			<td class="post_highlight">
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->id}">#{$customer->id}: {$customer->name|escape}</a></td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Enable SNMP monitoring:</td>
		<td class="post_highlight">
			<select name="ad_printer[snmp_enabled]">
				<option value="0">No</option>
				<option value="1" {if $ad_printer->snmp_enabled}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Monitor profile:</td>
		<td class="post_highlight">
				<select name="ad_printer[profile_id]">
					<option value="">[Select profile]</option>
					{html_options options=$profiles_list selected=$ad_printer->profile_id}
				</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Computer:</td>
		<td class="post_highlight">
			<select name="ad_printer[snmp_computer_id]">
				<option value="">[Select computer]</option>
				{html_options options=$computers_list selected=$ad_printer->snmp_computer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">IP address:</td>
		<td class="post_highlight">
			<input type="text" name="ad_printer[snmp_ip]" value="{$ad_printer->snmp_ip|escape}" size="20" />
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />

</form>
<p/>

{if count($computers_list_snmp) > 0}
	<b>Computers already doing peripherals SNMP monitoring for this customer:</b>
	<ul>
		{foreach from=$computers_list_snmp key=computer_id item=monitored_peripherals}
			<li>
				<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">#{$computer_id}: {$computers_list.$computer_id}</a>
				({$monitored_peripherals|@count} peripheral{if count($monitored_peripherals)>1}s{/if})
			</li>
		{/foreach}
	</ul>
{else}
	NOTE: Currently no computer is selected to do peripherals SNMP
	monitoring for this customer.
{/if}