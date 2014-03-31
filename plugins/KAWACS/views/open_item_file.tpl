{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, View File Content "}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer_id}
{assign var="computer_view_link" value="kawacs"|get_link:$p:'template'}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>View File Content </h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<a href="{$computer_view_link}}">&#0171; Back to computer</a>

<p>

<pre>
{$file_contents|escape}
</pre>
