{assign var="peripheral_id" value=$peripheral->id}
{assign var="paging_titles" value="KAWACS, Manage Peripherals, Edit Peripheral, Peripheral Remote Access"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripherals, /?cl=kawacs&peripheral_edit&id=$peripheral_id"}
{include file="paging.html"}

<h1>Peripheral Remote Access: {$peripheral->name}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="remote_access">
{$form_redir}

{assign var="idx" value=0}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
var idx_rdp = -1;
var idx_vnc = -1;
//]]>
</script>

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2" width="25%" nowrap="nowrap">
			Connect to | &nbsp;&nbsp;
			<a href="/?cl=klara&op=manage_access&customer_id={$peripheral->customer_id}">KLARA Information &#0187;</a>
		</td>
		<td width="25%">Peripheral IP</td>
		<td width="25%">Peripheral port</td>
		<td width="25%"> </td>
	</tr>
	</thead>
	
	
	<tr>
		<td width="1%">
			<input type="checkbox" class="checkbox" name="services[1][selected]" value="1" onclick="plink_changed ();" 
				{if $saved_plink->services.1->selected}checked{/if}
			/>
		</td>
		<td>Network</td>
		<td>
			<input type="text" name="services[1][peripheral_ip]" onchange="plink_changed ();"
				{if $saved_plink->services.1 and $saved_plink->services.1->peripheral_ip} value="{$saved_plink->services.1->peripheral_ip}"
				{else} value="{$peripheral->get_net_access_ip()}"
				{/if}
			/>
		</td>
		<td colspan="2">
			<input type="text" name="services[1][peripheral_port]" onchange="plink_changed ();" size="5"
				{if $saved_plink->services.1 and $saved_plink->services.1->peripheral_port} value="{$saved_plink->services.1->peripheral_port}"
				{else} value="{$peripheral->get_net_access_port()}"
				{/if}
			/>
			&nbsp;&nbsp;&nbsp;
			<a href="" onclick="frm=document.forms['remote_access']; frm.elements['services[1][peripheral_ip]'].value='{$peripheral->get_net_access_ip()}';  frm.elements['services[1][peripheral_port]'].value='{$peripheral->get_net_access_port()}'; return false;">[ Default ]</a>
		</td>
	</tr>
	
	
	<tr>
		<td width="1%">
			<input type="checkbox" class="checkbox" name="services[2][selected]" value="1" onclick="plink_changed ();" 
				{if $peripheral->get_access_url()}
					{if $saved_plink->services.2->selected}checked{/if}
				{else}
					disabled
				{/if}
			/>
		</td>
		<td>
			Web
			{if $peripheral->get_access_url()}
				<a href="{$peripheral->get_access_url()}" target="_blank" id="web_link" style="display:none;"
					alt="{$peripheral->get_access_url()}" title="{$peripheral->get_access_url()}"
				>Browse &#0187;</a>
			{else}
				<div class="light_text">[No URL specified]</div>
			{/if}
		</td>
		<td>
			<input type="text" name="services[2][peripheral_ip]" onchange="plink_changed ();"
				{if $saved_plink->services.2 and $saved_plink->services.2->peripheral_ip} value="{$saved_plink->services.2->peripheral_ip}"
				{else} value="{$peripheral->get_net_access_ip()}"
				{/if}
			/>
		</td>
		<td colspan="2">
			<input type="text" name="services[2][peripheral_port]" onchange="plink_changed ();" size="5"
				{if $saved_plink->services.2 and $saved_plink->services.2->peripheral_port} value="{$saved_plink->services.2->peripheral_port}"
				{else} value="80"
				{/if}
			/>
			&nbsp;&nbsp;&nbsp;
			<a href="" onclick="frm=document.forms['remote_access']; frm.elements['services[2][peripheral_ip]'].value='{$peripheral->get_net_access_ip()}';  frm.elements['services[2][peripheral_port]'].value='80'; return false;">[ Default ]</a>
		</td>
	</tr>
	
	
	<tr>
		<td colspan="5">
			<b>Connect via:</b>
			<select name="plink_wan_list" onchange="wan_changed()">
			{foreach from=$remote_ips item=remote_ip}
				<option value="{$remote_ip->id}"
				{if $saved_plink->peripheral_id and $saved_plink->public_ip==$remote_ip->public_ip}selected{/if}
				>{$remote_ip->public_ip}</option>
			{/foreach}
			</select>
			<input type="hidden" name="plink[public_ip]" 
				{if $saved_plink->peripheral_id and $saved_plink->public_ip} value="{$saved_plink->public_ip}"
				{else} value="{$remote_ip->public_ip}"
				{/if}
			/>
		</td>
	</tr>
	
	<tr>
		<td> </td>
		<td>
			WAN port:<br/>
			<input type="text" name="plink[pf_port]" onchange="plink_changed ();" onkeyup="plink_changed ();"
				{if $saved_plink->peripheral_id} value="{$saved_plink->pf_port}"
				{else} value="{$remote_ip->pf_port}"
				{/if} 
			/>

		</td>
		<td>
			GW user:<br/>
			<input type="text" name="plink[pf_login]" onchange="plink_changed ();" onkeyup="plink_changed ();"
				{if $saved_plink->peripheral_id} value="{$saved_plink->pf_login}"
				{else} value="{$remote_ip->pf_login}"
				{/if} 
			/>
		</td>
		<td>
			GW password:<br/>
			<input type="text" name="plink[pf_password]" onchange="plink_changed ();" onkeyup="plink_changed ();"
				{if $saved_plink->peripheral_id} value="{$saved_plink->pf_password}"
				{else} value="{$remote_ip->pf_password}"
				{/if} 
			/>
		</td>
		<td nowrap="nowrap">
			Local port:<br/>
			<input type="text" name="plink[local_port]" onchange="plink_changed ();" onkeyup="plink_changed ();"
				{if $saved_plink->peripheral_id} value="{$saved_plink->local_port}"
				{else} value="{$plink_local_port}"
				{/if}
			/>
			
			&nbsp;&nbsp;&nbsp;
			<a href="" onclick="document.forms['remote_access'].elements['plink[local_port]'].value='{$plink_local_port}'; wan_changed (); return false;">[ Default ]</a>
		</td>
	</tr>
	
	<tr>
		<td colspan="5"><b>Plink command:</b></td>
	</tr>
	<tr>
		<td> </td>
		<td colspan="4">
			<input type="text" name="plink[command_base]" size="140"
				{if $saved_plink->peripheral_id} value="{$saved_plink->command_base}"
				{else} value="C:\Program Files\PuTTY\plink.exe" 
				{/if}
			/>
			
			<textarea name="plink[command]" rows="3" cols="140" value=""></textarea>
		</td>
	</tr>
</table>
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

var web_protocol = "{$peripheral->get_access_url_protocol()}";
var web_base = "{$peripheral->get_access_url_base()}";

{literal}
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

function plink_changed ()
{
	frm = document.forms['remote_access'];
	
	plink_command = "-ssh -P " + frm.elements['plink[pf_port]'].value + " -a ";
	
	cnt = 0;
	base_port = parseInt (frm.elements['plink[local_port]'].value);
	
	services_count = 3;
	for (i=1; i<services_count; i++)
	{
		if (frm.elements['services['+i+'][selected]'].checked)
		{
			peripheral_ip = frm.elements['services['+i+'][peripheral_ip]'].value;
			peripheral_port = frm.elements['services['+i+'][peripheral_port]'].value;
		
			plink_command = plink_command + "-L " + (base_port+cnt) + ":" + peripheral_ip + ":" + peripheral_port + " ";
			
			// Check if it's a web service
			if (i==2)
			{
				service_link = document.getElementById ('web_link')
				if (service_link)
				{
					service_link.style.display = 'block';
					service_link.href = web_protocol + '://127.0.0.1:' + (base_port+cnt) + web_base;
				}
			}
			cnt++;
		}
		else
		{
			// Check if it's a web service
			if (i==2)
			{
				service_link = document.getElementById ('web_link')
				if (service_link)
				{
					service_link.style.display = 'none';
				}
			}
		}
	}
	
	plink_command = plink_command + frm.elements['plink[pf_login]'].value + "@" + frm.elements['plink[public_ip]'].value + " ";
	if (frm.elements['plink[pf_password]'].value != '')
	{
		plink_command = plink_command + "-pw " + frm.elements['plink[pf_password]'].value + " "
	}
	plink_command = plink_command + "sleep 60 ";
	
	frm.elements['plink[command]'].value = plink_command;
}
{/literal}


{if !$saved_plink->peripheral_id}
	wan_changed ();
{else}
	plink_changed ();
{/if}

//]]>
</script>
