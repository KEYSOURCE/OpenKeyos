{assign var="paging_titles" value="KAWACS, MIBs Management, Edit MIB"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=snmp&op=manage_mibs"}
{include file="paging.html"}

<h1>Edit MIB: {$mib->name|escape}</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="120">File name:</td>
		<td class="post_highlight">
			{$mib->orig_fname|escape}
			&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
			<a href="/?cl=snmp&amp;op=mib_download&amp;id={$mib->id}">Download</a>
			&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
			<a href="/?cl=snmp&amp;op=mib_upload_file&amp;id={$mib->id}">Upload new file &#0187;</a>
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Uploaded:</td>
		<td class="post_highlight">{$mib->date_imported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	<tr>
		<td class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="mib[name]" value="{$mib->name|escape}" size="40" />
	</tr>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight"><textarea name="mib[comments]" rows="8" cols="100">{$mib->comments|escape}</textarea></td>
	</tr>
</table>
<p/>
<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
<p/>

<h2>OIDs</h2>
Place the mouse over the OIDs to see more details (where available).<p/>
<a href="/?cl=snmp&amp;op=manage_mibs">&#0171; Back to MIBs</a><p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="300">OID</td>
		<td width="150">Name</td>
		<td width="30">Vals.</td>
		<td>Type</td>
	</tr>
	</thead>
	
	{foreach from=$oids item=oid}
	<tr class="row_hover">
		<td nowrap="nowrap">
			<img class="snmp_icon" style="margin-left:{$oid->level}em;"
{if $oid->node_type==$smarty.const.SNMP_NODE_NONE}src="/images/icons/folder.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_SCALAR}src="/images/icons/leaf.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_TABLE_COL}src="/images/icons/leaf.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_TABLE}src="/images/icons/table.png"
{elseif $oid->node_type==$smarty.const.SNMP_NODE_TABLE_ROW}src="/images/icons/props.png"
{/if}/>
			{if $oid->description}<div id="d_{$oid->id}" class="snmp_descr">
				<b>{$oid->oid} - {$oid->name|escape}</b><p/>
				{$oid->description|escape|nl2br}
				{if count($oid->vals) > 0}
				<p/><b>Values:</b>{foreach from=$oid->vals key=k item=v name=vals}{$k}:{$v}{if !$smarty.foreach.vals.last}, {/if}{/foreach}
				{/if}
			</div>{/if}
			<a href="#{$oid->id}" onclick="return false;"
			{if $oid->description}onmouseover="document.getElementById('d_{$oid->id}').style.display='block';" onmouseout="document.getElementById('d_{$oid->id}').style.display='none';"{/if}
			>.{$oid->oid}</a>
		</td>
		<td nowrap="nowrap" style="padding-left:{$oid->level}em;">{$oid->name|escape}</td>
		<td align="center">{if count($oid->vals)>0}Yes{else}-{/if}</td>
		<td>{if $oid->data_type}{assign var="data_type" value=$oid->data_type}{$SNMP_TYPES.$data_type}{/if}</td>
	</tr>
	{/foreach}

</table>
<p/>

<a href="/?cl=snmp&amp;op=manage_mibs">&#0171; Back to MIBs</a><p/>