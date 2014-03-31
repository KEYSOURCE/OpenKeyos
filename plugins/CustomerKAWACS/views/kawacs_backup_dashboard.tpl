{assign var="paging_titles" value="Technical Support"}
{include file="paging.html"}

<h1>Computers backup statuses dashboard</h1>
<p>
<font class="error">{$error_msg}</font>
</p>
<script language="JavaScript" type="text/javascript" src="javascript/ajax.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
	var groups_stat = new Array(true, true, true, true);

	function expand_group(id)
	{
		var stat = groups_stat[id];
		groups_stat[id] = (!groups_stat[id]);
		var group = document.getElementById('group_'+id);
		
		img = document.getElementById('img_'+id);
		if (stat)
		{
			img.src = '/images/expand.gif';
		}
		else
		{
			img.src = '/images/collapse.gif';
		}
		
		if(stat)
		{
			group.style.display = 'none';
		}
		else
		{
			group.style.display = 'block';
		}
	}

	function mouseOverReportsLabel(label)
	{
		var label_var = document.getElementById(label);
		label_var.style.fontStyle = 'italic';
	}
	
	function mouseOutReportsLabel(label)
	{
		var label_var = document.getElementById(label);
		label_var.style.fontStyle = 'normal';
	}
	function changeBackupImage(im_holder,title, legend, pr, po, pg, pgr)
	{
		//alert(title);
		{/literal}
		generateImage(im_holder, title,legend,pr, po, pg, pgr );
		{literal}
	}
	
{/literal}
//>]]
</script>


<form name='filter' method="POST">
{$form_redir}
<table width="98%" class="list">
	<tr class="head">
		<td style="width: 200px;">Profile</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<select name="filter[profile_id]">
				<option value="0">[All]</option>
				{html_options options=$profiles selected=$filter.profile_id}
			</select>
		</td>
		<td align="right" style="vertical-align: middle">
			<input type="hidden" name="do_filter_hidden" value="0">
			<input type="submit" name="do_filter" value="Apply filter">
		</td>
	</tr>
	
</table>

<div id="dhtmlgoodies_xpPane">
{if $count_r!=0}
<div onclick="expand_group(0)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: red;"><img src="/images/collapse.gif" width="10" height="11" id="img_0"></td>
		<td>Computers reporting backup error	   [{$count_r} computers]</td>
	</tr>
	</table>
</div>
<div class="dhtmlgoodies_panel" id='group_0'>
<div>
<table class="list" width="98%">
<tr class='head'>
	<td style="width: 10%;">ID</td>
	<td style="width: 40%;">Name</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Status</td>
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_red item=computerr}
<tr>
		{assign var=stats value=$computerr->backup_status()}
		{assign var=citem value=$stats[0]}
		{assign var=status value=$citem->get_specific_value('Status')}
		{assign var=message value=$citem->get_specific_value('Message')}
		{assign var=td value='<td style="color: red;">'}
		{$td}{$computerr->id}</td>
		{$td}<b>{$computerr->netbios_name}</b></td>
		<td>{assign var=pid value=$computerr->profile_id}{$profiles[$pid]}</td>
		{$td}<label>{$status}</label></td>
	</tr>
{/foreach}
</table>
</div>
</div>
{/if}
{if $count_o!=0}
<div onclick="expand_group(1)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: orange;"><img src="/images/collapse.gif" width="10" height="11" id="img_1"></td>
		<td>Computers reporting tape related backup error	   [{$count_o} computers]</td>
	</tr>
	</table>
