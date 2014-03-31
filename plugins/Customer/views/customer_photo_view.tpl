{assign var="paging_titles" value="Customers, View Photo"}
{assign var="paging_urls" value=""}
{include file="paging.html"}

<h1>View Photo</h1>

<p class="error">{$error_msg}</p>

<table class="list" width="70%">
	<thead>
	<tr>
		<td width="120">Customer:</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td>Subject:</td>
		<td>{$photo->subject|escape}</td>
	</tr>
	
	{if $photo->ext_url}
	<tr>
		<td>External URL:</td>
		<td>
			<a href="{$photo->ext_url|escape}" target="_blank">{$photo->ext_url|escape}</a>
		</td>
	</tr>
	{/if}
	
	{if $photo->comments}
	<tr>
		<td>Comments:</td>
		<td>{$photo->comments|escape|nl2br}</td>
	</tr>
	{/if}
	
	{if $photo->object_class}
	<tr>
		<td>Linked to:</td>
		<td>
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
				<a href="/?cl=customer&amp;op=location_edit&amp;id={$location_id}">{$locations_list.$location_id}</a>
			{/if}
			
		</td>
	</tr>
	{/if}
</table>

<p>
<a href="{$ret_url|urldecode}">&#0171; Close</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a href="/?cl=customer&op=customer_photo_edit&amp;id={$photo->id}&amp;returl={$ret_url}">Edit &#0187;</a>
</p>


{if $photo->get_full_path()}
	<p>
		{if $photo->is_image()}
			{$photo->get_tag()}
		{else}
			<a href="{$photo->get_url()}">{$photo->get_tag()}<br/>
			[Download: {$photo->original_filename|escape}]</a>
		{/if}
	</p>
{else}
	<p>[Image not available]</p>
{/if}