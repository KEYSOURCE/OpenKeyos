{assign var="paging_titles" value="KAWACS, Manage computers, Set LogMeIn"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_computers"}
{include file="paging.html"}

<h1>Set LogMeIn ID</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}
<input type="hidden" name="logmein[id]" value="{$logmein->id}" />

<table class="list">
    <tr class="head">
        <td>LogMeIn Computer id</td>
        <td><input type="text" name="logmein[logmein_id]" value="{$logmein->logmein_id}"></td>
    </tr>
    <tr class="head">
        <td colspan="2" style="text-align: right">
            <input type="submit" name="save" value="Save"/>
            <input type="submit" name="close" value="Close"/>
        </td>
    </tr>
</table>
</form>
