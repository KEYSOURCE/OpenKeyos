{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Merge Finished"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Merge Finished</h1>

<p class="error">{$error_msg}</p>

<p>
The data has been succesfully merged into the computer <b>{$computer->netbios_name|escape} (# {$computer->id})</b>.
</p>

<a href="/?cl=kawacs&op=computer_view&id={$computer->id}">Return to computer &#0187;</a>