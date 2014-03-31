{assign var="paging_titles" value="KALM, Manage Licenses"}
{assign var="paging_urls" value="/?cl=kalm"}
{include file="paging.html"}

<h1>Manage Licenses 
	{if $customer->id}
	 : {$customer->name} ({$customer->id})
	{/if}
</h1>
<p>
<font class="error">{$error_msg}</font>
<p>


<form action="" method="GET" name="filter"> 
{$form_redir}

<b>Customer: </b>
<select name="customer_id" onChange="document.forms['filter'].submit()">
	<option value="">[Select customer]</option>
	{foreach from=$customers item=cust key=id}
		<option value="{$id}" {if $customer->id==$id}selected{/if}>
			{$cust} {if $id!=' '}({$id}){/if}
		</option>
	{/foreach}
</select>
</form>
<p>


{if $customer->id}

	<!-- Show this information only if a customer has been specified -->
	
	<p>
	<a href="/?cl=kalm&op=license_add&customer_id={$customer->id}">Add license &#0187;</a>
	<p>
	
	<table class="list" width="98%">
		<thead>
			<tr>
				<td>Software</td>
				<td>Manufacturer</td>
				<td>Type</td>
				<td>Licenses</td>
				<td nowrap>Used *</td>
				<td>Issue date</td>
				<td>Exp. date</td>
				<td>Report</td>
				<td> </td>
			</tr>
		</thead>

		{foreach from=$licenses item=license}
			<tr>
				<td>
					<a href="/?cl=kalm&op=license_edit&id={$license->id}">{$license->software->name}</a>
					{if $license->no_notifications}
						<br/><font class="light_text">[No notifications]</font>
					{/if}
					{if $license->comments}
						<br/>{$license->comments|escape|nl2br}
					{/if}
				</td>
				<td>{$license->software->manufacturer}</td>
				
				<td>
					{assign var="lic_type" value=$license->license_type}
					{$LIC_TYPES_NAMES.$lic_type}
				</td>
				
				<td>
					{if $license->licenses == -1}
						<i class="light_text">Unlimited</i>
					{else}
						{$license->licenses}
					{/if}
				</td>
				<td nowrap>
					{if $license->license_type == $smarty.const.LIC_TYPE_SEAT or $license->license_type == $smarty.const.LIC_TYPE_FREEWARE}
						{if $license->licenses>=0 and $license->licenses_all < $license->used_licenses}<font class="error">{/if}
						
						{$license->used_licenses}
						
						{if $license->licenses>=0 and $license->licenses_all < $license->used_licenses}</font>{/if}
						
						<a href="/?cl=kalm&op=license_computers&id={$license->id}">computers &#0187;</a>
					{elseif $license->license_type == $smarty.const.LIC_TYPE_CLIENT}
						{if $license->licenses>=0 and $license->licenses < $license->used}<font class="error">{/if}
					
						{$license->used}
						
						{if $license->licenses>=0 and $license->licenses < $license->used}</font>{/if}
					{/if}
				</td>

				
				<td nowrap>{$license->issue_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
				<td nowrap>
					{if $license->exp_date}
						{$license->exp_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
					{else}
						--
					{/if}
				</td>
				<td>{if $license->software->in_reports}Yes{else}No{/if}</td>
				<td>
					<a href="/?cl=kalm&op=license_delete&id={$license->id}"
						onClick="return confirm('Are you sure you want to delete this?');"
					>Delete</a>
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="4">[No licenses defined yet]</td>
			</tr>
		{/foreach}

	</table>
	<p>
	<b>Note:</b> For "Per seat" and "Freeware" licenses, the above table will show the number of used licenses 
	based on the current KAWACS information from the database.
	<p>
	Click on the "Used" number to
	see which computers are using that software.
	
	<p>
	<a href="/?cl=kalm&op=license_add&customer_id={$customer->id}">Add license &#0187;</a>
	<p>
	
{/if}
