{assign var="paging_titles" value="KLARA, Access Information, Computer Remote Services"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access"}
{include file="paging.html"}

<h1>Computer Remote Services</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="edit_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td>Customer:</td>
		<td class="post_highlight">{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%" class="highlight"><b>Computer:</b></td>
		<td class="post_highlight">
			<b>#{$computer->id}: {$computer->netbios_name|escape}</b>
		</td>
	</tr>
	<tr>
		<td class="highlight">Services:</td>
		<td class="post_highlight">
			<table class="no_borders">
				{assign var="cnt" value=0}
				{foreach from=$REMOTE_SERVICE_NAMES key=srv_id item=srv_name}
				{assign var="srv_port" value=$REMOTE_SERVICES_PORTS.$srv_id}
						
					{* Show first services of this type which are already defined *}
					{foreach from=$services.$srv_id item=service name=comp_services}
					<input type="hidden" name="services[{$cnt}][service_id]" value="{$srv_id}"/>
					<tr>
						<td nowrap="nowrap">
							<input type="checkbox" name="services[{$cnt}][selected]" value="1" class="checkbox" checked/>
							{$srv_name|escape}
							<input type="hidden" name="services[{$cnt}][id]" value="{$service->id}"/>
						</td>
						<td nowrap="nowrap">
							:&nbsp;
							<input type="text" size="6" name="services[{$cnt++}][port]" value="{$service->port}"/>
						</td>
						<td nowrap="nowrap">
							{if $smarty.foreach.comp_services.last}
							&nbsp;&nbsp;
							<a href="" onclick="return show_new_srv({$srv_id});" id="link_srv_{$srv_id}">Add new &#0187;</a>
							{/if}
						</td>
					</tr>
					{/foreach}
					
					<input type="hidden" name="services[{$cnt}][service_id]" value="{$srv_id}"/>
					<tr id="srv_new_{$srv_id}"
						{if $services.$srv_id} style="display:none;" {/if}
					>
						<td nowrap="nowrap">
							<input type="checkbox" name="services[{$cnt}][selected]" value="1" class="checkbox"/>
							{$srv_name|escape}
						</td>
						<td nowrap="nowrap">
							:&nbsp;
							<input type="text" size="6" name="services[{$cnt++}][port]" value="{$srv_port}"/>
						</td>
					</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>
<p/>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}

function show_new_srv(service_id)
{
	elm = document.getElementById ('srv_new_' + service_id);
	elm.style.display = '';
	
	elm = document.getElementById ('link_srv_' + service_id);
	elm.style.display = 'none';
	
	return false;
}

{/literal}
//]]>
</script>

<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Close"/>
</form>
