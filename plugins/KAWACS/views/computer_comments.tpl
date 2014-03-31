{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Edit Computer Comments"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:"kawacs"|get_link:"computer_view":$p:"template"}
{include file="paging.html"}


<h1>Edit Computer Comments</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

Enter comments:
<p>

<textarea rows=10 cols=60 name="comments">{$computer->comments}</textarea>

<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
</form>
