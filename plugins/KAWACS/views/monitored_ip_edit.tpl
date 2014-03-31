{assign var="paging_titles" value="KAWACS, Internet Monitoring, Edit Monitored IP"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_monitored_ips"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var customer_id = {$monitored_ip->customer_id};
var returl = "{$ret_url}";

{literal}

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

function showInternetContract ()
{
	frm = document.forms['frm_t'];
	elm_contracts = frm.elements['monitored_ip[internet_contract_id]'];
	selected_id = elm_contracts.options[elm_contracts.selectedIndex].value;
	
	if (selected_id == '0') alert ('Please select a contract first.');
	else document.location = '/?cl=klara&op=customer_internet_contract_edit&id='+selected_id+'&returl='+returl;
	
	return false;
}

{/literal}
//]]>
</script>

<h1>Edit Monitored IP</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<table width="90%" class="list">
	<thead>
	<tr>
		<td width="15%">Customer:</td>
		<td width="85%" class="post_highlight">#{$monitored_ip->customer->id}: {$monitored_ip->customer->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Internet contract:</td>
		<td class="post_highlight" nowrap="nowrap">
			{if $internet_contracts}
				<select name="monitored_ip[internet_contract_id]">
					<option value="0">[No contract]</option>
					{foreach from=$internet_contracts item=contract}
					<option value="{$contract->id}" {if $monitored_ip->internet_contract_id==$contract->id}selected{/if}
					>{$contract->provider->name}: {$contract->provider_contract->name}</option>
					{/foreach}
				</select>
				
				&nbsp;&nbsp;
				[<a href="#" onclick="return showInternetContract();">View contract &#0187;</a>]
			{else}
				<font class="light_text">[No internet contract defined]</font>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Remote IP:</td>
		<td class="post_highlight" nowrap="nowrap">
			<input type="text" name="monitored_ip[remote_ip]" value="{$monitored_ip->remote_ip|escape}" size="20" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Target IP:</td>
		<td class="post_highlight">
			<input type="text" name="monitored_ip[target_ip]" value="{$monitored_ip->target_ip}">
			&nbsp;&nbsp;&nbsp;
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
	<tr {if $monitored_ip->is_down()}class="error"{/if}>
		<td class="highlight">Status:</td>
		<td class="post_highlight">
			{assign var="status" value=$monitored_ip->status}
			{$MONITOR_STATS.$status}
		</td>
	</tr>
	<tr>
		<td class="highlight">Ping OK:</td>
		<td class="post_highlight">
			{if $monitored_ip->ping_ok}Yes
			{else}No
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Last ping:</td>
		<td class="post_highlight">
			{if $monitored_ip->last_ping_test}{$monitored_ip->last_ping_test|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Last traceroute:</td>
		<td class="post_highlight">
			{if $monitored_ip->last_traceroute_test}{$monitored_ip->last_traceroute_test|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Last test duration:</td>
		<td class="post_highlight">
			{if $monitored_ip->last_test_duration}{$monitored_ip->last_test_duration} sec.
			{else}<font class="light_text">--</font>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Created by:</td>
		<td class="post_highlight">
			{if $monitored_ip->user}{$monitored_ip->user->get_name()}
			{else}<font class="light_text">--</font>
			{/if}
			,
			{if $monitored_ip->created}{$monitored_ip->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			<textarea name="monitored_ip[comments]" rows="4" cols="60">{$monitored_ip->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>

<p/>
<h2>Information</h2>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="30%">Computers</td>
		<td width="70%">Last ping / Last traceroute</td>
	</tr>
	</thead>
	
	<tr>
		<td>
		{foreach from=$computers_list key=id item=computer_name}
            {assign var="p" value="id:"|cat:$id}
			<a href="{'kawacs'|get_link:'computer_view':$p:'template'}">#{$id}: {$computer_name|escape}</a><br/>
		{/foreach}
		</td>
		<td>
			{if $monitored_ip->last_ping}
				<pre>{$monitored_ip->last_ping|escape}</pre>
			{else}
				<font class="light_text">--</font>
			{/if}
			<hr>
			{if $monitored_ip->last_traceroute}
				<pre>{$monitored_ip->last_traceroute|escape}</pre>
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
	</tr>
</table>
