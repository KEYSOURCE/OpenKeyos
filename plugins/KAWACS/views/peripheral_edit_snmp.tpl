{assign var="peripheral_id" value=$peripheral->id}
{assign var="paging_titles" value="KAWACS, Manage Peripherals, Edit Peripheral, Edit SNMP Settings"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripherals, ./cl=kawacs&op=peripheral_edit&id=$peripheral_id"}
{include file="paging.html"}


<h1>Edit SNMP Settings: #{$peripheral->id}: {$peripheral->name}</h1>

<p class="error">{$error_msg}</p>

<p>
Specify below the monitoring profile to be used for this peripheral
and the computer which should do the data gathering.<br/>
The list of available monitoring profiles is specified in the peripheral
class definition.
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
	<tr>
		<td>Peripheral class:</td>
		<td class="post_highlight">
			<a href="/?cl=kawacs&amp;op=peripheral_class_edit&amp;id={$peripheral->class_id}">{$class->name|escape}</a></td>
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Enable SNMP monitoring:</td>
		<td class="post_highlight">
			<select name="peripheral[snmp_enabled]">
				<option value="0">No</option>
				<option value="1" {if $peripheral->snmp_enabled}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Monitor profile:</td>
		<td class="post_highlight">
				<select name="peripheral[profile_id]">
					<option value="">[Select profile]</option>
					{foreach from=$class->profiles item=profile}
						<option value="{$profile->id}" {if $profile->id==$peripheral->profile_id}selected{/if}>{$profile->name|escape}</option>
					{/foreach}
				</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Computer:</td>
		<td class="post_highlight">
			<select name="peripheral[snmp_computer_id]">
				<option value="">[Select computer]</option>
				{html_options options=$computers_list selected=$peripheral->snmp_computer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">IP address:</td>
		<td class="post_highlight">
			<input type="text" name="peripheral[snmp_ip]" value="{$peripheral->snmp_ip|escape}" size="20" />
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