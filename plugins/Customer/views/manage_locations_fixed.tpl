{assign var="paging_titles" value="Customers, Manage Fixed Locations"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<h1>Manage Fixed Locations</h1>

<p class="error">{$error_msg}</p>

<p>
|
<a href="/?cl=customer&amp;op=location_fixed_add">Add Country</a> |
<a href="/?cl=customer&amp;op=manage_locations">Manage Customer Locations</a> |
</p>


<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="80%">

	{foreach from=$countries item=country}
		<tr class="head">
			<td nowrap="nowrap" width="50%">
				<a href="/?cl=customer&amp;op=location_fixed_edit&amp;id={$country->id}">{$country->name|escape}</a>
			</td>
			<td nowrap="nowrap" width="20%">Customers locations</td>
			<td nowrap="nowrap" width="20%">
				<a href="/?cl=customer&amp;op=location_fixed_add&amp;parent_id={$country->id}&amp;type={$smarty.const.LOCATION_FIXED_TYPE_PROVINCE}"
				>Add Province &#0187;</a>
			</td>
			<td nowrap="nowrap" width="10%" align="right">
				<a href="/?cl=customer&amp;op=location_fixed_delete&amp;id={$contry->id}"
					onclick="return confirm('Are you sure you want to delete this location?');"
				>Delete &#0187;</a>
			</td>
		</tr>
		{foreach from=$country->children item=province}
			<tr>
				<td nowrap="nowrap" style="padding-left: 20px;" colspan="2">
					<a href="/?cl=customer&amp;op=location_fixed_edit&amp;id={$province->id}"
					><b>{$province->name|escape}</b></a>
				</td>
				<td nowrap="nowrap">
					<a href="/?cl=customer&amp;op=location_fixed_add&amp;parent_id={$province->id}&amp;type={$smarty.const.LOCATION_FIXED_TYPE_TOWN}"
					>Add Town &#0187;</a>
				</td>
				<td nowrap="nowrap" align="right">
					<a href="/?cl=customer&amp;op=location_fixed_delete&amp;id={$contry->id}"
						onclick="return confirm('Are you sure you want to delete this location?');"
					>Delete &#0187;</a>
				</td>
			</tr>
			{foreach from=$province->children item=town}
				<tr>
					<td style="padding-left: 40px;">
						<a href="/?cl=customer&amp;op=location_fixed_edit&amp;id={$town->id}">{$town->name|escape}</a>
					</td>
					<td nowrap="nowrap">
						{if $town->locations_count}
							<a href="/?cl=customer&amp;op=location_fixed_customers&amp;id={$town->id}"
							>{$town->locations_count}: View &#0187;</a>
						{else}
							<font class="light_text">--</font>
						{/if}
					</td>
					<td> </td>
					<td nowrap="nowrap" align="right">
						<a href="/?cl=customer&amp;op=location_fixed_delete&amp;id={$contry->id}"
							onclick="return confirm('Are you sure you want to delete this location?');"
						>Delete &#0187;</a>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td class="light_text" style="padding-left: 40px;" colspan="4">[No towns]</td>
				</tr>
			{/foreach}
		{foreachelse}
			<tr>
				<td class="light_text" style="padding-left: 20px;" colspan="4">[No provinces]</td>
			</tr>
		{/foreach}
	{foreachelse}
	<tr>
		<td class="light_text">[No countries defined]</td>
	</tr>
	{/foreach}

</table>


</form>