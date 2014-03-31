{assign var="paging_titles" value="Customers, Manage Customers Photos"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<h1>Manage Customers Photos</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="manage_photos">
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			Customer:
			<select name="filter[customer_id]"
				onChange="document.forms['manage_photos'].elements['do_filter_hidden'].value=1; document.forms['manage_photos'].submit();"
			>
				<option value="">[Select]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $filter.customer_id}
				<a href="/?cl=customer&op=customer_photo_add&customer_id={$filter.customer_id}&returl={$ret_url}">Add photo &#0187;</a>
			{/if}
		</td>
</table>
</form>

<p>Click on an image to enlarge it, click on a subject to edit it.</p>

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="15%">Image</td>
		<td width="35%">Subject/Comments</td>
		<td width="40%">Linked to</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$photos item=photo}
		<tr>
			
			<td><a href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->get_thumb_tag()}</a>
			</td>
			
			<td>
				<a href="/?cl=customer&amp;op=customer_photo_edit&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->subject}</a>
				{if $photo->comments}
					<p/>{$photo->comments|escape|nl2br}
				{/if}
				{if $photo->ext_url}
					<br/>Link:<a href="{$photo->ext_url|escape}" target="_blank">{$photo->ext_url|escape}</a>
				{/if}
				
			</td>
			<td>
				{if $photo->object_class}
					{if $photo->object_class==$smarty.const.PHOTO_OBJECT_CLASS_COMPUTER}
						Computer:
						{assign var="computer_id" value=$photo->object_id}
						<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">{$computers_list.$computer_id}</a>
					{elseif $photo->object_class==$smarty.const.PHOTO_OBJECT_CLASS_PERIPHERAL}
						Peripheral:
						{assign var="peripheral_id" value=$photo->object_id}
						<a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripherals_list.$peripheral_id}</a>
					{elseif $photo->object_class==$smarty.const.PHOTO_OBJECT_CLASS_LOCATION}
						Location:
						{assign var="location_id" value=$photo->object_id}
						<a href="/?cl=customer&amp;op=location_edit&amp;id={$location_id}&amp;returl={$ret_url}">{$locations_list.$location_id}</a>
					{/if}
				{else}
					<font class="light_text">--</td>
				{/if}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=customer&amp;op=customer_photo_delete&amp;id={$photo->id}&amp;returl={$ret_url}"
					onClick="return confirm ('Are you really sure you want to delete this photo?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4">[No photos uploaded yet]</td>
		</tr>
	{/foreach}

</table>

