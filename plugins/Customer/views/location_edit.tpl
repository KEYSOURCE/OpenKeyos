{assign var="paging_titles" value="Customers, Manage Customers Locations, Edit Customer Location"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations"}
{include file="paging.html"}

<h1>Edit Customer Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table width="95%">
	<tr><td width="70%">
		<table class="list" width="95%">
			<thead>
			<tr>
				<td>Customer:</td>
				<td class="post_highlight">
					{assign var="customer_id" value=$location->customer_id}
					{$customers_list.$customer_id} (#{$customer_id})
				</td>
			</tr>
			<tr>
				<td>Town:</td>
				<td class="post_highlight">
					{assign var="town_id" value=$location->town_id}
					{$towns_list.$town_id}
				</td>
			</tr>
			<tr>
				<td>Parent location:</td>
				<td class="post_highlight">
					<a href="/?cl=customer&amp;op=manage_locations&amp;customer_id={$location->customer_id}"
					>[Top]</a>
					{foreach from=$location->parents item=parent name=parents_loop}
						&#0187; 
						<a href="/?cl=customer&amp;op=location_edit&amp;id={$parent->id}">{$parent->name}</a>
					{/foreach}
				</td>
			</tr>
			</thead>
			
			{if $location->parent_id}
			<tr>
				<td class="highlight" nowrap="nowrap">Street address:</td>
				<td class="post_highlight">
					{$location->street_address|escape|nl2br}
				</td>
			</tr>
			{/if}
			
			<tr>
				<td class="highlight" width="20%">Name:</td>
				<td class="post_highlight" width="80%">
					<input type="text" name="location[name]" value="{$location->name|escape}" size="40" />
				</td>
			</tr>
			<tr>
				<td class="highlight">Type:</td>
				<td class="post_highlight">
					<select name="location[type]">
						<option value="">[Select type]</option>
						{if $location->parent_id}
							{html_options options=$LOCATION_TYPES selected=$location->type}
						{else}
							{html_options options=$LOCATION_TYPES_TOP selected=$location->type}
						{/if}
					</select>
				</td>
			</tr>
			{if !$location->parent_id}
			<tr>
				<td class="highlight" nowrap="nowrap">Street address:</td>
				<td class="post_highlight">
					<textarea name="location[street_address]" rows="4" cols="40">{$location->street_address|escape}</textarea>
				</td>
			</tr>
			{/if}
		</table>
		<p/>
		
		<input type="submit" name="save" value="Save" class="button" />
		<input type="submit" name="cancel" value="Close" class="button" />
		
		</form>
	</td>
	<td>
		<table class="list" width="100%">
			<thead>
			<tr>
				<td width="70%">Sub-locations</td>
				<td nowrap="nowrap" align="right">
					<a href="/?cl=customer&amp;op=location_add&amp;parent_id={$location->id}&amp;returl={$ret_url}">Add &#0187;</a>
				</td>
			</tr>
			</thead>
	
			{foreach from=$location->children item=sub_location}
			<tr>
				<td>
					<a href="/?cl=customer&amp;op=location_edit&amp;id={$sub_location->id}&amp;returl={$ret_url}">{$sub_location->name|escape}</a>
				</td>
				<td align="right">
					{assign var="type" value=$sub_location->type}
					{$LOCATION_TYPES.$type}
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="2" class="light_text">[No sub-locations]</td>
			</tr>
			{/foreach}
		</table>
	</td></tr>
</table>


<table width="95%">
	<tr><td width="50%">
		<h2>Comments | <a href="/?cl=customer&amp;op=location_comment_add&location_id={$location->id}">Add comment &#0187;</a></h2>
		<p/>
		<table class="list" width="95%">
			<thead>
			<tr>
				<td width="90%">Comments</td>
				<td width="10%"> </td>
			</tr>
			</thead>
			{foreach from=$location->comments item=comment}
			<tr>
				<td>
					{assign var="user_id" value=$comment->user_id}
					<a href="/?cl=customer&amp;op=location_comment_edit&amp;id={$comment->id}"
					>{$comment->updated|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY},
					{$users_list.$user_id}</a>
					<br/>
					{$comment->comments|escape|nl2br}
				</td>
				<td align="right" nowrap="nowrap">
					<a href="/?cl=customer&amp;op=location_comment_delete&amp;id={$comment->id}"
						onclick="return confirm('Are you really sure you want to delete this comment?');"
					>Delete &#0187;</a>
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="2" class="light_text">[No comments]</td>
			</tr>
			{/foreach}
		</table>
	</td><td>
		<h2>Photos | <a href="/?cl=customer&amp;op=customer_photo_add&amp;location_id={$location->id}&amp;returl={$ret_url}">Add photo &#0187;</a></h2>
		<p/>
		
		<table class="list" width="95%">
			<thead>
			<tr>
				<td colspan="3">Pictures</td>
			</tr>
			</thead>
		
			{foreach from=$location->photos item=photo}
			<tr>
				<td width="100">
					<a href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->get_thumb_tag()}</a></td>
				</td><td width="90%">
					<a href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->subject|escape}</a><br/>
					{if $photo->comments}
						{$photo->comments|escape|nl2br}
					{/if}
				</td>
				<td width="10%" align="right" nowrap="nowrap">
					<a href="/?cl=customer&amp;op=customer_photo_delete&amp;id={$photo->id}&amp;returl={$ret_url}"
						onclick="return confirm('Are you sure you want to delete this photo?');"
					>Delete &#0187;</a>
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td class="light_text">[No photos]</td>
			</tr>
			{/foreach}
		</table>
	</td></td>
