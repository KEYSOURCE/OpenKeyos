{assign var="paging_titles" value="KAWACS, Add Computer Manually"}
{assign var="computer_id" value=$computer->id}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Add Computer Manually</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="70%">
	<thead>
	<tr>
		<td width="120">Customer: <font color="red">*</font></td>
		<td class="post_highlight">
			<select name="computer[customer_id]">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$computer->customer_id}
			</select>
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Computer name: <font color="red">*</font></td>
		<td class="post_highlight">
			<input type="text" name="computer[netbios_name]" value="{$computer->netbios_name}" size="40" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Type: <font color="red">*</font></td>
		<td class="post_highlight">
			<select name="computer[type]">
				{html_options options=$COMP_TYPE_NAMES selected=$computer->type}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Profile: <font color="red">*</font></td>
		<td class="post_highlight">
			<select name="computer[profile_id]">
				<option value="">[Select profile]</option>
				{html_options options=$profiles_list selected=$computer->profile_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">MAC address: <font color="red">*</font></td>
		<td class="post_highlight">
			<input type="text" name="computer[mac_address]" value="{$computer->mac_address}" size="40" />
			<br/>
			If you don't know the real MAC, just write some random characters and put something
			like "(not real)" next to them.
		</td>
	</tr>
	<tr>
		<td class="highlight">Remote IP address:</td>
		<td class="post_highlight">
			<input type="text" name="computer[remote_ip]" value="{$computer->remote_ip}" size="40" />
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>
