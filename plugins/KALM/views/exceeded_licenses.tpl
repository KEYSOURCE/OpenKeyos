{assign var="paging_titles" value="KALM, Exceeded Licenses"}
{assign var="paging_urls" value="/?cl=kalm"}
{include file="paging.html"}

<h1>Exceeded Licenses</h1>

<p class="error">{$error_msg}</p>

The table below shows the customers which have reached or exceeded their licenses.
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="20%">Customer</td>
		<td width="35%">Software</td>
		<td width="10%">License type</td>
		<td width="10%">Available</td>
		<td width="15%">Used</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$exceeded_licenseses key=customer_id item=licenses}
		{foreach from=$licenses item=license}
		<tr>
			<td><a href="/?cl=customer&amp;op=customer_edit&amp;customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id} ({$customer_id})</a></td>
			<td>
				{$license->software->name}
				{if $license->no_notifications}
				<br/><font class="light_text">[No notifications]</font>
				{/if}
			</td>
			<td>
				{assign var="license_type" value=$license->license_type}
				{$LIC_TYPES_NAMES.$license_type}
			</td>
			{if $license->need_kawacs_counting()}
				<td {if $license->used_licenses > $license->licenses}class="error"{/if}>
					{$license->licenses_all}
				</td>
				<td {if $license->used_licenses > $license->licenses}class="error"{/if} nowrap="nowrap">
					{$license->used_licenses}
					
					<a href=".?cl=kalm&amp;op=license_computers&amp;id={$license->id}">computers &#0187;</a>
				</td>
			{else}
				<td {if $license->used > $license->licenses}class="error"{/if}>
					{$license->licenses}
				</td>
				<td {if $license->used > $license->licenses}class="error"{/if}>
					{$license->used}
				</td>
			{/if}
			<td align="right" nowrap="nowrap">
				<a href="/?cl=kalm&amp;op=license_edit&amp;id={$license->id}">Details &#0187;</a>
			</td>
		</tr>
		{/foreach}
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" colspan="6">[No customers have exceeded their licenses]</td>
	</tr>
	{/foreach}

</table>