</div>
<div class="dhtmlgoodies_panel" id='group_1'>
<div>
<table class="list" width="98%">
<tr class='head'>
	<td style="width: 10%;">ID</td>
	<td style="width: 40%;">Name</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Status</td>
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_orange item=computero}
<tr>
		{assign var=stats value=$computero->backup_status()}
		{assign var=citem value=$stats[0]}
		{assign var=status value=$citem->get_specific_value('Status')}
		{assign var=message value=$citem->get_specific_value('Message')}
		{assign var=td value='<td style="color: orange;">'}
		{$td}{$computero->id}</td>
		{$td}<b>{$computero->netbios_name}</b></td>
		<td>{assign var=pid value=$computero->profile_id}{$profiles[$pid]}</td>
		{$td}<label>{$status}</label></td>
	</tr>
{/foreach}
</table>
</div>
</div>
{/if}
{if $count_g != 0}
<div onclick="expand_group(2)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: green;"><img src="/images/collapse.gif" width="10" height="11" id="img_2"></td>
		<td>Computers reporting backup success	   [{$count_g} computers]</td>
	</tr>
	</table>
</div>
<div class="dhtmlgoodies_panel" id='group_2'>
<div>
<table class="list" width="98%">
<tr class='head'>
	<td style="width: 10%;">ID</td>
	<td style="width: 40%;">Name</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Status</td>
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$computers_green item=computerv}
<tr>
		{assign var=stats value=$computerv->backup_status()}
		{assign var=citem value=$stats[0]}
		{assign var=status value=$citem->get_specific_value('Status')}
		{assign var=message value=$citem->get_specific_value('Message')}
		{assign var=td value='<td style="color: green;">'}
		{$td}{$computerv->id}</td>
		{$td}<b>{$computerv->netbios_name}</b></td>
		<td>{assign var=pid value=$computerv->profile_id}{$profiles[$pid]}</td>
		{$td}<label>{$status}</label><br /></td>
	</tr>
{/foreach}
</table>
</div>
</div>
{/if}
{if $count_gr!=0}
<div onclick="expand_group(3)" style="cursor: hand;">
	<table class="list" width="98%">
	<tr class='head'>
		<td style="width: 10px; background-color: gray;"><img src="/images/collapse.gif" width="10" height="11" id="img_3"></td>
		<td>Computers reporting backup error	   [{$count_gr} computers]</td>
	</tr>
	</table>
</div>
<div class="dhtmlgoodies_panel" id='group_3'>
	<div>
		<table class="list" width="98%">
			<tr class='head'>
				<td style="width: 10%;">ID</td>
				<td style="width: 40%;">Name</td>
				<td style="width: 20%;">Profile</td>
				<td style="width: 30%;">Status</td>
			</tr>
			{foreach from=$computers_grey item=computerg}
			<tr>
					{assign var=stats value=$computerg->backup_status()}
					{assign var=citem value=$stats[0]}
					{assign var=status value=$citem->get_specific_value('Status')}
					{assign var=message value=$citem->get_specific_value('Message')}
					{assign var=td value='<td style="color: gray;">'}
					{$td}{$computerg->id}</td>
					{$td}<b>{$computerg->netbios_name}</b></td>
					<td>{assign var=pid value=$computerg->profile_id}{$profiles[$pid]}</td>
					{$td}<label>{if $status!=""}{$status}{else} no status {/if}</label></td>
				</tr>
			{/foreach}
		</table>
	</div>
</div>
{/if}
{if $count_r==0 && $count_o==0 && $count_g==0 && $count_gr==0}
<p>	
[There aren't any computers with backup reporting in the profile]
</p>
{/if}
{if $count_r!=0 or $count_o!=0 or $count_g!=0 or $count_gr!=0}
<!-- <div>
<h2>Overall backup status</h2>
<p>The overall backup status of your computers: </p>
<table class="list" width="100%">
	 <tr>
		<td>
		<img name="_backup" id="_backup" src="" width="600" height="400">
		<script language="JavaScript" type="text/javascript">
		//<![CDATA
		setTimeout("changeBackupImage('_backup','Overall backup status', {literal}new Array('Backup error', 'Tape error', 'Success', 'Not reporting'){/literal}, {$perc_red}, {$perc_orange}, {$perc_green}, {$perc_grey})", 0);
		//]>
		</script>
		</td>
	 </tr>
</table>
</div>
{/if}
-->
</div>
</form> 

