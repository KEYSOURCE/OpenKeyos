{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Edit MAC Address"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Edit MAC Address</h1>

<font class="error">{$error_msg}</font>

<p>On this page you can edit the MAC address by which the computer is identified in Keyos.</p>
<p><b>WARNING:</b> Do NOT modify this unless you know what you are doing. Setting a wrong MAC addres 
could prevent Keyos from correctly identifying the computer from Kawacs Agent reports.</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="120">Computer:</td>
		<td class="post_highlight">#{$computer->id}: {$computer->netbios_name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Customer:</td>
		<td class="post_highlight">{$customer->name|escape} ({$customer->id})</td>
	</tr>
	<tr>
		<td class="highlight">MAC Address:</td>
		<td class="post_highlight">
			<input type="text" size="20" name="mac_address" value="{$computer->mac_address|escape}" />
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" 
onclick="return confirm('Are you really sure you want to modify the MAC address?');"
/>
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>
