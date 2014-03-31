<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}

// Set the window size
window.resizeTo (800, 700);

function showD (oid_id)
{
	document.getElementById('d_'+oid_id).style.display='block';
}

function hideD (oid_id)
{
	document.getElementById('d_'+oid_id).style.display='none';
}

function sel (oid, oid_id, oid_name)
{
	if (window.opener && !window.opener.closed)
	{
		// Send data back to the caller window
		parent_wind = window.opener;
		parent_wind.setOid (oid, oid_id, oid_name);
	}
	window.close ();
}


{/literal}
//]]>
</script>

<div style="disply:block; padding: 10px;">

<h1 style="margin-top:0px;">Select OID</h1>
<p class="error">{$error_msg}</p>

<form action="" method="GET" name="frm_t">
{$form_redir}

<table width="100%">
	<tr>
		<td width="140">
			<b>MIB:</b>
			<select name="mib_id" onchange="document.forms['frm_t'].submit()">
				<option value="">[Select MIB]</option>
				{html_options options=$mibs_list selected=$selected_mib->id}
			</select>
		</td>
		<td>
			{if $selected_mib->id}
				Place the mouse over the OIDs to see more details (where available).
				Click on an OID link to select it.
			{/if}
		</td>
	</tr>
</table>
<p/>
</form>


{if $selected_mib->id}
	<table class="list" width="100%">
		<thead>
		<tr>
			<td>OID</td>
			<td width="120">Name</td>
		</tr>
		</thead>
		
		{foreach from=$oids item=oid}
		<tr class="row_hover">
			<td nowrap="nowrap">
				<a name="a{$oid->id}"></a>
				{if $oid->description}<div id="d_{$oid->id}" class="snmp_descr">
					<b>{$oid->oid} - {$oid->name|escape}</b><p/>
					{$oid->description|escape|nl2br}
					{if count($oid->vals) > 0}
					<p/><b>Values:</b>{foreach from=$oid->vals key=k item=v name=vals}{$k}:{$v}{if !$smarty.foreach.vals.last}, {/if}{/foreach}
					{/if}
				</div>{/if}
				<img class="snmp_icon" style="margin-left:{$oid->level}em;" {if $oid->node_type==$smarty.const.SNMP_NODE_NONE}src="/images/icons/folder.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_SCALAR}src="/images/icons/leaf.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_TABLE_COL}src="/images/icons/leaf.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_TABLE}src="/images/icons/table.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_TABLE_ROW}src="/images/icons/props.png" {/if}/>&nbsp;<a 
				href="#" onclick="return sel('{$oid->oid}', {$oid->id}, '{$oid->name}');"
				{if $oid->description}onmouseover="showD({$oid->id})" onmouseout="hideD({$oid->id})"{/if}
				>.{$oid->oid}</a>
				
			</td>
			<td nowrap="nowrap" style="padding-left:{$oid->level}em;">{$oid->name|escape}</td>
		</tr>
		{/foreach}
	
	</table>
	<p/>
{/if}

</div>