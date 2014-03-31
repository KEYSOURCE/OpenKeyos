{assign var="paging_titles" value="KLARA, Access Information, Add Computer Remote Service"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access"}
{include file="paging.html"}

<script language="JavaScript">

var ports = new Array ();
{foreach from=$REMOTE_SERVICES_PORTS key=service_id item=service_port}
	ports[{$service_id}] = {$service_port};
{/foreach}

{literal}
function service_changed ()
{
	frm = document.forms['add_frm'];
	elm = frm.elements['computer_remote_service[service_id]'];
	new_id = elm.options[elm.selectedIndex].value;
	
	if (new_id != '')
	{
		if (new_id != -1)
		{
			frm.elements['computer_remote_service[port]'].value = ports[new_id];
			elm = document.getElementById ('custom_row');
			elm.style.display = 'none';
		}
		else
		{
			elm = document.getElementById ('custom_row');
			elm.style.display = '';
			
			elm_url = document.getElementById ('custom_url_row');
			elm_https = document.getElementById ('custom_https_row');
			if (frm.elements['computer_remote_service[is_web]'].checked)
			{
				elm_url.style.display = '';
				elm_https.style.display = '';
			}
			else
			{
				elm_url.style.display = 'none';
				elm_https.style.display = 'none';
			}
		}
	}
}
{/literal}

</script>


<h1>Add Computer Remote Service</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td>Customer:</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Computer:</td>
		<td>
			<select name="computer_remote_service[computer_id]">
				<option value="">[Select computer]</option>
				{html_options options=$computers_list selected=$computer_remote_service->computer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Service:</td>
		<td>
			<select name="computer_remote_service[service_id]" onChange="service_changed();">
				<option value="">[Select service]</option>
				{html_options options=$REMOTE_SERVICE_NAMES selected=$computer_remote_service->service_id}
				<option value="-1" {if $computer_remote_service->is_custom}selected{/if}>[Custom]</option>
			</select>
		</td>
	</tr>
	<tr id="custom_row" style="display:none;">
		<td>Custom service:</td>
		<td>
			<table width="100%">
				<tr>
					<td width="15%">Name:</td>
					<td>
						<input type="text" name="computer_remote_service[name]" value="{$computer_remote_service->name}" />
						&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td>Web:</td>
					<td>
						<input type="checkbox" class="checkbox" name="computer_remote_service[is_web]" value="1"
							{if $computer_remote_service->is_web}checked{/if}
							onclick="service_changed();"
						/>
					</td>
				</tr>
				<tr id="custom_url_row">
					<td>URL:</td>
					<td>
						<input type="text" name="computer_remote_service[url]" value="{$computer_remote_service->url}" size="60" />
					</td>
				</tr>
				<tr id="custom_https_row">
					<td>Use HTTPS:</td>
					<td>
						<input type="checkbox" class="checkbox" name="computer_remote_service[use_https]" 
							{if $computer_remote_service->use_https}checked{/if}
						/>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>Port number:</td>
		<td>
			<input type="text" name="computer_remote_service[port]" value="{$computer_remote_service->port}" size="10"/>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="computer_remote_service[comments]" rows="4" cols="60">{$computer_remote_service->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
service_changed ();
//]]>
</script>