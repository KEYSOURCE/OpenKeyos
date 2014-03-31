{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Remote Access"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Remote Access: {$computer->netbios_name|escape} ({$computer->id})</h1>

<p class="error">{$error_msg}</p>
[ <a href="{$computer_view_link}">&#0171; Back to computer</a> ]
&nbsp;&nbsp;&nbsp;
{assign var="retl" value=$ret_url|urldecode}
{if $view == 'simple'}
    {assign var="egp" value="view:"|cat:"advanced"}
	[ <a href="{$retl|add_extra_get_params:$egp:'template'}">Advanced view</a> ]
{else}
    {assign var="egp" value="view:"|cat:"simple"}
	[ <a href="{$retl|add_extra_get_params:$egp:'template'}">Simple view</a> ]
{/if}
&nbsp;&nbsp;&nbsp;

{assign var="p" value="id:"|cat:$computer->id}
[ <a href="{'klara'|get_link:'computer_remote_services':$p:'template'}">Edit services &#0187;</a> ]
{assign var="p" value="customer_id:"|cat:$customer->id}
[ <a href="{'klara'|get_link:'manage_access':$p:'template'}">KLARA Information &#0187;</a> ]
<p/>

<form action="" method="POST" name="remote_access">
{$form_redir}

{if $view=='simple'}
	<!-- Simple view -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="4">Remote connections</td>
		</tr>
		</thead>
	
		<tr>
			<td class="highlight" width="15%">Connect using:</td>
			<td class="post_highlight" colspan="3">
				<select name="connect_using" style="width: 260px;">
					{if $computer->remote_ip}
					<option value="{$computer->remote_ip}">Remote IP: {$computer->remote_ip}</option>
					{/if}
					{foreach from=$ips item=ip}
					<option value="{$ip.ip}">Local IP: {$ip.ip} - {$ip.adapter}</option>
					{/foreach}
					<option value="127.0.0.1" selected>Localhost: 127.0.0.1</option>
				</select>
			</td>
		</tr>
		<tr {if !$vnc_info}style="display:none;"{/if}>
			<td class="highlight" width="15%">VNC:</td>
			<td width="35%" class="post_highlight" nowrap="nowrap">
				<select name="vnc_opts" onchange="vnc_changed()" style="width: 260px">
					{foreach from=$vnc_info item=vnc}
						<option>Port:{$vnc.$vnc_port_id}; Hash:{$vnc.$vnc_pwd_id}</option>
					{/foreach}
				</select>
			</td>
			<td width="15%" nowrap="nowrap">
				<div id="div_plink_port_vnc" style="display:none;">
				Plink port:
				<input type="text" name="vnc_port" value="{$vnc.$vnc_port_id}" size="10"/>
				<input type="hidden" name="vnc_hash" value="{$vnc.$vnc_pwd_id}"/>
				</div>
			</td>
			<td width="35%" class="post_highlight">
				<input type="submit" name="connect_vnc" value="Connect with VNC &#0187;"/>
			</td>
		</tr>
		<tr>
			<td class="highlight">Remote Desktop:</td>
			<td class="post_highlight">
				Port: 3389
			</td>
			<td nowrap="nowrap">
				<div id="div_plink_port_rdp" style="display:none;">
				Plink port: <input type="text" name="rdp_port" value="3389" size="10"/>
				</div>
			</td>
			<td class="post_highlight">
				<input type="submit" name="connect_rdp" value="Connect with Remote Desktop &#0187;"/>
			</td>
		</tr>
	</table>
{else}
	<!-- Advanced view -->

	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="3">Remote connections</td>
		</tr>
		</thead>
		<tr {if !$vnc_info}style="display:none;"{/if}>
			<td class="highlight" width="15%">VNC:</td>
			<td width="35%" class="post_highlight" nowrap="nowrap">
				<select name="vnc_opts" onchange="vnc_changed()" style="width: 260px">
					{foreach from=$vnc_info item=vnc}
						<option>Port:{$vnc.$vnc_port_id}; Hash:{$vnc.$vnc_pwd_id}</option>
					{/foreach}
				</select>
			</td>
			
			<td width="50%" nowrap="nowrap">
				<div id="div_plink_port_vnc" style="display:none;">
				Plink port:
				<input type="text" name="vnc_port" value="{$vnc.$vnc_port_id}" size="10"/>
				<input type="hidden" name="vnc_hash" value="{$vnc.$vnc_pwd_id}"/>
				</div>
			</td>
		</tr>
		<tr>
			<td class="highlight">Remote Desktop:</td>
			<td class="post_highlight">
				Port: 3389
			</td>
			<td nowrap="nowrap">
				<div id="div_plink_port_rdp" style="display:none;">
				Plink port: <input type="text" name="rdp_port" value="3389" size="10"/>
				</div>
			</td>
		</tr>
		<!--
		<tr>
			<td width="20%">VNC: </td>
			<td width="30%" nowrap="nowrap">
				<select name="vnc_opts" onchange="vnc_changed()">
					{foreach from=$vnc_info item=vnc}
						<option>(:{$vnc.$vnc_port_id}) Hash {$vnc.$vnc_pwd_id}</option>
					{/foreach}
				</select>
				Port used:
				<input type="text" name="vnc_port" value="{$vnc.$vnc_port_id}" size="10"/>
				<input type="hidden" name="vnc_hash" value="{$vnc.$vnc_pwd_id}"/>
			</td>
			<td>
				<a href="" onclick="document.forms['remote_access'].elements['vnc_port'].value='{$vnc.$vnc_port_id}'; return false;">[ Default ]</a>
			</td>
		</tr>
		<tr>
			<td>Remote Desktop:</td>
			<td>
				Port:
				<input type="text" name="rdp_port" value="3389" size="10"/>
			</td>
			<td>
				<a href="" onclick="document.forms['remote_access'].elements['rdp_port'].value='3389'; return false;">[ Default ]</a>
			</td>
		</tr>
		-->
		
		<tr class="head"><td colspan="3">Connect using</td></tr>
		<tr>
			<td>
				<input type="radio" name="connect_using" value="{$computer->remote_ip}" checked class="radio"
				{if !$computer->remote_ip}disabled{/if}
				> Remote IP:
			</td>
			<td colspan="2">{$computer->remote_ip}</td>
		</tr>
		
		{foreach from=$ips item=ip}
		<tr>
			<td><input type="radio" name="connect_using" value="{$ip.ip}" checked class="radio"> Local IP: </td>
			<td colspan="2">{$ip.ip} - {$ip.adapter}</td>
		</tr>
		{/foreach}
		
		<tr>
			<td><input type="radio" name="connect_using" value="other" checked class="radio"> Other IP: </td>
			<td colspan="2"><input type="text" name="other_ip" size="20" value=""></td>
		</tr>
		<tr>
			<td><input type="radio" name="connect_using" value="localhost" checked class="radio"> Localhost: </td>
			<td colspan="2"><input type="text" name="localhost_ip" size="20" value="127.0.0.1"></td>
		</tr>
	</table>
	<p/>
	{if $vnc_info}
	<input type="submit" name="connect_vnc" value="Connect with VNC"/>
	{/if}
	<input type="submit" name="connect_rdp" value="Connect with Remote Desktop"/>
{/if}
<p/>

{assign var="idx" value=0}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
var indexes_vnc = new Array (); 
var indexes_rdp = new Array ();

var idx_rdp = -1;
var idx_vnc = -1;
var cnt_vnc = 0;
var cnt_rdp = 0;
//]]>
</script>

{if $view=='simple'}
	<!-- Simple view -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="4">SSH Tunnel (Plink)</td>
		</tr>
		</thead>
		
		<tr>
			<td class="highlight" width="10%" rowspan="3">Services:</td>
			<td class="post_highlight" nowrap="nowrap" width="35%" rowspan="3">
			{foreach from=$computer_services item=computer_service}
					{assign var="service_id" value=$computer_service->id}
					<input type="checkbox" class="checkbox" name="plink[services][]"
						value="{$computer_service->id}" onclick = "plink_changed ();"
						{if $saved_plink->services.$service_id->selected}checked{/if}
					/>
					<input type="hidden" name="plink[ids_map][]" value="{$computer_service->id}" />
					
					{if !$computer_service->is_custom}
						{assign var="service_type" value=$computer_service->service_id}
						{$REMOTE_SERVICE_NAMES.$service_type}
						(:{$computer_service->port})
					{else}
						{$computer_service->name}
						{if $computer_service->is_web}
							<input type="hidden" name="web_url_{$idx}" value="{$computer_service->url}"/>
							<input type="hidden" name="web_protocol_{$idx}" 
								value="{if $computer_service->use_https}https{else}http{/if}"
							/>
							<a href="" id="web_service_link_{$idx}" style="display:none;"
							target="_blank" alt="{$computer_service->url}" title="{$computer_service->url}"
							>Browse &#0187;</a>
						{/if}
					{/if}
					
					{if $computer_service->service_id == $smarty.const.REMOTE_SERVICE_TYPE_TERMINALSRV}
						<script language="JavaScript" type="text/javascript">
							//<![CDATA[
							//var idx_rdp = {$idx};
							indexes_rdp[cnt_rdp++] = {$idx};
							//]]>
						</script>
					{elseif $computer_service->service_id == $smarty.const.REMOTE_SERVICE_TYPE_VNC}
						<script language="JavaScript" type="text/javascript">
							//<![CDATA[
							indexes_vnc[cnt_vnc++] = {$idx};
							//var idx_vnc = {$idx};
							//]]>
						</script>
					{/if}
					
					<select name="plink[computer_ip][]" onchange="plink_changed ();" style="display:none;">
						{foreach from=$ips item=ip}
							<option value="{$ip.ip}"
								{if $saved_plink->services.$service_id and $saved_plink->services.$service_id->computer_ip==$ip.ip}selected{/if}
							>{$ip.ip}</option>
						{/foreach}
					</select>
					
					<input type="hidden" name="plink[computer_port][]" 
						{if $saved_plink->services.$service_id}
							value="{$saved_plink->services.$service_id->computer_port}"
						{else}
							value="{$computer_service->port}" 
						{/if}
						onchange="plink_changed ();" onkeyup="plink_changed ();" 
					/>
					&nbsp;&nbsp;&nbsp;
				<!-- {$idx++} -->
				<br/>
			{/foreach}
			</td>
			
			<td class="highlight" width="15%"  style="height: 12px;">Computer IP:</td>
			<td class="post_highlight" style="height: 12px;">
				<select name="plink[global_computer_ip]" onchange="global_computer_ip_changed ();">
					{foreach from=$ips item=ip}
					<option value="{$ip.ip|trim}"
						{if $saved_plink->services.$service_id and $saved_plink->services.$service_id->computer_ip==$ip.ip}selected{/if}
					>{$ip.ip}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight" style="height: 12px;">Gateway:</td>
			<td class="post_highlight"  style="height: 12px;">
				<select name="plink_wan_list" onchange="wan_changed()">
				{foreach from=$remote_ips item=remote_ip}
					<option value="{$remote_ip->id}"
					{if $saved_plink->computer_id and $saved_plink->public_ip==$remote_ip->public_ip}selected{/if}
					>{$remote_ip->public_ip}:{$remote_ip->pf_port} ({$remote_ip->pf_login}/{$remote_ip->pf_password})</option>
				{/foreach}
				</select>
				<input type="hidden" name="plink[public_ip]" value="{$remote_ip->public_ip}" />
				<input type="hidden" name="plink[pf_port]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->pf_port}"
					{else} value="{$remote_ip->pf_port}"
					{/if} 
				/>
				<input type="hidden" name="plink[pf_login]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->pf_login}"
					{else} value="{$remote_ip->pf_login}"
					{/if} 
				/>
				<input type="hidden" name="plink[pf_password]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->pf_password}"
					{else} value="{$remote_ip->pf_password}"
					{/if} 
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight">Local port:</td>
			<td class="post_highlight">
				<input type="text" name="plink[local_port]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->local_port}"
					{else} value="{$plink_local_port}"
					{/if}
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight">Plink command:</td>
			<td class="post_highlight" colspan="3">
				<input type="text" name="plink[command_base]" size="120"
					{if $saved_plink->computer_id} value="{$saved_plink->command_base}"
					{else} value="C:\Program Files\PuTTY\plink.exe" 
					{/if}
				/>
				<br/>
				<textarea name="plink[command]" rows="3" cols="120" value=""></textarea>
			</td>
		</tr>
	</table>
{else}
	<!-- Advanced view -->
	<h2>SSH Tunnel (Plink)</h2>
	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="2" width="25%" nowrap="nowrap">
				Service 
			</td>
			<td width="25%">Computer IP</td>
			<td width="25%">Computer port</td>
			<td width="25%"> </td>
		</tr>
		</thead>
		
		{foreach from=$computer_services item=computer_service}
		<tr>
			<td width="1%">
				{assign var="service_id" value=$computer_service->id}
				<input type="checkbox" class="checkbox" name="plink[services][]"
					value="{$computer_service->id}" onclick = "plink_changed ();"
					{if $saved_plink->services.$service_id->selected}checked{/if}
				/>
				<input type="hidden" name="plink[ids_map][]" value="{$computer_service->id}" />
			</td>
			<td>
				{if !$computer_service->is_custom}
					{assign var="service_type" value=$computer_service->service_id}
					{$REMOTE_SERVICE_NAMES.$service_type}
				{else}
					{$computer_service->name}
					{if $computer_service->is_web}
						<input type="hidden" name="web_url_{$idx}" value="{$computer_service->url}"/>
						<input type="hidden" name="web_protocol_{$idx}" 
							value="{if $computer_service->use_https}https{else}http{/if}"
						/>
						<a href="" id="web_service_link_{$idx}" style="display:none;"
						target="_blank" alt="{$computer_service->url}" title="{$computer_service->url}"
						>Browse &#0187;</a>
					{/if}
				{/if}
				
				{if $computer_service->service_id == $smarty.const.REMOTE_SERVICE_TYPE_TERMINALSRV}
					<script language="JavaScript" type="text/javascript">
						//<![CDATA[
						//var idx_rdp = {$idx};
						indexes_rdp[cnt_rdp++] = {$idx};
						//]]>
					</script>
				{elseif $computer_service->service_id == $smarty.const.REMOTE_SERVICE_TYPE_VNC}
					<script language="JavaScript" type="text/javascript">
						//<![CDATA[
						//var idx_vnc = {$idx};
						indexes_vnc[cnt_vnc++] = {$idx};
						//]]>
					</script>
				{/if}
			</td>
			<td>
				<select name="plink[computer_ip][]" onchange="plink_changed ();">
					{foreach from=$ips item=ip}
						<option value="{$ip.ip}"
							{if $saved_plink->services.$service_id and $saved_plink->services.$service_id->computer_ip==$ip.ip}selected{/if}
						>{$ip.ip}</option>
					{/foreach}
				</select>
			</td>
			<td colspan="2">
				<input type="text" name="plink[computer_port][]" 
					{if $saved_plink->services.$service_id}
						value="{$saved_plink->services.$service_id->computer_port}"
					{else}
						value="{$computer_service->port}" 
					{/if}
					onchange="plink_changed ();" onkeyup="plink_changed ();" size="6"
				/>
				&nbsp;&nbsp;&nbsp;
				<a href="" onclick="document.forms['remote_access'].elements['plink[computer_port][]'][{$idx}].value='{$computer_service->port}'; return false;">[ Default ]</a>
			</td>
			<!-- {$idx++} -->
		</tr>
		{/foreach}
		
		<tr>
			<td colspan="5">
				<b>Connect via:</b>
				<select name="plink_wan_list" onchange="wan_changed()">
				{foreach from=$remote_ips item=remote_ip}
					<option value="{$remote_ip->id}"
					{if $saved_plink->computer_id and $saved_plink->public_ip==$remote_ip->public_ip}selected{/if}
					>{$remote_ip->public_ip}</option>
				{/foreach}
				</select>
				<input type="hidden" name="plink[public_ip]" value="{$remote_ip->public_ip}" />
			</td>
		</tr>
		
		<tr>
			<td> </td>
			<td>
				WAN port:<br/>
				<input type="text" name="plink[pf_port]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->pf_port}"
					{else} value="{$remote_ip->pf_port}"
					{/if} 
				/>
	
			</td>
			<td>
				GW user:<br/>
				<input type="text" name="plink[pf_login]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->pf_login}"
					{else} value="{$remote_ip->pf_login}"
					{/if} 
				/>
			</td>
			<td>
				GW password:<br/>
				<input type="text" name="plink[pf_password]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->pf_password}"
					{else} value="{$remote_ip->pf_password}"
					{/if} 
				/>
			</td>
			<td nowrap="nowrap">
				Local port:<br/>
				<input type="text" name="plink[local_port]" onchange="plink_changed ();" onkeyup="plink_changed ();"
					{if $saved_plink->computer_id} value="{$saved_plink->local_port}"
					{else} value="{$plink_local_port}"
					{/if}
				/>
				
				&nbsp;&nbsp;&nbsp;
				<a href="" onclick="wan_changed (); return false;">[ Default ]</a>
			</td>
		</tr>
		
		<tr>
			<td colspan="5"><b>Plink command:</b></td>
		</tr>
		<tr>
			<td> </td>
			<td colspan="4">
				<input type="text" name="plink[command_base]" size="140"
					{if $saved_plink->computer_id} value="{$saved_plink->command_base}"
					{else} value="C:\Program Files\PuTTY\plink.exe" 
					{/if}
				/>
				<br/>
				<textarea name="plink[command]" rows="3" cols="140" value=""></textarea>
			</td>
		</tr>
	</table>
{/if}
<p/>
<input type="submit" name="connect_plink" value="Make Plink tunnel" />
<input type="submit" name="save_plink" value="Save settings" />
<input type="submit" name="cancel" value="Close" />
</form>
<p/>

<script language="JavaScript">
//<![CDATA[

wan_public_ips = new Array ();
wan_pf_ports = new Array ();
wan_pf_logins = new Array ();
wan_pf_passwords = new Array ();
i=0;
{foreach from=$remote_ips item=remote_ip}
wan_public_ips[i] = "{$remote_ip->public_ip}";
wan_pf_ports[i] = "{$remote_ip->pf_port}";
wan_pf_logins[i] = "{$remote_ip->pf_login}";
wan_pf_passwords[i] = "{$remote_ip->pf_password}";
i++;
{/foreach}

var vnc_ports = new Array ();
var vnc_hashes = new Array ();
i=0;
{foreach from=$vnc_info item=vnc}
vnc_ports[i] = "{$vnc.$vnc_port_id}";
vnc_hashes[i] = "{$vnc.$vnc_pwd_id}";
i++;
{/foreach}

var view = "{$view}";


{literal}

// Called when a different VNC port/hash is selected, to update the hidden fields
function vnc_changed ()
{
	frm = document.forms['remote_access'];
	vnc_opts_list = frm.elements['vnc_opts'];
	
	if (vnc_opts_list.options.length > 0)
	{
		idx = vnc_opts_list.selectedIndex;
		frm.elements['vnc_port'].value = vnc_ports[idx];
		frm.elements['vnc_hash'].value = vnc_hashes[idx];
	}
}

// In simple view, this is called when another computer IP is selected from the drop-down.
// It will set all the computer IP drop-downs for each service (which are hidden in the simple view)
function global_computer_ip_changed ()
{
	frm = document.forms['remote_access'];
	computer_ips_list = frm.elements['plink[global_computer_ip]'];
	selected_ip_index = computer_ips_list.selectedIndex;
	
	services_list = frm.elements['plink[services][]'];
	if (!services_list) services_list = new Array ();
	else if (!services_list.length) services_list = new Array (services_list);
	services_count = services_list.length;
	if (services_list.length > 0)
	{
		for (i=0; i<services_list.length; i++)
		{
			if (services_list.length == 1)
			{
				frm.elements['plink[computer_ip][]'].options[selected_ip_index].selected = true;
			}
			else
			{
				frm.elements['plink[computer_ip][]'][i].options[selected_ip_index].selected = true;
			}
		}
	}
	
	plink_changed ();
}


function wan_changed ()
{
	frm = document.forms['remote_access'];
	wans_list = frm.elements['plink_wan_list'];
	sel_wan = wans_list.selectedIndex ;
	
	if (sel_wan >= 0)
	{
		frm.elements['plink[public_ip]'].value = wan_public_ips[sel_wan];
		frm.elements['plink[pf_port]'].value = wan_pf_ports[sel_wan];
		frm.elements['plink[pf_login]'].value = wan_pf_logins[sel_wan];
		frm.elements['plink[pf_password]'].value = wan_pf_passwords[sel_wan];
	}
	plink_changed ();
}

function is_vnc_element (idx)
{
	ret = false
	for (j=0; (j<indexes_vnc.length && !ret); j++) {ret = (indexes_vnc[j]==idx);}
	return ret;
}

function is_rdp_element (idx)
{
	ret = false
	for (j=0; (j<indexes_rdp.length && !ret); j++) {ret = (indexes_rdp[j]==idx);}
	return ret;
}


function plink_changed ()
{
	frm = document.forms['remote_access'];
	services_list = frm.elements['plink[services][]'];
	if (!services_list)
	{
		// There is no service
		services_list = new Array ();
	}
	else if (!services_list.length)
	{
		// There is a single service
		services_list = new Array (services_list);
	}
	
	plink_command = "-ssh -P " + frm.elements['plink[pf_port]'].value + " -a ";
	
	cnt = 0;
	base_port = parseInt (frm.elements['plink[local_port]'].value);
	
	// There are multiple services
	services_count = services_list.length;
	any_vnc_selected = false;
	any_rdp_selected = false;
	if (services_count > 0)
	{
		for (i=0; i<services_count; i++)
		{
			if (services_list[i].checked)
			{
				if (services_count == 1)
				{
					computer_ips_list = frm.elements['plink[computer_ip][]'];
					computer_ip = computer_ips_list.options[computer_ips_list.selectedIndex].value;
					computer_port = frm.elements['plink[computer_port][]'].value;
				}
				else
				{
					computer_ips_list = frm.elements['plink[computer_ip][]'][i];
					computer_ip = computer_ips_list.options[computer_ips_list.selectedIndex].value;
					computer_port = frm.elements['plink[computer_port][]'][i].value;
				}
			
				plink_command = plink_command + "-L " + (base_port+cnt) + ":" + computer_ip + ":" + computer_port + " ";
				
				if (is_vnc_element (i))
				{
					frm.elements['vnc_port'].value = (base_port+cnt);
					any_vnc_selected = true;
				}
				if (is_rdp_element (i))
				{
					frm.elements['rdp_port'].value = (base_port+cnt);
					any_rdp_selected = true;
				}
			
				// Check if it's a web service
				if (service_link = document.getElementById ('web_service_link_'+i))
				{
					if (view == 'simple') service_link.style.display = 'inline';
					else service_link.style.display = 'block';
					service_link.href = frm.elements['web_protocol_'+i].value + '://127.0.0.1:' + (base_port+cnt) + frm.elements['web_url_'+i].value;
				}
				
				cnt++;
			}
			else
			{
				// Check if it's a web service
				if (service_link = document.getElementById ('web_service_link_'+i))
				{
					service_link.style.display = 'none';
				}
			}
			
		}
		
		// If no VNC or RDP services are selected, restore the default ports
		elm_port = document.getElementById ('div_plink_port_vnc');
		if (any_vnc_selected) elm_port.style.display = '';
		else
		{
			vnc_changed ();
			elm_port.style.display = 'none';
		}
		elm_port = document.getElementById ('div_plink_port_rdp');
		if (any_rdp_selected) elm_port.style.display = '';
		else
		{
			frm.elements['rdp_port'].value = '3389';
			elm_port.style.display = 'none';
		}
		
		//div_plink_port_vnc
		
		plink_command = plink_command + frm.elements['plink[pf_login]'].value + "@" + frm.elements['plink[public_ip]'].value + " ";
		plink_command = plink_command + " -pw " + frm.elements['plink[pf_password]'].value + " sleep 300 ";
		
		
		frm.elements['plink[command]'].value = plink_command;
	}
}
{/literal}


vnc_changed ();

{if !$saved_plink->computer_id}
	wan_changed ();
{else}
	plink_changed ();
{/if}

{if $view=='simple'}
	// Make sure that the GW values are the ones from currently selected list, 
	// in case that what was saved is not in the list
	wan_changed ();
	// Make sure the same computer IP is selected for each service
	global_computer_ip_changed ();
{/if}

//]]>
</script>

<h2>Computer Passwords</h2>
{if $view=='simple'}
	<!-- Simple view -->
	<table width="70%" class="list">
		<thead>
		<tr>
			<td width="40%">Login / Password</td>
			<td width="60%">Comments</td>
		</tr>
		</thead>
		
		{foreach from=$computer_passwords item=password}
		<tr>
			<td>{$password->login|escape} / {$password->password|escape}</td>
			<td>{$password->comments|escape|nl2br}</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="2">[No passwords defined for this computer]</td>
		</tr>
		{/foreach}
	</table>
{else}
	<!-- Advanced view -->
	<table width="70%" class="list">
		<thead>
		<tr>
			<td width="50%">Login / Password | &nbsp;&nbsp;
            {assign var="p" value="computer_id:"|cat:$computer->id|cat:",customer_id:"|cat:$customer->id|cat:",ret:"|cat:"remote_access"}
			<a href="{'klara'|get_link:'computer_password_add':$p:'template'}">Add password &#0187;</a></td>
			<td width="30%">Comments</td>
			<td width="20%"> </td>
		</tr>
		</thead>
		
		{foreach from=$computer_passwords item=password}
		<tr>
			<td>
                {assign var="p" value="id:"|cat:$password->id|cat:",ret:"|cat:"remote_access"}
				<a href="{'klara'|get_link:'computer_password_edit':$p:'template'}"
				>{$password->login} / {$password->password}</a>
				{if $password->computer_id==0}[Network password]{/if}
			</td>
			<td>{$password->comments|escape|nl2br}</td>
			<td align="right" nowrap="nowrap">
                {assign var="p" value="id:"|cat:$password->id|cat:",ret:"|cat:"remote_access"}
				<a href="{'klara'|get_link:'computer_password_expire':$p:'template'}"
				>Expire&nbsp;&#0187;</a>
				&nbsp;&nbsp;|&nbsp;&nbsp;

                {assign var="p" value="id:"|cat:$password->id|cat:",ret:"|cat:"remote_access"}
				<a href="{'klara'|get_link:'computer_password_delete':$p:'template'}"
					onClick="return confirm('Are you really sure that you want to delete this password?');"
				>Delete&nbsp;&#0187;</a>
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="3">[No passwords defined for this computer]</td>
		</tr>
		{/foreach}
	</table>
	<p/>
	
	<h2>Expired Computer Passwords</h2>
	<table width="70%" class="list">
		<thead>
		<tr>
			<td width="50%">Login / Password</td>
			<td width="20%">Comments</td>
			<td width="20%">Expired</td>
			<td width="10%"> </td>
		</tr>
		</thead>
		
		{foreach from=$expired_computer_passwords item=password}
		{if $password->date_removed}
		<tr>
			<td>
                {assign var="p" value="id:"|cat:$password->id|cat:",ret:"|cat:"remote_access"}
				<a href="{'klara'|get_link:'computer_password_edit':$p:'template'}"
				>{$password->login} / {$password->password}</a>
				{if $password->computer_id==0}[Network password]{/if}
			</td>
			<td>{$password->comments|escape|nl2br}</td>
			<td>{$password->date_removed|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
			<td align="right">
                {assign var="p" value="id:"|cat:$password->id|cat:",ret:"|cat:"remote_access"}
				<a href="{'klara'|get_link:'computer_password_delete':$p:'temaplte'}"
					onClick="return confirm('Are you really sure that you want to delete this password?');"
				>Delete&nbsp;&#0187;</a>
			</td>
		</tr>
		{/if}
		{foreachelse}
		<tr>
			<td colspan="4">[No expired passwords defined for this computer]</td>
		</tr>
		{/foreach}
	</table>
{/if}
<p/>