</table>


<table width="95%">
	<tr><td width="50%">
		<h2>Computers | <a href="/?cl=customer&amp;op=location_computers&amp;id={$location->id}">Edit computers list &#0187;</a></h2>
		<p/>
		
		<table class="list" width="95%">
			<thead>
			<tr>
				<td width="1%">ID</td>
				<td width="99%">Computer name</td>
			</tr>
			</thead>
			
			{foreach from=$location->computers_list key=computer_id item=computer_name}
			<tr>
				<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">{$computer_id}</a></td>
				<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">{$computer_name|escape}</a></td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="2" class="light_text">[No computers assigned]</td>
			</tr>
			{/foreach}
		</table>
	</td>
	<td width="50%">
		<h2>Peripherals | <a href="/?cl=customer&amp;op=location_peripherals&amp;id={$location->id}">Edit peripherals list &#0187;</a></h2>
		<p/>
		
		<table class="list" width="95%">
			<thead>
			<tr>
				<td width="1%">ID</td>
				<td width="99%">Peripheral name</td>
			</tr>
			</thead>
			
			{foreach from=$location->peripherals_list key=peripheral_id item=peripheral_name}
			<tr>
				<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripheral_id}</a></td>
				<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripheral_name|escape}</a></td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="2" class="light_text">[No peripherals assigned]</td>
			</tr>
			{/foreach}
		</table>
		
		<h2>AD Printers | <a href="/?cl=customer&amp;op=location_ad_printers&amp;id={$location->id}">Edit AD Printers list &#0187;</a></h2>
		<p/>
		
		<table class="list" width="95%">
			<thead>
			<tr>
				<td width="100%">Printer name</td>
			</tr>
			</thead>
			
			{foreach from=$location->ad_printers_list key=printer_cn item=printer_name}
			<tr>
				<td>
					{assign var="computer_id" value=$printers_cn_ids.$printer_cn->computer_id}
					{assign var="nrc" value=$printers_cn_ids.$printer_cn->nrc}
					<a href="/?cl=kerm&amp;op=ad_printer_view&amp;computer_id={$computer_id}&amp;nrc={$nrc}">{$printer_name|escape}</a>
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="2" class="light_text">[No printers assigned]</td>
			</tr>
			{/foreach}
		</table>
	</td></tr>
</table>
