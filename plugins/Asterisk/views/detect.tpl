<link type='text/css' rel='stylesheet' href='{$base_plugin_url}views/css/style.css'>
<script type='text/javascript' language='JavaScript'>
    var missing_caller_id = {$missing_caller_id};
    var caller_detected = {$caller_detected};
    var load_stuff_fn = '{$base_plugin_url}load_misc_data.php';
    var ajax_loader = '{$base_plugin_url}views/images/ajax-loader.gif';
    var user_type_keysource = {$USER_TYPE_KEYSOURCE};
    var user_type_customer = {$USER_TYPE_CUSTOMER};
</script>
<script language="JavaScript" src="/javascript/fancybox/jquery.easing-1.3.pack.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/fancybox/jquery.fancybox-1.3.4.js" type="text/javascript"></script>
<style type="text/css">
    {literal}
    #fancybox-wrap{
        position: 'absolute';
        top: 100px !important;        
    }
{/literal}
</style>
<script type='text/javascript' language="JavaScript" src='{$base_plugin_url}views/js/decoder.js'></script>
<script type='text/javascript' language="JavaScript" src='{$base_plugin_url}views/js/script.js'></script>

<a id='search_caller_id_lnk' href="#div_caller_id" style="display: none;">Seach caller id</a>

<form name='frm_detect_phone' method='POST' action="">
    {$form_redir}
    {if $missing_caller_id}
        
    {else}
        {if $caller_detected} 
        {assign var=user value=$caller_info.user}
        {assign var=customer value=$caller_info.customer}
        {assign var=phone value=$caller_info.phone}                    
        <h1>Caller ID: {$caller_number}: {$user->fname} {$user->lname} from (#{$customer->id}) {$customer->name}</h1>
        <p />           
        <input type="hidden" name="customer_id" id='customer_id' value="{$customer->id}" />
        <div id='caller_information_div'>            
            <div id='phone_info_div'>
                <h2 style="color: black; background-color: #eee;">Call info</h2>
                <table style="width: 100%">
                    <tr>   
                        <td class="prehead">Number</td>
                        <td>{$phone->phone}</td>
                    </tr>
                    <tr>   
                        <td class="prehead">Type</td>
                        <td>{assign var=ptp value=$phone->type}{$PHONE_TYPES.$ptp}</td>
                    </tr>
                    <tr>   
                        <td class="prehead">Comments</td>
                        <td>{$phone->comment|nl2br}</td>
                    </tr>
                </table>
            </div>
            <div id='user_info_div'>         
                {if $caller_info.type=='user'}
                <h2 style="color: black; background-color: #eee;">User</h2>
                <table style="width: 100%">
                    <tr>
                        <td class="prehead">Name</td>
                        <td><a target="_blank" href="/?cl=user&op=user_edit&id={$user->id}">{$user->fname} {$user->lname}</a></td>                        
                    </tr>
                    <tr>
                        <td class="prehead">Email</td>
                        <td><a href="mailto:{$user->email}">{$user->email}</a></td>
                    </tr>
                    <tr>
                        <td class="prehead">Type</td>
                        <td>{assign var='utp' value=$user->type}{$USER_TYPES.$utp}</a></td>
                    </tr>
                    <tr>
                        <td class="prehead">Manager</td>
                        <td>{assign var='manager' value=$user->is_manager}{if $manager}Yes{else}No{/if}</a></td>
                    </tr>
                    <tr>
                        <td colspan="2" class='prehead'>Phones</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            {assign var=user_phones value=$user->phones}
                            <table style="width: 100%">
                                {foreach from=$user_phones item=pn}
                                <tr>
                                    <td>{$pn->phone}</td>
                                    <td style="border-left: 1px solid #eee;">{assign var=ptpx value=$pn->type}{$PHONE_TYPES.$ptpx}</td>
                                    <td style="border-left: 1px solid #eee;">{$pn->comment}</td>
                                </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>                        
                </table>
                {else if $caller_info.type=='contact'}                
                <h2 style="color: black; background-color: #eee;">Customer contact</h2>
                <table style="width: 100%">
                    <tr>
                        <td class="prehead">Name</td>
                        <td><a target="_blank" href="/?cl=customer&op=customer_contact_edit&id={$user->id}">{$user->fname} {$user->lname}</a></td>                        
                    </tr>
                    <tr>
                        <td class="prehead">Email</td>
                        <td><a href="mailto:{$user->email}">{$user->email}</a></td>
                    </tr>                    
                    <tr>
                        <td class="prehead">Position</td>
                        <td>{$user->position}</a></td>
                    </tr>
                    <tr>
                        <td colspan="2" class='prehead'>Phones</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            {assign var=user_phones value=$user->phones}
                            <table style="width: 100%">
                                {foreach from=$user_phones item=pn}
                                <tr>
                                    <td>{$pn->phone}</td>
                                    <td style="border-left: 1px solid #eee;">{assign var=ptpx value=$pn->type}{$PHONE_TYPES.$ptpx}</td>
                                    <td style="border-left: 1px solid #eee;">{$pn->comment}</td>
                                </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>                        
                </table>   
                {/if}
            </div>
            <div id='customer_info_div'>
                <h2 style="color: black; background-color: #eee;">Customer</h2>
                <table style="width: 100%">
                    <tr>
                        <td class='prehead'>Name</td>
                        <td><a target="_blank" href='/?cl=customer&op=customer_edit&id={$customer->id}'>{$customer->name}</a></td>
                    </tr>
                    <tr>
                        <td class='prehead'>Erp ID</td>
                        <td>{$customer->erp_id}</td>
                    </tr>
                    <tr>
                        <td class='prehead'>Contract type</td>
                        <td>{assign var="contract_type" value=$customer->contract_type}{$CONTRACT_TYPES.$contract_type}</td>
                    </tr>
                    <tr>
                        <td class='prehead'>Contract subtype</td>
                        <td>{assign var="contract_sub_type" value=$customer->contract_sub_type}{$CONTRACT_SUBTYPES.$contract_sub_type}</td>
                    </tr>
                    <tr>
                        <td class='prehead'>Price type</td>
                        <td>{assign var="price_type" value=$customer->price_type}{$PRICE_TYPES.$price_type}</td>
                    </tr>
                    {assign var="on_hold" value=$customer->onhold}                        
                    {if $on_hold}
                    <tr>                        
                        <td colspan="2">                           
                            <input type="radio" name="customer_on_hold" value="0" checked="checked">On hold
                            <input type="radio" name="customer_on_hold" value="1">Ok
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="onhold_auth_code" style="display: none;">
                                <input type="text" name="auth_code_txt" size="40" value="" />
                                <input type="hidden" name="caller_id_txt" value="{$phone->phone}" />
                                <input type="submit" name="auth_code_submit" value="Authorize" />
                            </div>
                        </td>
                    </tr>
                    {else}
                    <tr>                        
                        <td class='prehead'>On hold</td>
                        <td>No</td>
                    </tr>
                    {/if}
                </table>
            </div>
        </div>
        <div style="clear: both;"></div>
        <div id="customer_contacts_and_contracts">
            <div id='customer_contacts_info'>
                <h2 style="color: black;">Users & contacts</h2><p style='margin-bottom: 0; border-bottom: 1px solid; width: 100%;'></p>
                <table class="list" width="100%" style="margin-top: 0;">
                    <tr class='head'>
                        <td colspan="2">Users</td>
                    </tr>
                    {foreach from=$customer_users item="user"}
                    <tr>
                        <td>
                            <a href="/?cl=user&op=user_edit&id={$user->id}">{$user->lname} {$user->fname}</a>
                        </td>
                        <td>
                            <div>
                                <table>
                                {assign var=phones value=$user->phones}
                                {foreach from=$phones item=phone}
                                    <tr>
                                        <td>{$phone->phone}</td>
                                        <td>{assign var="ptype" value=$phone->type}{$PHONE_TYPES.$ptype}</td>
                                    </tr>
                                {/foreach}
                                </table>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                    <tr class='head'>
                        <td colspan="2">Contacts</td>
                    </tr>
                    {foreach from=$customer_contacts item="contact"}
                    <tr>
                        <td>
                            <a href="/?cl=customer&op=customer_contact_edit&id={$contact->id}">{$contact->lname} {$contact->fname}</a>
                        </td>
                        <td>
                            <div>
                                <table>
                                {assign var=phones value=$contact->phones}
                                {foreach from=$phones item=phone}
                                    <tr>
                                        <td>{$phone->phone}</td>
                                        <td>{assign var="ptype" value=$phone->type}{$PHONE_TYPES.$ptype}</td>
                                    </tr>
                                {/foreach}
                                </table>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                </table>
            </div>
            <div id='customer_contracts_info'>
                <h2 style="color: black;">Internet contracts</h2><p style='margin-bottom: 0; border-bottom: 1px solid; width: 100%;'></p>
                <table class="list" width="100%" style="margin-top: 0;">
                    <tr class='head'>
                        <td>Contract dates</td>
                        <td>Provider / Contract</td>
                        <td>Client number</td>
                        <td>ADSL number</td>
                        <td>Type</td>
                    </tr>
                    {foreach from=$customer_contracts item=contract}
                    <tr>
                        <td nowrap='nowrap'>
                            <b>Start:</b> {if $contract->start_date > 0}{$contract->start_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}{else}[unspecified]{/if}<br />
                            <b>End:</b> {if $contract->end_date > 0}{$contract->end_date|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}{else}[unspecified]{/if}<br />
                            {if $contract->is_in_notice_period(true)}
                                <br/><b><font color="{if $contract->is_expired()}red{else}orange{/if}">{$contract->get_expiration_string()}</font></b>
                                {if $contract->suspend_notifs}
                                <br/><font class="light_text">[Notif. suspended]</font>
                                {/if}
                            {/if}
                            <br />
                            <b>Status:</b> {if $contract->is_closed}Closed{else}Active{/if}
                        </td>
                        <td nowrap='nowrap'>
                            {assign var='provider' value=$contract->provider}                            
                            {assign var='provider_contract' value=$contract->provider_contract}
                            <b>Provider:</b> <a target="_blank" href="/?cl=klara&op=provider_edit&id={$provider->id}">{$provider->name}</a><br />
                            <b>Contract:</b> <a target="_blank" href="/?cl=klara&op=provider_contract_edit&id={$provider_contract->id}">{$provider_contract->name}</a>
                        </td>
                        <td nowrap="nowrap"><a target="_blank" href="/?cl=klara&op=customer_internet_contract_edit&id={$contract->id}">{$contract->client_number}</a></td>
                        <td nowrap='nowrap'><a target="_blank" href="/?cl=klara&op=customer_internet_contract_edit&id={$contract->id}">{$contract->adsl_line_number}</a></td>
                        <td nowrap='nowrap'>
                            {assign var="line_type" value=$contract->line_type}
                            {$LINE_TYPES.$line_type}
                        </td>
                    </tr>
                    {foreachelse}
                        <tr><td colspan="5">[No internet contracts defines]</td></tr>
                    {/foreach}
                </table>
            </div>    
        </div>
        <div style="clear: both;"></div>
        <div id='actions'>
            <div id="create_update_ticket">
                <h2 style="color: black;">Create/Update ticket from conversation</h2>
                <p />
                <table style="width: 100%;">
                    <tr>
                        <td class='prehead' id="action_title" style="width: 200px;">Create ticket</td>
                        <td>
                            <select id="ticket_sel" name="ticket[id]" style="max-width: 300px;">
                                <option value='0'>[Create new ticket]</option>
                                {html_options options=$customer_tickets}
                            </select>
                        </td>
                        <td>
                            <select id="ticket_stat" name="ticket[status]" style="max-width: 300px;">
                                <option value='-1'>[Not closed]</option>
                                {html_options options=$TICKET_STATUSES}
                            </select>
                        </td>
                        <td><a target="_blank" id="krifs_editor_lnk" href="/?cl=krifs&op=ticket_add&customer_id={$customer->id}">KRIFS editor new ticket</a></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div style="display: none; height: auto;" id="ticket_comments_div">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            {*
            <div id="computers_list">
               <h2 style="color: black;">Computers list</h2> 
               <table style="width: 100%;">
                    <tr>
                        <td class='prehead' id="action_title" style="width: 200px;">Choose computer</td>
                        <td>
                            <select id="computers_sel" name="computer[id]" style="max-width: 300px;">
                                <option value='0'>[Choose computer]</option>
                                {html_options options=$customer_computers}
                            </select>
                        </td>
                        <td><div id='computers_redirect_container'></div></td>
                    </tr>
               </table>                    
            </div>
            *}
        </div>
        {else}
            <a href="#caller_unknown_actions" id="lnk_caller_unknown" style="display: none;">Unknown caller actions</a>
            <h1>Call from: {$caller_number}: Unknown</h1>
            <p /> 
            Caller not detected. What you want to do?
            
        {/if}               
    {/if}


</form>

<div id="search" style="display: none;">
    <div id='div_caller_id'>
        <form name="frm_search_caller" method="POST" action="">
            {$form_redir}
            <label>Search phone number:</label> <input type='text' name="caller_id_txt" id="caller_id_txt" value="" />
            <input type="submit" name='search_caller_id' value="Search" />
        </form>
    </div>
</div>
            
<div id="caller_unknown" style="display: none;">
    <div id="caller_unknown_actions">        
        <h1>Unkown caller what you want to do?</h1>
        <p />
        <div style='border-bottom: 1px solid black; margin-bottom: 15px; height: 40px;'>
            <label id="sel_action_title" style="font-weight: bold">Actions:</label><br />
            <input style="float: left; margin: 5px 10px 5px 0;" type="button" name="btn_add_to_existing" onclick="add_to_existing_user();" value="Add number to existing user" />
            <input style="float: left; margin: 5px 10px 5px 0;" type="button" name="btn_create_new_user" onclick="create_new_user();" value="Create new user" /> 
            <input style="float: left; margin: 5px 10px 5px 0;" type="button" name="btn_add_to_customer_contact" onclick="add_to_customer_contact();" value="Add number to customer contact" />
            <input style="float: left; margin: 5px 10px 5px 0;" type="button" name="btn_create_new_customer_contact" onclick="create_new_customer_contact();" value="Create new customer contact" /> 
            <input style="float: left; margin: 5px 10px 5px 0;" type="button" name="btn_create_new_customer" onclick="create_new_customer();" value="Create new customer" />
        </div>
        <div id="actions_impl_div">
        <div id="add_to_existing_user_div" style="display: none; height: auto; margin-top: 15px;">
            <form id='frm_add_to_existing' name="frm_add_to_existing" method="POST" action="">
            {$form_redir}
            <table style="width: 98%;">
                <tr>
                    <td>
                        <label>Select customer: </label>
                    </td>
                    <td>
                        <select name="cu_customer_id" id='cu_customer_id'>
                            <option value="0">[Select customer]</option>
                            {html_options options=$customers_list}
                        </select>
                    </td>
                </tr>
                <tr id='users_hid' style="display: none;">
                    <td>
                        <label>Select user: </label>
                    </td>
                    <td id='users_select_cell'></td>
                </tr>
                <tr id='phones_hid' style="display: none;">
                    <td colspan="2" id='phones_list_cell'></td>
                </tr>
                <tr id='phone_add_hid' style='display: none; margin-top: 20px;'>
                    <td colspan="2" style='border: 1px solid #999; padding-top: 10px;'>
                        <table style='width: 98%'>
                            <tr>
                                <td><label>Add number: </label></td>
                                <td><input type='text' value="{$caller_number}" name="phone[phone]" /></td>
                            </tr>
                            <tr>
                                <td><label>Type: </label></td>
                                <td><select name='phone[type]'>{html_options options=$PHONE_TYPES}</select></td>
                            </tr>
                            <tr>
                                <td><label>Comment: </label></td>
                                <td><textarea name='phone[comment]' rows="4" cols="40"></textarea></td>
                            </tr>
                            <tr>
                                <td colspan="2" >
                                    <input style='float: right; margin-left: 20px;' type="submit" name='add_to_user_submit' value="Add phone number" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table> 
            </form>
        </div>
        <div id="create_new_user_div" style="display: none; height: auto; margin-top: 15px;">                
            <form id='frm_create_new_user' name="frm_create_new_user" method="POST" action="">
            {$form_redir}
            <table style="width: 98%;">
                <tr>
                    <td>
                        <label>Select customer: </label>
                    </td>
                    <td>
                        <select name="cu_cusr_customer_id" id='cu_cusr_customer_id'>
                            <option value="0">[None - Keysource user]</option>
                            {html_options options=$customers_list}
                        </select>
                    </td>
                </tr>
                <tr id='subscription_frm' style="display: none; margin-top: 10px;">
                    <td colspan="2">
                        <label id='create_new_user_title' style="font-weight: bold;">Add new KeySource user</label><p />
                        <table class='list' style='width: 98%'>
                            <tr class="head">
                                <td colspan="2">Personal information</td>
                            </tr>
                            <tr>
                                <td>First name</td>
                                <td><input type='text' id='cusr_fname' name='user[fname]' value="" size=30><label id='fname_err'></label></td>
                            </tr>
                            <tr>
                                <td>Last name</td>
                                <td><input type='text' id='cusr_lname' name='user[lname]' value="" size=30><label id='lname_err'></label></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td><input type='text' name='user[email]' id='cusr_email' value="" size=30><label id='eml_err'></label></td>
                            </tr>
                            <tr>
                                <td>Language</td>
                                <td>
                                    <select name="user[language]">
                                            {html_options options=$LANGUAGES}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Newsletter: </td>
                                <td>
                                    <select name="user[newsletter]">
                                        <option value="0">No</option>
                                        <option value="1" {if $user->newsletter} selected {/if}>Yes</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="head">
                                <td colspan="2">Login information</td>
                            </tr>
                            <tr>
                                <td>Login name</td>
                                <td><input type='text' id='cusr_login' name='user[login]' value="" size=30><label id='login_err'></label></td>
                            </tr>    
                            <tr>
                                <td>Password: </td>
                                <td><input id="pass_txt" type="password" name="user[password]" value="" size="30"> <input type="button" id='btn_gen_auto_pass' value="Generate" /></td>
                            </tr>
                            <tr>
                                <td>Confirm password: </td>
                                <td><input id='pass_confirm_txt' type="password" name="user[password_confirm]" value="" size="30"><label  style='color: red;' id='confirm_err'></label></td>
                            </tr>
                            <tr>
                                <td>Type: </td>
                                <td>
                                    <select id="cusr_type" name="user[type]">
                                        {html_options options=$USER_TYPES}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Allow private access: </td>
                                <td>
                                    <select name="user[allow_private]">
                                            <option value="0">No</option>
                                            <option value="1" {if $user->allow_private} selected {/if}>Yes</a>
                                    </select>
                                    For customer users only. Internal users are always allowed to see private entries.
                                </td>
                            </tr>                                
                            <tr>
                                <td>Allow user management access: </td>
                                <td>
                                    <select name="user[has_kadeum]">
                                            <option value="0">No</option>
                                            <option value="1" {if $user->has_kadeum} selected {/if}>Yes</a>
                                    </select>
                                    For customer users only.
                                </td>
                            </tr>
                            <tr class="head">
                                <td colspan="2">Phone</td>
                            </tr>
                            <tr>
                                <td><label>Phone number: </label></td>
                                <td><input type='text' value="{$caller_number}" name="user_phone[phone]" /></td>
                            </tr>
                            <tr>
                                <td><label>Type: </label></td>
                                <td><select name='user_phone[type]'>{html_options options=$PHONE_TYPES}</select></td>
                            </tr>
                            <tr>
                                <td><label>Comment: </label></td>
                                <td><textarea name='user_phone[comment]' rows="4" cols="40"></textarea></td>
                            </tr>
                        </table>
                    </td>
                </tr>                    
                <tr>
                    <td colspan="2">
                        <input type="hidden" name="user[send_invitation_email]" value="1" />
                        <input type="submit" id='create_new_user_submit' name="create_new_user_submit" value="Create user" style='float: right; margin-left: 20px;' />
                    </td>
                </tr>
            </table>  
            </form>
        </div>
        <div id="add_to_existing_customer_contact_div" style="display: none; height: auto; margin-top: 15px;">
            <form id='frm_add_to_existing_cc' name="frm_add_to_existing_cc" method="POST" action="">
            {$form_redir}
            <table style="width: 98%;">
                <tr>
                    <td>
                        <label>Select customer: </label>
                    </td>
                    <td>
                        <select name="cu_cc_customer_id" id='cu_cc_customer_id'>
                            <option value="0">[Select customer]</option>
                            {html_options options=$customers_list}
                        </select>
                    </td>
                </tr>
                <tr id='customer_contacts_hid' style="display: none;">
                    <td>
                        <label>Select contact: </label>
                    </td>
                    <td id='cc_select_cell'></td>
                </tr>
                <tr id='cc_phones_hid' style="display: none;">
                    <td colspan="2" id='cc_phones_list_cell'></td>
                </tr>
                <tr id='cc_phone_add_hid' style='display: none; margin-top: 20px;'>
                    <td colspan="2" style='border: 1px solid #999; padding-top: 10px;'>
                        <table style='width: 98%'>
                            <tr>
                                <td><label>Add number: </label></td>
                                <td><input type='text' value="{$caller_number}" name="phone[phone]" /></td>
                            </tr>
                            <tr>
                                <td><label>Type: </label></td>
                                <td><select name='phone[type]'>{html_options options=$PHONE_TYPES}</select></td>
                            </tr>
                            <tr>
                                <td><label>Comment: </label></td>
                                <td><textarea name='phone[comments]' rows="4" cols="40" ></textarea></td>
                            </tr>
                            <tr>
                                <td colspan="2" >
                                    <input style='float: right; margin-left: 20px;' type="submit" name='add_to_customer_contact_submit' value="Add phone number" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </form>
        </div>
        <div id="create_new_contact_div" style="display: none; height: auto; margin-top: 15px;">                
            <form id='frm_create_new_contact' name="frm_create_new_contact" method="POST" action="">
            {$form_redir}
            <table style="width: 98%;">
                <tr>
                    <td>
                        <label>Select customer: </label>
                    </td>
                    <td>
                        <select name="cu_ccont_customer_id" id='cu_ccont_customer_id'>
                            <option value="0">[Select customer]</option>
                            {html_options options=$customers_list}
                        </select>
                    </td>
                </tr>
                <tr id='contact_subscription_frm' style="display: none; margin-top: 10px;">
                    <td colspan="2">
                        <label id='create_new_contact_title' style="font-weight: bold;">Create new contact</label><p />
                        <table class='list' style='width: 98%'>
                            <tr class="head">
                                <td colspan="2">Personal information</td>
                            </tr>
                            <tr>
                                <td>First name</td>
                                <td><input type='text' id='ccont_fname' name='contact[fname]' value="" size=30><label id='cfname_err'></label></td>
                            </tr>
                            <tr>
                                <td>Last name</td>
                                <td><input type='text' id='ccont_lname' name='contact[lname]' value="" size=30><label id='clname_err'></label></td>
                            </tr>
                            <tr>
                                <td>E-mail: </td>
                                <td><input id='ccont_email' type="text" name="contact[email]" value="" size="30"/><label id='cemail_err'></label></td>
                            </tr>
                            <tr>
                                <td>Position/Function: </td>
                                <td><input type="text" name="contact[position]" value="" size="30"/></td>
                            </tr>
                            <tr>
                                <td>Comments: </td>
                                <td>
                                    <textarea name="contact[comments]" rows="4" cols="40"></textarea>
                                </td>
                            </tr>
                            <tr class="head">
                                <td colspan="2">Phone</td>
                            </tr>
                            <tr>
                                <td><label>Phone number: </label></td>
                                <td><input id='ccont_phone_number' type='text' value="{$caller_number}" name="contact_phone[phone]" /><label id='ccont_phone_number_err'></label></td>
                            </tr>
                            <tr>
                                <td><label>Type: </label></td>
                                <td><select name='contact_phone[type]'>{html_options options=$PHONE_TYPES}</select></td>
                            </tr>
                            <tr style='border-bottom: 0;'>
                                <td style='border-bottom: 0;'><label>Comments: </label></td>
                                <td style='border-bottom: 0;'><textarea name='contact_phone[comments]' rows="4" cols="40"></textarea></td>
                            </tr>                            
                            <tr style='border-bottom: 0; margin-top: 20px;'>
                                <td colspan="2" style='border-bottom: 0;'>                        
                                    <input type="submit" id='create_new_contact_submit' name="create_new_contact_submit" value="Create contact" style='float: right; margin-left: 20px;' />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>                  
            </table>
            </form>
        </div>
        <div id="create_new_customer_div" style="display: none; height: auto; margin-top: 15px;">                
            <form id='frm_create_new_customer' name="frm_create_new_customer" method="POST" action="">
            {$form_redir}
            <table class="list" style="width: 98%;">
                <tr class="head">
                    <td colspan="2">Personal information</td>
                </tr>
                <tr>
                    <td>Name: </td>
                    <td><input type="text" name="customer[name]" id='ccust_name' value="" size="30"><label id='ccust_name_err'></label></td>
                </tr>   
                <tr class="head">
                    <td colspan="2">Services</td>
                </tr>
                <tr>
                    <td>Kawacs: </td>
                    <td>
                        <select id='ccust_has_kawacs' name="customer[has_kawacs]">
                            <option value="0">No</option>
                            <option value="1" selected>Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Krifs: </td>
                    <td>
                        <select id='ccust_has_krifs' name="customer[has_krifs]">
                            <option value="0">No</option>
                            <option value="1" selected>Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>SLA time: </td>
                    <td><input id='ccust_sla_hours' type="text" name="customer[sla_hours]" value="0" size="6"> hours</td>
                </tr>
                <tr>
                    <td>Account Manager: </td>
                    <td>
                        <select id='ccust_account_manager' name="customer[account_manager]">
                            {html_options options=$ACCOUNT_MANAGERS selected=$DEFAULT_ACCOUNT_MANAGER}
                        </select>
                    </td>
                </tr> 
                <tr>
                    <td colspan="2">                        
                        <input type="submit" id='create_new_customer_submit' name="create_new_customer_submit" value="Create customer" style='float: right; margin-left: 20px;' />
                    </td>
                </tr>
            </table>
            </form>
        </div>
        </div>
    </div>
</div>