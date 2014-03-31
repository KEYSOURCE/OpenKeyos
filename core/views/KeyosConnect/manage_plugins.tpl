{include file="paging.html"}

<script type="text/javascript">
    {literal}
//<![CDATA[
    function activate_plugin(plugin_key, plugin_status){
        $("<input type='hidden' name='plugin_key' id='plugin_key' value='" + plugin_key + "' />").appendTo($('#hidden_elements'));
        $("<input type='hidden' name='plugin_status' id='plugin_status' value='" + plugin_status + "' />").appendTo($('#hidden_elements'));
        $("#frm_plugins").submit();
    }
//]]>
    {/literal}
</script>

<style>
    {literal}
    th{text-align: left; background-color: #eee;}
    {/literal}
</style>

<h1>Manage Keyos Plugins</h1>

<p class='error'>
{$error_msg}
</p>

<form method="post" id="frm_plugins" action="">
{$form_redir}
<div id="hidden_elements"></div>
<table class="list" width="98%">
    <thead>
        <th>Name</th>
        <th>Version</th>
        <th>Description</th>
        <th>Creator</th>
        <th>Status</th>
        <th>Activate / De-activate</th>
    </thead>
    <tbody>
        {foreach from=$active_plugins item='plugin' key='plugin_key'}
            <tr>
                <td>{$plugin.plugin_name} : {$plugin_key}</td>
                <td>{$plugin.plugin_version}</td>
                <td>{$plugin.plugin_desc}</td>
                <td>{$plugin.plugin_creator}</td>
                <td>{$plugin_statuses.$plugin_key.status_display}</td>
                <td>
                    <a href="#" id="act_{$plugin_key}" onclick="activate_plugin('{$plugin_key}','{$plugin_statuses.$plugin_key.status}')">
                          {if $plugin_statuses.$plugin_key.status == PLUGIN_STATUS_ENABLED}Disable{else}Enable{/if}
                    </a>
                </td>
        {/foreach}
    </tbody>
</table>
</form>