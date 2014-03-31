{assign var="paging_titles" value="KAWACS, Generate KawacsAgent deployment script"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}
<script language="javascript" type="text/javascript" src='/javascript/ajax.js'></script>
<script language="text/javascript" type="text/javascript">
    var jarr = [];
    var jargh = "";
    {assign var="i" value=1}
    {foreach from=$profiles_times item="v" key="k"}        
        jargh += {literal}"{/literal}{if $i==1}{literal}{{/literal}{/if}'{$k}':'{$v}'{if $i<$lenpt},{literal}"{/literal};{else}{literal}}";{/literal}{/if}
        {assign var="i" value=$i+1}
    {/foreach}
    {literal}
        jarr = eval("(" + jargh + ")");
        function change_report_interval(){
            var mprof = document.getElementById("script_monitor_profile").value;
            //ka = "1";
            var rinterval = jarr[mprof];
            if(eval(rinterval) > 0){
                document.getElementById("script_report_interval").value = rinterval;
            }
        }
    {/literal}
</script>

<h1>Generate KawacsAgent deployment script</h1>
<p class="error">{$error_msg}</p>

<form method="POST" action="" name="gkas_form">
{$form_redir}

{if !$customer_template}
    {if $depl_count>0}
    <table width="60%">
        <thead>
            <tr>
                <th colspan="4">Existing scripts</th>
            </tr>
            <tr>
                    <th style="font-weight: bold; text-align: left;">Name</th>
                    <th style="font-weight: bold; text-align: left;">Monitor profile</th>
                    <th style="font-weight: bold; text-align: left;">Computer type</th>
                    <th style="font-weight: bold; text-align: left;">&nbsp;</th>
            </tr>
        </thead>
       <tbody>
    {foreach from=$existing_deployers item="dplscript"}
        <tr style="border: 0px solid white">
            <td style="width: 40%;">
            {assign var="customer_id:"|cat:$dplscript.customer|cat:",profile:"|cat:$dplscript.profile|cat:",type:"|cat:$dplscript.type}
            <a href='{"kawacs"|get_link:"get_deployer":$p:"template"}' > {$dplscript.name} </a>
            </td>
            <td style="width: 20%">
                    {assign var="prf" value=$dplscript.profile}
                    {$monitor_profiles.$prf}
            </td>
            <td style="width: 20%">
                    {assign var="typ" value=$dplscript.type}                     
                    {$computer_types.$typ}
            </td>
            <td style="width: 20%">
                <input type="submit"  name="forward" value="Send to customer" />
             </td>
        </tr>
    {/foreach}
        </tbody>
    </table>
    <p />
    <p />
    {/if}
     {*this is a KS admin, we'll allow this one to create scripts for other customers*}
     <table class="list" style="width: 90%;">
        <thead>
            <tr>
                <td colspan="2" style="text-align: left;">Script Information<p/></td>
            </tr>
        </thead>
        <tbody>
        <tr>
                <input type="hidden" name="chgcust" id="chgcust" value="" />
                <td class="highlight" style="width: 30%;">Customer name</td>
                <td class="posthighlight" style="width: 70%;">
                    <select name="script_customer" id="script_customer" onchange="gkas_form.chgcust.value = this.value;gkas_form.submit();">
                            <option value="-1">[Select customer]</option>
                            {html_options options=$customers_list selected=$sel_customer}
                        </select>
                </td>
        </tr>
        <tr>
                <td class="highlight" style="width: 30%;">Server URL:</td>
                <td class="posthighlight" style="width: 70%;">
                    <input type="text" name="script_server_url" size="100" id="script_server_url" value={$smarty.const.KEYOS_KAWACS_SERVER} />
                </td>
        </tr>
        <tr>
                <td class="highlight" style="width: 30%;">Monitor profile:</td>
                <td class="posthighlight" style="width: 70%;">
                    <select name="script_monitor_profile" id="script_monitor_profile" onchange="change_report_interval()">                            
                            {html_options options=$monitor_profiles selected=$sel_profile}
                        </select>
                </td>
        </tr>
        <tr>
                <td class="highlight" style="width: 30%;">Computer type:</td>
                <td class="posthighlight" style="width: 70%;">
                        <select name="script_computer_type" id="script_computer_type">
                            {html_options options=$computer_types selected=$sel_type}
                        </select>
                </td>
        </tr>
        <tr>
                <td class="highlight" style="width: 30%;">Report Interval:</td>
                <td class="posthighlight" style="width: 70%;">
                    <input type="text" name="script_report_interval" size="15" id="script_report_interval" value="{$sel_repint}" /> minutes
                </td>
        </tr>
        <tr>
                <td class="highlight" style="width: 30%;">Installer link:</td>
                <td class="posthighlight" style="width: 70%;">
                    <input type="text" name="script_installer_url" size="100" id="script_installer_url" value={$installer_url} readonly />
                </td>
        </tr>
        </tbody>
    </table>
{/if}

<p/>
<input type="submit" name="save" value="Generate">
<input type="submit" name="cancel" value="Cancel">
</form>