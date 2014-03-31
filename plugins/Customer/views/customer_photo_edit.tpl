{assign var="paging_titles" value="Customers, Manage Customers Photos, Edit Photo"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_customers_photos"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var class_computer = {$smarty.const.PHOTO_OBJECT_CLASS_COMPUTER};
var class_peripheral = {$smarty.const.PHOTO_OBJECT_CLASS_PERIPHERAL};
var class_location = {$smarty.const.PHOTO_OBJECT_CLASS_LOCATION};

{literal}
function check_linked_objects ()
{
	frm = document.forms['photo_frm'];
	elm = frm.elements['photo[object_class]']
	
	selected_class = elm.options[elm.selectedIndex].value;
	
	computers_lst = document.getElementById ('computers_list');
	peripherals_lst = document.getElementById ('peripherals_list');
	locations_lst = document.getElementById ('locations_list');
	
	computers_lst.style.display = 'none';
	peripherals_lst.style.display = 'none';
	
	if (selected_class == class_computer)
	{
		computers_lst.style.display = '';
	}
	else if (selected_class == class_peripheral)
	{
		peripherals_lst.style.display = '';
	}
	else if (selected_class == class_location)
	{
		locations_lst.style.display = '';
	}
}

{/literal}

//]]>
</script>


<h1>Edit Photo</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" enctype="multipart/form-data" name="photo_frm">
{$form_redir}


<table class="list" width="70%">
	<thead>
	<tr>
		<td width="120">Customer:</td>
		<td >{$customer->name} ({$customer->id})</td>
		<td width="40%">Preview</td>
	</tr>
	</thead>
	
	<tr>
		<td>File:</td>
		<td>
			<input type="file" name="photo_file" value="Choose file...">
			{if $photo->get_full_path() and $photo->width}
				<br/>[{$photo->original_filename|escape}]
			{/if}
		</td>
		<td>
			{if $photo->get_full_path()}
				{$photo->get_thumb_tag()}
			{else}
				<font class="light_text">[no image]</font>
			{/if}
		</td>
	</tr>
	<tr>
		<td>Subject:</td>
		<td colspan="2">
			<input type="text" name="photo[subject]" value="{$photo->subject|escape}" size="60">
		</td>
	</tr>
	<tr>
		<td>External URL:</td>
		<td colspan="2">
			<input type="text" name="photo[ext_url]" value="{$photo->ext_url|escape}" size="60">
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td colspan="2">
			<textarea name="photo[comments]" rows="5" cols="60">{$photo->comments|escape}</textarea>
		</td>
	</tr>
	<tr>
		<td>Linked to:</td>
		<td colspan="2">
			<select name="photo[object_class]" onchange="check_linked_objects()">
				<option value="0">[None]</option>
				{html_options options=$PHOTO_OBJECT_CLASSES selected=$photo->object_class}
			</select>
			
			<select name="photo[computer_id]" style="display:none;" id="computers_list">
				<option value="">[Select computer]</option>
				{html_options options=$computers_list selected=$photo->object_id}
			</select>
			
			<select name="photo[peripheral_id]" style="display:none;"  id="peripherals_list">
				<option value="">[Select peripheral]</option>
				{html_options options=$peripherals_list selected=$photo->object_id}
			</select>
			
			<select name="photo[location_id]" style="display:none;"  id="locations_list">
				<option value="">[Select location]</option>
				{html_options options=$locations_list selected=$photo->object_id}
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Close"/>
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_linked_objects ();
//]]>
</script>



<h2>Full Photo:</h2>

{if $photo->get_full_path()}
	{if $photo->is_image()}
		Image size: {$photo->width} x {$photo->height}<p/>
		{$photo->get_tag()}
	{else}
		<p/>
		<a href="{$photo->get_url()}">{$photo->get_tag()}<br/>
		[Download: {$photo->original_filename|escape}]</a>
	{/if}
	</p>
{else}
	<p>[Image not available]</p>
{/if}