{assign var="paging_titles" value="KRIFS"}
{include file="paging.html"}
{literal}
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#checkall').click(function() {
            var checked_status = this.checked;
            $('.cb-element').each(function() {
                this.checked = checked_status;
            });
        });
    });
</script>
{/literal}
<h1>Tickets from emails</h1>
<p>
    <font class="error">{$error_msg}</font>
</p>
<table class="tab_header">
    <tr>
        <td{if $tab != 'unseen'} class="tab_inactive"{/if}><a href="{'krifs'|get_links:'tickets_from_emails'}">Unread Emails</a></td>
        <td{if $tab != 'last_2_days'} class="tab_inactive"{/if} width="183">
            {assign var="p" value="filter:"|cat:"last_2_years"}
            <a href="{'krifs'|cat:'tickets_from_emails':$p:'template'}" style="width: 183px">Emails since {$filter}</a></td>
    </tr>
</table>

{if $tab == 'unseen'}
<div class="tab_content">
    {if $tickets}
    {assign var="i" value=0}
    <form method="post" action="">
        {$form_redir}

        <table class="list" width="100%">
            <thead>
                <tr>
                    <td><input type="checkbox" checked="checked" id="checkall"/></td>
                    <td>Subject</td>
                    <td>Customer</td>
                    <td>Assigned</td>
                    <td>Mailbox</td>
                    <td>CC List</td>
                    <td>Date</td>
                    <td>&nbsp;</td>
                </tr>
            </thead>
            {foreach from=$tickets item=ticket}
            <tr>
                <td>
                    {if $ticket.customer_id}
                    <input type="checkbox" class="cb-element" name="msgnos[]" value="{$ticket.msgno}" checked="checked"/>
                    {/if}
                </td>
                <td>
                    <a href="{$i}" class="expand-body"><img src="/images/expand.gif" alt="" id="img_{$i}"/></a>
                    {$ticket.subject}
                </td>
                <td>
                    {$ticket.customer}
                </td>
                <td>{$ticket.assigned}</td>
                <td>{$ticket.mail}</td>
                <td>{$ticket.cc_emails}</td>
                <td>{$ticket.mail_date}</td>
                <td>
                    {assign var="p" value="msgno:"|cat:$ticket.msgno}
                    <a href="{'krifs'|cat:'mark_email_as_read':$p:'template'}">Mark as read</a>
                    {if !$ticket.customer_id}
                    <a href="{'krifs'|get_link:'ticket_add'}" id="{$i}" class="ticket_add" target="_blank">Create ticket</a>
                    <input type="hidden" value="{$ticket.body|escape}" id="ticket_body_{$i}">
                    <input type="hidden" value="{$ticket.subject|escape}" id="ticket_subject_{$i}"/>
                    {/if}
                </td>
            </tr>

            <tr style="display: none;" id="body_{$i}">
                <td>&nbsp;</td>
                <td colspan="6">
                    <div style="background-color: #EEEEEE; margin: 5px; padding: 10px;">{$ticket.body}</div>
                </td>
            </tr>
            {assign var="i" value=$i+1}
            {/foreach}
        </table><br />
        <input type="submit" value="Create tickets" name="create"/>
        <input type="submit" value="Mark as read" name="mark_as_read"/>
    </form>
    {else}
    <span style="background-color: lightgreen; color: #0066cc; padding: 10px; display: inline-block;">No new tickets are available for creation.</span>
    {/if}
</div>
{/if}

{if $tab == 'last_2_days'}
<div class="tab_content">
        <form action="" method="post" name="frm_t">
        <table class="list">
            <thead
                <tr>
                    <td colspan="2">Email filters</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Since Date</td>
                    <td>
                        <input type="text" name="from_date" value="{$from_date}" style="width: 250px;"/>
                        <a href="javascript:;" onclick="showCalendarSelector('frm_t', 'from_date');" name="anchor_calendar" id="anchor_calendar"><img src="/images/icon_cal.gif" alt="calendar" border="0" style="vertical-align: middle"></a>
                    </td>
                </tr>
                <tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Apply filter" name="submit"/></td>
                </tr>
            </tbody>
        </table>
    </form>
    <br />
    {if $tickets}
    {assign var="i" value=0}
    <form method="post" action="">
        {$form_redir}

        <table class="list" width="100%">
            <thead>
                <tr>
                    <td><input type="checkbox" checked="checked" id="checkall"/></td>
                    <td>Subject</td>
                    <td>Customer</td>
                    <td>Assigned</td>
                    <td>Mailbox</td>
                    <td>CC List</td>
                    <td>Date</td>
                    <td>&nbsp;</td>
                </tr>
            </thead>
            {foreach from=$tickets item=ticket}
            <tr>
                <td>
                    {if $ticket.customer_id}
                    <input type="checkbox" class="cb-element" name="msgnos[]" value="{$ticket.msgno}" checked="checked"/>
                    {/if}
                </td>
                <td>
                    <a href="{$i}" class="expand-body"><img src="/images/expand.gif" alt="" id="img_{$i}"/></a>
                    {$ticket.subject}
                </td>
                <td>
                    {$ticket.customer}
                </td>
                <td>{$ticket.assigned}</td>
                <td>{$ticket.mail}</td>
                <td>{$ticket.cc_emails}</td>
                <td>{$ticket.mail_date}</td>
                <td style="width: 80px;">
                    {if !$ticket.customer_id}
                    <a href="{'krifs'|get_link:'ticket_add'}" id="{$i}" class="ticket_add" target="_blank">Create ticket</a>
                    <input type="hidden" value="{$ticket.body|escape}" id="ticket_body_{$i}">
                    <input type="hidden" value="{$ticket.subject|escape}" id="ticket_subject_{$i}"/>
                    {/if}
                </td>
            </tr>
            <tr style="display: none;" id="body_{$i}">
                <td>&nbsp;</td>
                <td colspan="6">
                    <div style="background-color: #EEEEEE; margin: 5px; padding: 10px;">{$ticket.body}</div>
                </td>
            </tr>
            {assign var="i" value=$i+1}
            {/foreach}
        </table><br />
        <input type="submit" value="Create tickets" name="create"/>
    </form>
    {else}
    <span style="background-color: lightgreen; color: #0066cc; padding: 10px; display: inline-block;">No emails for the running filter.</span>
    {/if}
</div>
{/if}
<form action="" method="post" id="ticket_add_form">
    <input type="hidden" name="cl" value="krifs"/>
    <input type="hidden" name="op" value="ticket_add"/>
    <input type="hidden" id="ticket_body" name="body" value=""/>
    <input type="hidden" id="ticket_subject" name="subject" value=""/>
</form>
{literal}
<script type="text/javascript">
    $(document).ready(function() {
        $('.expand-body').click(function() {
            var el = '#body_' + $(this).attr('href');
            var img = '#img_' + $(this).attr('href');
            if($(el).css('display') == 'none') {
                $(el).show();
                $(img).attr('src', '/images/collapse.gif');
            } else {
                $(el).hide();
                $(img).attr('src', '/images/expand.gif');
            }
            return false;
        });

        $('.ticket_add').click(function() {
            var id = $(this).attr('id');
            $('#ticket_body').val($('#ticket_body_' + id).val());
            $('#ticket_subject').val($('#ticket_subject_' + id).val());
            $('#ticket_add_form').submit();
            return false;
        });
    });
</script>
{/literal}