{assign var="paging_titles" value="KAWACS, Manage Computers, Edit Computer Item "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_monitor_items"}
{include file="paging.html"}

<h1>Edit Computer Item: #{$computer_item->itemdef->id}: {$computer_item->itemdef->name}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="item_frm">
{$form_redir}


<table width="80%" class="list">
	<thead>
	<tr>
		<td width="140"><b>Computer:</td>
		<td><b># {$computer->id}: {$computer->netbios_name|escape}</b></td>
	</tr>
	</thead>
	
	<tr>
		<td>Value(s):</td>
		<td valign="top">
		
			{foreach from=$computer_item->val item=item_val key=nrc_val name=values}
		
				{if $computer_item->val|@count>1 and !$smarty.foreach.values.first}<p/><hr>{/if}
				
				{if $smarty.foreach.values.last and $computer_item->itemdef->multi_values == $smarty.const.MONITOR_MULTI_YES}
					<b>Add new value:</b>
					{if $computer_item->item_id==$smarty.const.WARRANTY_ITEM_ID}
						<br/>
						[<a href=""
						onClick="
							frm=document.forms['item_frm'];
							frm.elements['item[value][{$nrc_val}][{$warranty_product_field_id}]'].value = '{$computer_product_name}';
							frm.elements['item[value][{$nrc_val}][{$warranty_sn_field_id}]'].value = '{$computer_sn}';
							return false;"
						>Use computer data</a>]
					{/if}
					<p/>
				{/if}
			
				{if $computer_item->itemdef->struct_fields}
					<!-- This is a structure item -->
					<table>
						{foreach from=$computer_item->itemdef->struct_fields item=field_def key=nrc}
						<tr>
							<td>
								{assign var="field_id" value=$field_def->id}
								{$field_def->name} : 
							</td>
							<td>
								{if $field_def->type == $smarty.const.MONITOR_TYPE_TEXT}
									<textarea name="item[value][{$nrc_val}][{$field_id}]" rows="10" cols="60">{$item_val->value.$field_id}</textarea>
								{elseif $field_def->type == $smarty.const.MONITOR_TYPE_FILE}
									[n/a]
									<input type="hidden" name="item[value][{$nrc_val}][{$field_id}]" value="{$item_val->value.$field_id}">
								{elseif $field_def->type == $smarty.const.MONITOR_TYPE_DATE}
									{if $item_val->value.$field_id} {assign var="time" value=$item_val->value.$field_id}
									{else} {assign var="time" value="0000--"}
									{/if}
									
									{html_select_date 
										field_array="item[value][$nrc_val][$field_id]"
										start_year="-10"
										end_year="+10" 
										time=$time
										year_empty="--" month_empty="--" day_empty="--"
									}
								{elseif $field_def->type == $smarty.const.MONITOR_TYPE_LIST}
									{assign var="list_type" value=$field_def->list_type}
									{assign var="list_options" value=$AVAILABLE_ITEMS_LISTS.$list_type}
									<select name="item[value][{$nrc_val}][{$field_id}]">
										<option value="">--</option>
										{html_options options=$list_options selected=$item_val->value.$field_id}
									</select>
								{else}
									<input type="text" name="item[value][{$nrc_val}][{$field_id}]" value="{$item_val->value.$field_id}" size="60" />
								{/if}
							</td>
						</tr>
						{/foreach}
						<tr>
							<td colspan="2">
							{if $computer_item->itemdef->multi_values == $smarty.const.MONITOR_MULTI_NO or (!$smarty.foreach.values.last and $computer_item->itemdef->multi_values == $smarty.const.MONITOR_MULTI_YES)}
								<a href="{$delete_url}&val={$nrc_val}"
								onClick="return confirm('Are you sure you want to delete this value?');"
								>[Delete]</a>
							{/if}
							</td>
						</tr>
					</table>
				{else}
					<!-- This is a non-struct item -->
					{if $computer_item->itemdef->type == $smarty.const.MONITOR_TYPE_TEXT}
						<textarea name="item[value][{$nrc_val}]" rows="10" cols="60">{$item_val->value}</textarea>
					{elseif $computer_item->itemdef->type == $smarty.const.MONITOR_TYPE_FILE}
						[n/a]
						<input type="hidden" name="item[value][{$nrc_val}]" value="{$item_val->value}">
					{elseif $computer_item->itemdef->type == $smarty.const.MONITOR_TYPE_DATE}
						{assign var="times" value="0"}
						
						{if $item_val->value} {assign var="time" value=$item_val->value}
						{else} {assign var="time" value="0000-00-00"}
						{/if}

						{html_select_date 
							field_array="item[value][$nrc_val]" 
							end_year="+10"
							time=$time
							year_empty="--" month_empty="--" day_empty="--"
						}
					{elseif $computer_item->itemdef->type == $smarty.const.MONITOR_TYPE_LIST}
						{assign var="list_type" value=$computer_item->itemdef->list_type}
						{assign var="list_options" value=$AVAILABLE_ITEMS_LISTS.$list_type}
						<select name="item[value][{$nrc_val}]">
							<option value="">--</option>
							{html_options options=$list_options selected=$item_val->value}
						</select>
					{else}
						<input type="text" name="item[value][{$nrc_val}]" value="{$item_val->value}" size="60">
					{/if}
					
					{if $computer_item->itemdef->multi_values == $smarty.const.MONITOR_MULTI_NO or (!$smarty.foreach.values.last and $computer_item->itemdef->multi_values == $smarty.const.MONITOR_MULTI_YES)}
						&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;
						<a href="{$delete_url}&val={$nrc_val}"
						onClick="return confirm('Are you sure you want to delete this value?');"
						>[Delete]</a>
					{/if}
				{/if}
				
			{/foreach}
		</td>
	</tr>
</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
<p>

