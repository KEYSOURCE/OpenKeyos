{assign var="paging_titles" value="Clients, Transfer Assets"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
    {literal}
    function info_display_state(label, info_panel_name, part)
    {
        var info_panel = document.getElementById(info_panel_name);
        if(part == "source")
        {
            var go = document.getElementById("go_source");
        }
        else
        {
            var go = document.getElementById("go_dest");
        }
        if(info_panel.style.display=="block")
        {
            //it's displayed we hide it
            info_panel.style.display = "none";
            label.innerHTML = "Show &#0187;"
        }
        else{
            info_panel.style.display = "block";
            label.innerHTML = "Hide &#0187;"
            //go.value = info_panel_name;
            //document.forms["transf_frm"].submit();
        }
    }
    function spl_display_state(label, info_panel_name)
    {
        var info_panel = document.getElementById(info_panel_name);
        if(info_panel.style.display=="block")
        {
            //it's displayed we hide it
            info_panel.style.display = "none";
            //label.innerHTML = "Show &#0187;"
        }
        else{
            info_panel.style.display = "block";
            //label.innerHTML = "Hide &#0187;"
        }
    }
//]]>
    {/literal}
</script>

<h1>Assets Transfer</h1>
<p class="error">{$error_msg}</p>

<form  name="transf_frm" method="post" action="">
{$form_redir}
<input type="hidden" name="go_source" id="go_source" value="" />
<input type="hidden" name="go_dest" id="go_dest" value="" />
<div  style="border: 0px solid black; display: inline; width: 99% height: 99%;" >
    <table class="list" style="padding: 5px; float:left; width: 49%; height: 100%;">
        <thead>
            <tr>
                <td align="left">
                    <select id="src_customer" name="src_customer" onchange="submit()">
                        <option value="">[Select customer]</option>
                        {html_options options=$customers selected=$src_cust}
                    </select>
                </td>
                <td align="middle">
                    <input type="submit" value="Select"/>
                </td>
            </tr>
        </thead>
         {if $src_cust}
        <tr>
            <td  colspan="2" width="100%">
                <div style="border: 1px solid black; width: 100%; ">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Info</label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'source_info', 'source');">Hide &#0187;</label>
                    </p>
                    <table id="source_info" name="source_info" width="98%" style="display: block;">
                        <tr>
                            <td style="font-weight: bold; width: 30%">Name: </td>
                            <td style="width: 70%;">{$source->name} </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">ERP Name: </td>
                            <td style="width: 70%;">{$source->ERP_name} </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">ERP ID: </td>
                            <td style="width: 70%;">{$source->erp_id} </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">Contract type: </td>
                            <td style="width: 70%;">{assign var="ct" value=$source->contract_type}{$CONTRACT_TYPES.$ct}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">Account manager: </td>
                            <td style="width: 70%;">{assign var="am" value=$source->account_manager}{$ACCOUNT_MANAGERS.$am}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        {/if}
        {assign var="stc" value=$src_tickets|count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Tickets
                        {foreach  from=$src_tickets key="stat" item="tl"}
                            {$tickets_statuses.$stat}({$stc.$stat})
                        {/foreach}
                        </label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'source_tickets', 'source');">Show &#0187;</label>
                    </p>
                    <table id="source_tickets" name="source_tickets" width="98%" style="display: none;">
                        {foreach from=$src_tickets key="stat" item="tl"}
                        <tr>
                            <td style="font-weight: bold;" {if !$dest_cust}colspan=2{/if}><label onclick="spl_display_state(this, '{$tickets_statuses.$stat}_{$src_cust}_tickets');">{$tickets_statuses.$stat}({$stc.$stat})</label></td>
                            {if $dest_cust}<td style="text-align: right;">Transfer &#0187;</td>{/if}
                        </tr>
                        <tr><td colspan=2>
                        <div id="{$tickets_statuses.$stat}_{$src_cust}_tickets" name="{$tickets_statuses.$stat}_{$src_cust}_tickets" style="display:none;">
                            <table width="100%">
                            {foreach from=$tl key="tid" item="subj"}
                            <tr>
                                <td style="width: 20%;"><a href="/?cl=krifs&op=ticket_edit&id={$tid}">{$tid}</td>
                                <td style="width: 80%;"><a href="/?cl=krifs&op=ticket_edit&id={$tid}">{$subj}</td>
                            </tr>
                            {/foreach}
                            </table>
                        </div>
                        </td></tr>
                        {/foreach}
                        <tr>
                            <td style="text-align: right;" colspan=2>
                                <label>Transfer all &#0187;</label>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>

        {assign var="stc" value=$src_irs|count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Interventions
                        {foreach  from=$src_irs key="stat" item="irl"}
                            {$INTERVENTION_STATS.$stat}({$stc.$stat})
                        {/foreach}
                        </label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'source_irs', 'source');">Show &#0187;</label>
                    </p>
                    <table id="source_irs" name="source_irs" width="98%" style="display: none;">
                        {foreach from=$src_irs key="stat" item="irl"}
                        <tr>
                            <td style="font-weight: bold;" {if !$dest_cust}colspan=2{/if}><label onclick="spl_display_state(this, '{$INTERVENTION_STATS.$stat}_{$src_cust}_irs');">{$INTERVENTION_STATS.$stat}({$stc.$stat})</label></td>
                            {if $dest_cust}<td style="text-align: right;">Transfer &#0187;</td>{/if}
                        </tr>
                        <tr><td colspan=2>
                        <div id="{$INTERVENTION_STATS.$stat}_{$src_cust}_irs" name="{$INTERVENTION_STATS.$stat}_{$src_cust}_irs" style="display:none;">
                            <table width="100%">
                            {foreach from=$irl key="irid" item="subj"}
                            <tr>
                                <td style="width: 20%;"><a href="/?cl=krifs&op=intervention_edit&id={$irid}">{$irid}</td>
                                <td style="width: 80%;"><a href="/?cl=krifs&op=intervention_edit&id={$irid}">{$subj}</td>
                            </tr>
                            {/foreach}
                            </table>
                        </div>
                        </td></tr>
                        {/foreach}
                        <tr>
                            <td style="text-align: right;" colspan=2>
                                <label>Transfer all &#0187;</label>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        {assign var="stc" value=$src_comp|count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Computers
                        {foreach  from=$src_comp key="stat" item="cl"}
                            {$COMP_TYPES.$stat}({$stc.$stat})
                        {/foreach}
                        </label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'source_comp', 'source');">Show &#0187;</label>
                    </p>
                    <table id="source_comp" name="source_comp" width="98%" style="display: none;">
                        {foreach from=$src_comp key="stat" item="cl"}
                        <tr>
                            <td style="font-weight: bold;" {if !$dest_cust}colspan=2{/if}><label onclick="spl_display_state(this, '{$COMP_TYPES.$stat}_{$src_cust}_comp');">{$COMP_TYPES.$stat}({$stc.$stat})</label></td>
                            {if $dest_cust}<td style="text-align: right;">Transfer &#0187;</td>{/if}
                        </tr>
                        <tr><td colspan=2>
                        <div id="{$COMP_TYPES.$stat}_{$src_cust}_comp" name="{$COMP_TYPES.$stat}_{$src_cust}_comp" style="display:none;">
                            <table width="100%">
                            {foreach from=$cl key="cid" item="name"}
                            <tr>
                                <td style="width: 20%;"><a href="/?cl=kawacs&op=computer_view&id={$cid}">{$cid}</td>
                                <td style="width: 80%;"><a href="/?cl=kawacs&op=computer_view&id={$cid}">{$name}</td>
                            </tr>
                            {/foreach}
                            </table>
                        </div>
                        </td></tr>
                        {/foreach}
                        <tr>
                            <td style="text-align: right;" colspan=2>
                                <label>Transfer all &#0187;</label>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        {if $src_users_count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Users ({$src_users_count})</label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'source_users', 'source');">Show &#0187;</label>
                    </p>
                    <table id="source_users" name="source_users" width="98%" style="display: none;">
                        {foreach from=$src_users item='usr' key="uid"}
                        <tr>
                            <td style="width: 20%;"><a href="/?cl=user&op=user_edit&id={$uid}">{$uid}</td>
                            <td style="width: 80%;"><a href="/?cl=user&op=user_edit&id={$uid}">{$usr}</td>
                        </tr>
                        {/foreach}
                        <tr>
                            <td style="text-align: right;" colspan=2>
                                <label>Transfer all &#0187;</label>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        {/if}
    </table>
    <table class="list" style="padding: 5px; float: left;" width="49%" height="100%" >
        <thead>
            <tr>
                <td align="left">
                    <select id="dest_customer" name="dest_customer" onchange="submit()">
                        <option value="">[Select customer]</option>
                        {html_options options=$customers selected=$dest_cust}
                    </select>
                </td>
                <td align="middle">
                    <input type="submit" value="Select"/>
                </td>
            </tr>
        </thead>
         {if $dest_cust}
        <tr>
            <td colspan="2">
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align: center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Info</label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'dest_info', 'dest');">Hide &#0187;</label>
                    </p>
                    <table id="dest_info" name="dest_info" width="98%" style="display: block;">
                        <tr>
                            <td style="font-weight: bold; width: 30%">Name: </td>
                            <td>{$dest->name} </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">ERP Name: </td>
                            <td>{$dest->ERP_name} </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">ERP ID: </td>
                            <td>{$dest->erp_id} </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">Contract type: </td>
                            <td>{assign var="ct" value=$dest->contract_type}{$CONTRACT_TYPES.$ct}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; width: 30%;">Account manager: </td>
                            <td>{assign var="am" value=$dest->account_manager}{$ACCOUNT_MANAGERS.$am}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        {/if}
        {assign var="stc" value=$dest_tickets|count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Tickets
                        {foreach  from=$dest_tickets key="stat" item="tl"}
                            {$tickets_statuses.$stat}({$stc.$stat})
                        {/foreach}
                        </label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'dest_tickets', 'dest');">Show &#0187;</label>
                    </p>
                    <table id="dest_tickets" name="dest_tickets" width="98%" style="display: none;">
                        {foreach from=$dest_tickets key="stat" item="tl"}
                        <tr>
                            <td style="font-weight: bold;" {if !$dest_cust}colspan=2{/if}><label onclick="spl_display_state(this, '{$tickets_statuses.$stat}_{$dest_cust}_tickets');">{$tickets_statuses.$stat}({$stc.$stat})</label></td>
                        </tr>
                        <tr><td colspan=2>
                        <div id="{$tickets_statuses.$stat}_{$dest_cust}_tickets" name="{$tickets_statuses.$stat}_{$dest_cust}_tickets" style="display:none;">
                            <table width="100%">
                            {foreach from=$tl key="tid" item="subj"}
                            <tr>
                                <td style="width: 20%;"><a href="/?cl=krifs&op=ticket_edit&id={$tid}">{$tid}</td>
                                <td style="width: 80%;"><a href="/?cl=krifs&op=ticket_edit&id={$tid}">{$subj}</td>
                            </tr>
                            {/foreach}
                            </table>
                        </div>
                        </td></tr>
                        {/foreach}
                    </table>
                </div>
            </td>
        </tr>
        {assign var="stc" value=$dest_irs|count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Interventions
                        {foreach  from=$dest_irs key="stat" item="irl"}
                            {$INTERVENTION_STATS.$stat}({$stc.$stat})
                        {/foreach}
                        </label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'dest_irs', 'dest');">Show &#0187;</label>
                    </p>
                    <table id="dest_irs" name="dest_irs" width="98%" style="display: none;">
                        {foreach from=$dest_irs key="stat" item="irl"}
                        <tr>
                            <td style="font-weight: bold;" {if !$dest_cust}colspan=2{/if}><label onclick="spl_display_state(this, '{$INTERVENTION_STATS.$stat}_{$dest_cust}_irs');">{$INTERVENTION_STATS.$stat}({$stc.$stat})</label></td>                           
                        </tr>
                        <tr><td colspan=2>
                        <div id="{$INTERVENTION_STATS.$stat}_{$dest_cust}_irs" name="{$INTERVENTION_STATS.$stat}_{$dest_cust}_irs" style="display:none;">
                            <table width="100%">
                            {foreach from=$irl key="irid" item="subj"}
                            <tr>
                                <td style="width: 20%;"><a href="/?cl=krifs&op=intervention_edit&id={$irid}">{$irid}</td>
                                <td style="width: 80%;"><a href="/?cl=krifs&op=intervention_edit&id={$irid}">{$subj}</td>
                            </tr>
                            {/foreach}
                            </table>
                        </div>
                        </td></tr>
                        {/foreach}                        
                    </table>
                </div>
            </td>
        </tr>
        {assign var="stc" value=$dest_comp|count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Computers
                        {foreach  from=$dest_comp key="stat" item="cl"}
                            {$COMP_TYPES.$stat}({$stc.$stat})
                        {/foreach}
                        </label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'dest_comp', 'dest');">Show &#0187;</label>
                    </p>
                    <table id="dest_comp" name="dest_comp" width="98%" style="display: none;">
                        {foreach from=$dest_comp key="stat" item="cl"}
                        <tr>
                            <td style="font-weight: bold;" {if !$dest_cust}colspan=2{/if}><label onclick="spl_display_state(this, '{$COMP_TYPES.$stat}_{$dest_cust}_comp');">{$COMP_TYPES.$stat}({$stc.$stat})</label></td>                            
                        </tr>
                        <tr><td colspan=2>
                        <div id="{$COMP_TYPES.$stat}_{$dest_cust}_comp" name="{$COMP_TYPES.$stat}_{$dest_cust}_comp" style="display:none;">
                            <table width="100%">
                            {foreach from=$cl key="cid" item="name"}
                            <tr>
                                <td style="width: 20%;"><a href="/?cl=kawacs&op=computer_view&id={$cid}">{$cid}</td>
                                <td style="width: 80%;"><a href="/?cl=kawacs&op=computer_view&id={$cid}">{$name}</td>
                            </tr>
                            {/foreach}
                            </table>
                        </div>
                        </td></tr>
                        {/foreach}                        
                    </table>
                </div>
            </td>
        </tr>
        {if $dest_users_count}
        <tr>
            <td colspan=2>
                <div style="border: 1px solid black; width: 100%;">
                    <p style="width: 100%; text-align:center;">
                        <label style="width:50%; text-align: left; font-weight: bold;">Users ({$dest_users_count})</label>
                        <label style="width:50%; text-align: right;" onclick="info_display_state(this, 'dest_users', 'source');">Show &#0187;</label>
                    </p>
                    <table id="dest_users" name="dest_users" width="98%" style="display: none;">
                        {foreach from=$dest_users item='usr' key="uid"}
                        <tr>
                            <td style="width: 20%;"><a href="/?cl=user&op=user_edit&id={$uid}">{$uid}</td>
                            <td style="width: 80%;"><a href="/?cl=user&op=user_edit&id={$uid}">{$usr}</td>
                        </tr>
                        {/foreach}                        
                    </table>
                </div>
            </td>
        </tr>
        {/if}
    </table>
</div>
</form>
