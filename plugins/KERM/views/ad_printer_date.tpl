{assign var="computer_id" value=$ad_printer->computer_id}
{assign var="nrc" value=$ad_printer->nrc}
{assign var="paging_titles" value="KERM, AD Printers, View AD Printer, AD Printer Date"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_printers, /?cl=kerm&op=ad_printer_view&computer_id=$computer_id&nrc=$nrc"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Set AD Printer Date: {$ad_printer->name|escape}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

Specify below the date since when this AD Printer should be considered as managed in Keyos:
<p/>
Date: 
<input type="text" size="12" name="date_created"
	{if $ad_printer->date_created}value="{$ad_printer->date_created|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"{/if}
/>
{literal}
<a href="#" onclick="showCalendarSelector('frm_t', 'date_created'); return false;" name="anchor_calendar" id="anchor_calendar"
	><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
{/literal}
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
