{assign var="paging_titles" value="Customers, Manage Customers, Nagvis Data"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer"}
{include file="paging.html"}

<h1>Nagvis data</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

<div id="nagvis-form">
    {if $nagvis->id}
    <input type="hidden" name="nagvis[id]" value="{$nagvis->id}" />
    {/if}
    <table cellpadding="0" cellspacing="0" class="list">
        <tr class="head">
            <td colspan="2">Nagvis account</td>
        </tr>
        <tr>
            <td style="text-align: right;">username</td>
            <td><input type="text" name="nagvis[username]" value="{$nagvis->username}" style="width: 300px;" /></td>
        </tr>
        <tr>
            <td style="text-align: right;">password</td>
            <td><input type="text" name="nagvis[password]" value="{$nagvis->password}" style="width: 300px;" /></td>
        </tr>
        <tr>
            <td style="text-align: right;">url</td>
            <td>
                <select name="nagvis[protocol]">
                    <option value="http://" {if $nagvis->protocol == 'http://'}selected{/if}>http://</option>
                    <option value="https://" {if $nagvis->protocol == 'https://'}selected{/if}>https://</option>
                </select>
                <input type="text" name="nagvis[url]" value="{$nagvis->url}" style="width: 237px;" />
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right;">
                <input type="submit" name="save" value="Save" />
                <input type="submit" name="cancel" value="Close">
            </td>
        </tr>
    </table>
</div>

</form>
