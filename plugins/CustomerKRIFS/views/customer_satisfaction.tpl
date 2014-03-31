{assign var="paging_titles" value="Technical Support, Customer Satisfaction"}
{assign var="paging_urls" value="/?cl=customer_krifs"}
{include file="paging.html"}

<link type='text/css' rel='stylesheet' href='{$base_plugin_url}views/css/customer_satisfaction.css' />

<form name="customer_satisfaction_frm" id="customer_satisfaction_frm" method="post" action="">
    {$form_redir}
    <input type="hidden" name="customer_satisfaction[ticket_id]" value="{$ticket->id}" />
    <input type="hidden" name="customer_satisfaction[customer_id]" value="{$ticket->customer_id}" />
    <div id="satisf_customer_container">
        <div id="logo_container"><label id='ticket_title'>Ticket n° {$ticket->id}</label></div>
        <div style="clear: both;"></div>
        <div id="thanks_container" class="NormaWhiteBold1">{$thanks_note}</div>
        <div style="clear: both;"></div>
        <div id="questions_block" style="margin-top: 5px;">
            <table width="100%">
                <tbody>
                    <tr>
                        <td width="65%">&nbsp;</td>
                        <td width="15%" class="NormaGreyMini">{$satisfaction_level.little_satisfied}</td>
                        <td width="5%">&nbsp;</td>
                        <td width="15%" class="NormaGreyMini">{$satisfaction_level.very_satisfied}</td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMiniBold">1. {$questions.overall_satisfaction}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[overall_satisfaction]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[overall_satisfaction]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[overall_satisfaction]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[overall_satisfaction]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[overall_satisfaction]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMiniBold">2. {$questions.problem_solved}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[problem_solved]" value="1" checked='checked'>{$responses.yes}
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[problem_solved]" value="0">{$responses.no}
                            </div>                                                    
                        </td>
                    </tr>
                    <tr>
                        <td width="100%" colspan="4" style="padding-top: 10px;" class="NormaGreyMiniBold">3. {$questions.satisfaction_degree}</td>
                    </tr>
                    <tr>
                        <td width="65%">&nbsp;</td>
                        <td width="15%" class="NormaGreyMini">{$satisfaction_level.little_satisfied}</td>
                        <td width="5%">&nbsp;</td>
                        <td width="15%" class="NormaGreyMini">{$satisfaction_level.very_satisfied}</td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.waiting_time}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[wating_time]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[wating_time]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[wating_time]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[wating_time]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[wating_time]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.expertize}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[expertize]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[expertize]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[expertize]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[expertize]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[expertize]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.urgency_consideration}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[urgency_consideration]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[urgency_consideration]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[urgency_consideration]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[urgency_consideration]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[urgency_consideration]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.impact_consideration}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[impact_consideration]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[impact_consideration]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[impact_consideration]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[impact_consideration]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[impact_consideration]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.technician_expertize}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_expertize]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_expertize]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[technician_expertize]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_expertize]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_expertize]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.technician_commitment}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_commitment]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_commitment]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[technician_commitment]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_commitment]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[technician_commitment]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="65%" class="NormaGreyMini" style="padding-left: 10px;"> {$questions.time_to_solve}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[time_to_solve]" value="1">1
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[time_to_solve]" value="2">2
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                                <input type="radio" name="customer_satisfaction[time_to_solve]" value="3" checked="checked">3
                            </div>                        
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[time_to_solve]" value="4">4
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[time_to_solve]" value="5">5
                            </div>
                        </td>
                    </tr>
                    <tr><td colspan="4" width="100%" style="height: 20px;"></td></tr>                    
                    <tr>
                        <td width="65%" class="NormaGreyMiniBold">4. {$questions.occurence}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            {foreach from=$incident_occurence key="occurence_id" item="occurence_text"}
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[occurence]" value="{$occurence_id}" {if $occurence_id==1}checked="checked"{/if}>{$occurence_text}
                            </div>
                            {/foreach}
                        </td>
                    </tr>                    
                    <tr><td colspan="4" width="100%" style="padding-top: 20px;" class="NormaGreyMiniBold">5. {$questions.suggestions}</td></tr>                    
                    <tr>
                        <td width="65%">
                            <textarea name="customer_satisfaction[suggestions]" rows="5" cols="60" style="border: 1px solid black;"></textarea>
                        </td>
                        <td colspan="3" width="35%">&nbsp;</td>
                    </tr>
                    
                    <tr><td colspan="4" width="100%" style="height: 20px;"></td></tr>
                    <tr>
                        <td width="65%" class="NormaGreyMiniBold">6. {$questions.would_recommend}</td>
                        <td width="35%" colspan="3" class="NormaGreyMini">
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[would_recommend]" value="1" checked='checked'>{$responses.yes}
                            </div>
                            <div  style="float: left; margin: 0 20px 0 0;">
                             <input type="radio" name="customer_satisfaction[would_recommend]" value="0">{$responses.no}
                            </div>                                                    
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" width="100%">
                            <div style="margin: auto; width: 150px; height: auto;">
                                <input type="submit" name="send" value="{$submit_texts.send}" class="NormaGreyBold" />
                                <input type="submit" name="quit" value="{$submit_texts.quit}" class="NormaGreyBold" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>