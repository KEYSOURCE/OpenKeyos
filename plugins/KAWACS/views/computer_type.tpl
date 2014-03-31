{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Edit Computer Type"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Edit Computer Type</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

Please specify the type for this computer:
<p>

{foreach from=$COMP_TYPE_NAMES item=type_name key=type_id}
	<input type="radio" name="type" value="{$type_id}" {if $type_id==$computer->type}checked{/if}>
	{$type_name} 
	{if $type_id} (ID: {$type_id}) {/if}
	<p>
{/foreach}

<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
