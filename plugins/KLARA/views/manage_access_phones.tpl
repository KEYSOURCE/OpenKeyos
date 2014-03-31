{assign var="paging_titles" value="KLARA, Manage Access Phones"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Manage Access Phones</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
{$form_redir}


<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $customer->id}
				<a href="/?cl=klara&op=access_phone_add&customer_id={$customer->id}"><b>Add access number &#0187;</b></a>
			{/if}
		</td>
	</tr>
</table>
<p/>

{if $customer->id}
	
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="10">ID</td>
			<td>Phone number</td>
			<td>Connected to</td>
			<td>Login</td>
			<td>Comments</td>
			<td> </td>
		</tr>
		</thead>

		{foreach from=$access_phones item=access_phone}
			<tr>
				<td><a href="/?cl=klara&op=access_phone_edit&id={$access_phone->id}">{$access_phone->id}</a></td>
				<td><a href="/?cl=klara&op=access_phone_edit&id={$access_phone->id}">{$access_phone->phone}</a></td>
				<td>
					{assign var="device_type" value=$access_phone->device_type}
					{$PHONE_ACCESS_DEVICES.$device_type}
					{if $access_phone->object_id}
						: 
						{assign var="object_id" value=$access_phone->object_id}
						{if $access_phone->device_type == $smarty.const.PHONE_ACCESS_DEV_COMPUTER}
							{$computers_list.$object_id}
						{elseif $access_phone->device_type == $smarty.const.PHONE_ACCESS_DEV_PERIPHERAL}
							{$peripherals_list.$object_id}
						{/if}
					{/if}
				</td>
				<td>
					{$access_phone->login} / {$access_phone->password}
				</td>
				<td>{$access_phone->comments|escape}</td>
				<td align="right" nowrap="nowrap">
					<a href="/?cl=klara&op=access_phone_delete&id={$access_phone->id}"
						onClick="return confirm('Are you really sure you want to delete this number?');"
					>Delete &#0187;</a>
				</td>
			</tr>
		
		{foreachelse}
			<tr>
				<td colspan="6">[No access phone numbers defined yet]</td>
			</tr>
		{/foreach}
		
	</table>
{/if}

</form>
<p/>
