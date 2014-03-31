<form action="" method="GET" name="frm_t" style="display:block; padding: 5px;">
{$form_redir}

<script language="JavaScript" type="text/javascript">

{literal}

// Set the window size
window.resizeTo (700, 550);

function updateStat (s)
{
	//frm = document.forms['frm_t'];
	//elm_stat = frm.elements['traceroute_results'];
	//elm_stat.value = elm_stat.value + s;
	
	elm_stat = document.getElementById ('traceroute_results');
	//re = /\s/gi; s = s.replace (re, "&nbsp;");
	
	re = /\n/gi; 
	has_break = (s.indexOf('\n')>=0);
	if (has_break) s = s.replace (re, '');
	elm_stat.appendChild(document.createTextNode(s));
	if (has_break) elm_stat.appendChild(document.createElement('br'));
	
	
	//elm_stat.appendChild(document.createElement('br'));
	//if (s.indexOf('\n')>=0) elm_stat.appendChild(document.createElement('br'));
}

function checkIP ()
{
	frm = document.forms['frm_t'];
	elm_ips = frm.elements['ips'];
	elm_ip = frm.elements['target_ip'];
	selected_ip = elm_ips.options[elm_ips.selectedIndex].value;
	
	elm_ip.value = selected_ip;
	
}

function runFinished (result)
{
	document.getElementById('result_running').style.display = 'none';
	
	if (result) document.getElementById('result_ok').style.display = '';
	else document.getElementById('result_failed').style.display = '';
}

function doClose ()
{
	window.close ();
}

{/literal}

</script>

<h2>Traceroute</h2>
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="20%">
			{if $customer->id}Customer:
			{else}Computer:
			{/if}
		</td>
		<td class="post_highlight">
			{if $computer->id} #{$computer->id}: {$computer->netbios_name|escape}
			{elseif $customer->id} #{$customer->id}: {$customer->name|escape}
			{else} [Unspecified]
			{/if}
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">IP Address:</td>
		<td class="post_highlight" nowrap="nowrap">
		
		
			{assign var="in_list" value=0}
			<select name="ips" onchange="checkIP();" style="width: 250px;">
				{if $computer->remote_ip}
					<option value="{$computer->remote_ip}" 
						{if $computer->remote_ip==$target_ip}selected {assign var="in_list" value=1}{/if}
					>Remote IP: {$computer->remote_ip}</option>
				{/if}
				
				{foreach from=$ips item=ip}
					<option value="{$ip.ip}">Local IP: {$ip.ip} - {$ip.adapter}</option>
				{/foreach}
				
				{foreach from=$customer_ips item=customer_ip}
					<option value="{$customer_ip->remote_ip}" 
						{if $monitored_ip->remote_ip==$target_ip}selected {assign var="in_list" value=1}{/if}>
						{$customer_ip->remote_ip} &nbsp;&nbsp;({$customer_ip->computers_count} computers)
					</option>
				{/foreach}
				
				{if !$in_list and $target_ip}
					<option value="{$target_ip}" selected>[Other] {$target_ip}</option>
				{/if}
			</select>
			&nbsp;&nbsp;&nbsp;
			<input type="text" name="target_ip" value="{$target_ip}" size="30" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Max hops:</td>
		<td class="post_highlight">
			<input type="text" name="max_hops" value="{$max_hops}" size="4" />
		</td>
	</tr>
	{if $run_traceroute}
	<tr>
		<td class="highlight">Traceroute:</td>
		<td class="post_highlight">
			<pre id="traceroute_results" style="display:block; height:auto; width:100%;"></pre>
		</td>
	</tr>
	<tr>
		<td class="highlight"><b>Result:</b></td>
		<td class="post_highlight">
			<div id="result_running"><b>[Traceroute running, please wait ...]</b></div>
			<div id="result_ok" style="display:none;"><b>OK, target reached</b></div>
			<div id="result_failed" class="error" style="display:none;">FAILED, target was not reached</div>
		</td>
	</tr>
	{/if}

</table>
<p/>

<input type="submit" name="run_traceroute" value="Run traceroute" class="botton" />
<input type="button" name="cancel" value="Close" onclick="doClose(); return false;" />

</form>

<script language="JavaScript" type="text/javascript">

checkIP();
</script>