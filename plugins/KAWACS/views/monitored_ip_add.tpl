{assign var="paging_titles" value="KAWACS, Internet Monitoring, Add Monitored IP"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_monitored_ips"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var customer_id = {$customer->id};

{literal}
function checkIP ()
{
	frm = document.forms['frm_t'];
	elm_list = frm.elements['customers_ips'];
	elm_ip = frm.elements['monitored_ip[remote_ip]'];
	selected_ip = elm_list.options[elm_list.selectedIndex].value;
	
	if (selected_ip != '') elm_ip.value = selected_ip;
}

function setDefaultTargetIP ()
{
	frm = document.forms['frm_t'];
	elm_ip = frm.elements['monitored_ip[remote_ip]'];
	elm_target = frm.elements['monitored_ip[target_ip]'];
	
	elm_target.value = elm_ip.value;
	
	return false;
}

// Open the traceroute window
var last_traceroute_window = false;
function runTraceroute ()
{
	target_ip = document.forms['frm_t'].elements['monitored_ip[target_ip]'].value;
	
	if (target_ip == '') alert ('Please specify a target IP.');
	else
	{
		if (last_traceroute_window) last_traceroute_window.close ();
		popup_url = '/?cl=kawacs&op=popup_traceroute&customer_id='+customer_id+'&target_ip='+target_ip+'&run_traceroute=1';
		last_traceroute_window = window.open (popup_url, 'Traceroute', 'dependent, scrollbars=yes, resizable=yes, width=100, height=100');
	}
	return false;
}


{/literal}
//]]>
</script>

<h1>Add Monitored IP</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<p>The <b>Remote IP</b> should be a public address of a computer. The <b>Target IP</b> is the
last IP address that can be pinged/traceroute for that Remote IP. If the Remote IP can be
pinged, then the Target IP can be the same as the Remote IP.
</p>

<table width="90%" class="list">
	<thead>
	<tr>
		<td width="15%">Customer:</td>
		<td width="85%" class="post_highlight">#{$customer->id}: {$customer->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Internet contract:</td>
		<td class="post_highlight">
			{if $internet_contracts}
				<select name="monitored_ip[internet_contract_id]">
					<option value="0">[No contract]</option>
					{foreach from=$internet_contracts item=contract}
					<option value="{$contract->id}" {if $monitored_ip->internet_contract_id==$contract->id}selected{/if}
					>{$contract->provider->name}: {$contract->provider_contract->name}</option>
					{/foreach}
				</select>
			{else}
				<font class="light_text">[No internet contract defined]</font>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Remote IP:</td>
		<td class="post_highlight" nowrap="nowrap">
			<input type="text" name="monitored_ip[remote_ip]" value="{$monitored_ip->remote_ip|escape}" size="20" />
			&nbsp;&nbsp;&nbsp;
			<select name="customers_ips" onchange="checkIP()">
				<option value="">[Select remote IP]</option>
				{foreach from=$customers_ips item=customer_ip}
				<option value="{$customer_ip->remote_ip}" {if $monitored_ip->remote_ip==$customer_ip->remote_ip}selected{/if}>
				{$customer_ip->remote_ip} &nbsp;&nbsp;({$customer_ip->computers_count} computers)
				</option>
				{/foreach}
			</select>
			
		</td>
	</tr>
	<tr>
		<td class="highlight">Target IP:</td>
		<td class="post_highlight">
			<input type="text" name="monitored_ip[target_ip]" value="{$monitored_ip->target_ip}">
			&nbsp;&nbsp;&nbsp;
			[<a href="#" onclick="return setDefaultTargetIP();">Use remote IP</a>]
			[<a href="#" onclick="return runTraceroute();">Test traceroute</a>]
		</td>
	</tr>
	<tr>
		<td class="highlight">Enabled:</td>
		<td class="post_highlight">
			<select name="monitored_ip[disabled]">
				<option value="0">Enabled</option>
				<option value="1" {if $monitored_ip->disabled}selected{/if}>Disabled</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			<textarea name="monitored_ip[comments]" rows="4" cols="60">{$monitored_ip->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>