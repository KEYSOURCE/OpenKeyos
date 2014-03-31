{assign var="paging_titles" value="KRIFS, Ticket, Add Object"}
{assign var="ticket_id" value=$ticket->id}
{assign var="paging_urls" value="/krifs, /krifs/ticket_edit/"|cat:$ticket_id}
{include file="paging.html"}

<h1>Add Object</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

Please select from the list below the object or objects that you want to 
attach to this ticket.
<p>

<form action="" method="POST" id="form_obj">
{$form_redir}

<table class="list">
	<thead>
	<tr>
		<td width="10"> </td>
		{foreach from=$headers_list item=header_name}
		<td>{$header_name}</td>
		{/foreach}
	</tr>
	</thead>

	{foreach from=$objects_list item=object_fields key=object_id}
	<tr>
		<td><input type="checkbox" class="checkbox" name="object_ids[]" value="{$object_id}"></td>
		{foreach from=$object_fields item=field_value}
		<td>{$field_value}</td>
		{/foreach}
	</tr>
	{/foreach}
</table>
<p>

<input type="submit" name="save" value="Add">
<input type="submit" name="cancel" value="Cancel" onclick="javascript: top.$.fancybox.close();">

</form>

{literal}
<script type="text/javascript" src="/javascript/jquery.js"></script>
<script type="text/javascript">
    $('#form_obj').submit(function () {
        var data = unescape($(this).serialize());
        data = data + '&save=true';
        $.post('/krifs/ticket_edit', data, function(data) {
            top.location.href = top.location.href;
            top.$.fancybox.close();
        });
        return false;
    })
</script>
{/literal}