<div style='text-align: left;'>
{assign var=paging_titles value="Customer Reports"}
{include file="paging.html"}
</div>
<link rel="stylesheet" type="text/css" href="{$base_plugin_url}views/css/style.css" />
<link rel="stylesheet" type="text/css" href="{$base_plugin_url}views/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="{$base_plugin_url}views/css/layout.css" media="screen" />

<script language="JavaScript" type="text/javascript" src="{$base_plugin_url}views/js/datepicker.js"></script>
<script language="JavaScript" type="text/javascript" src="{$base_plugin_url}views/js/eye.js"></script>
<script language="JavaScript" type="text/javascript" src="{$base_plugin_url}views/js/utils.js"></script>
<script language="JavaScript" type="text/javascript" src="{$base_plugin_url}views/js/layout.js?ver=1.0.2"></script>
<script language="JavaScript" type="text/javascript" src="{$base_plugin_url}views/js/customer_reports.js"></script>

<h1 style="font-size: 24px; font-weight: bold; text-align: left;">{$page_title} {if $customer->id}#{$customer->id} {$customer->name}{/if}</h1>

<form name='customer_report_frm' id="customer_report_frm" method="POST" action="">
    {$form_redir}
    {if not $customer->id}
        <div style="width: 100%; margin-top: 20px; text-align: left;">
            <label style="float: left; margin-left: 10px; padding-top: 3px;">{$page_messages.select_customer}</label> 
            <select name="filter[customer_id]" id="select_customer_filter" style="margin-left: 10px;">
                <option value="-1">[{$page_messages.select_customer}]</option>
                {html_options options=$customers_list }
            </select>
        </div>
        <div class="clear" />
    {else}
        <input type="hidden" name="filter[customer_id]" value="{$customer->id}" />
        <input type="hidden" name="report[customer_id]" value="{$customer->id}" />
        <div id="section_settings" class="section_div">
            {*report settings append*}
            <h2 style="font-size: 20px;">{$page_messages.settings}</h2>
            <div class="settings">
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_customer}:</label> 
                    <b>(#{$customer->id}) {$customer->name}   <a href="/?cl=customer_reports&op=generate_report">Change &#187;</a></b>                               
                </div>
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_type}:</label> 
                    <select name="report[report_type]" id="report_type_sel">
                        {html_options options=$REPORTS_TYPES selected="$report.report_type"}
                    </select>                
                </div>
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_title}:</label>
                    <input type="report[title]" size="50" value="{$report.title}" />
                </div>
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_interval}:</label>
                    <input type="text" name="report[start_date]" id="start_date" value="{$report.start_date}" />
                    <label class="settings_label_prehead_min">-</label>
                    <input type="text" name="report[end_date]" id="end_date" value="{$report.end_date}" />                    
                </div>
            </div>
            <div class="settings" style="margin-left:10px;">
                <div>
                    <label class="settings_label_prehead">&nbsp;</label>                     
                </div>
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_cover_page}:</label> 
                    <select name="report[cover_page]" id="cover_page_sel">
                        <option value="1">{$page_messages.report_yes}</option>
                        <option value="0">{$page_messages.report_no}</option>
                    </select>                               
                </div>
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_table_of_contents}:</label> 
                    <select name="report[table_of_contents]" id="table_of_contents_sel">
                        <option value="1">{$page_messages.report_yes}</option>
                        <option value="0">{$page_messages.report_no}</option>
                    </select>                               
                </div>
                <div>
                    <label class="settings_label_prehead">{$page_messages.report_section_cover}:</label> 
                    <select name="report[section_cover]" id="section_cover_sel">
                        <option value="1">{$page_messages.report_yes}</option>
                        <option value="0">{$page_messages.report_no}</option>
                    </select>                               
                </div>    
            </div>
        </div>
        <div class="clear" />
        <div id="section_technical" class="section_div">
            <h2 style="font-size: 20px;">{$page_messages.technical_information}</h2>
            <div class="settings">
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[servers]" {if $report.servers}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_servers}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[workstations]" {if $report.workstations}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_workstations}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[warranties]" {if $report.warranties}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_warranties}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[peripherals]" {if $report.peripherals}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_peripherals}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[software]" {if $report.software}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_software}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[all_software]" {if $report.all_software}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_all_software}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[licences]" {if $report.licenses}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_licences}</label>                
                </div>
                <div>
                    <input style="margin-top: 2px;" type="checkbox" name="report[users]" {if $report.users}checked="checked"{/if}>
                    <label class="settings_label_prehead_min">{$page_messages.report_users}</label>                
                </div>
            </div>
        </div>
        <div class="clear">
        <div style="width: 100%; margin-left: 20px; margin-top: 20px; text-align: left;">
            <input type="submit" name="generate" value="{$page_messages.generate}" />
        </div>
    {/if}    
</form>