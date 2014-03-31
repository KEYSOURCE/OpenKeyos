{assign var="paging_titles" value="Technical Support, Create Ticket"}
{assign var="paging_urls" value="/?cl=customer_krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js"></script>
<script language="JavaScript" src="/javascript/tiny_mce/tiny_mce.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
{literal}
tinyMCE.init
(
    {
        //General options
        mode: "exact",
        elements : "ticket_detail[comments]",
        theme: "advanced",
        plugins: "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlextras,imagemanager",
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect", 
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor", 
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen", 
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage", 
        theme_advanced_toolbar_location : "top", 
        theme_advanced_toolbar_align : "left", 
        theme_advanced_statusbar_location : "bottom", 
        theme_advanced_resizing : true
    }
);
{/literal}
</script>

<h1>Create Ticket</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Ticket information</td>
	</tr>
	</thead>

	<tr>
		<td>Subject: </td>
		<td><input type="text" name="ticket[subject]" value="{$ticket_detail->subject}" size="100"></td>
	</tr>
	{if $assigned_customers_count > 1}
	<tr>
		<td>Customer: </td>
		<td>
			<select name="ticket[customer_id]">
			{html_options options=$customers_list}
			</select>
		</td>
	</tr>
	{else}
	<tr>
		<td>Customer: </td>
		<td>
		{foreach from=$customers_list item=customer}
		{$customer}
		{/foreach}
		</td>
	</tr>	
	{/if}
	<tr>
		<td>Priority: </td>
		<td>
			<select name="ticket[priority]">
			{html_options options=$TICKET_PRIORITIES selected=$ticket->priority}
			</select>
		</td>
	</tr>
	<tr>
		<td>Deadline: </td>
		<td>
			<input type="text" size="12" name="ticket[deadline]" value="{$ticket->deadline|date_format:$smarty.const.DATE_FORMAT_SELECTOR}">
			{literal}
			<a HREF="#" onClick="showCalendarSelector('frm', 'ticket[deadline]'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
	</tr>
	
	<tr>
		<td>Comments: </td>
		<td colspan="3">
			<textarea name="ticket_detail[comments]" rows="10" cols="100">{$ticket_detail->comments|escape}</textarea>
		</td>
	</tr>
	
	
	
</table>

<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">

</form>
