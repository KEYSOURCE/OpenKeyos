{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Managing Since"}
{assign var="paging_urls" value="/kawacs, /kawacs&op=manage_computers, "|cat:"kawacs"|get_link:"computer_view":$p:"template"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Set Managing Since Date</h1>

<font class="error">{$error_msg}</font>

<p>Below you can set the date since when this computer is considered to be managed through Keyos.</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="120">Computer:</td>
		<td class="post_highlight">#{$computer->id}: {$computer->netbios_name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Customer:</td>
		<td class="post_highlight">{$customer->name|escape} ({$customer->id})</td>
	</tr>
	<tr>
		<td class="highlight">Managing since:</td>
		<td class="post_highlight">
			<input type="text" size="12" name="date_created"
				{if $computer->date_created}value="{$computer->date_created|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"{/if}
			/>
			{literal}
			<a href="#" onclick="showCalendarSelector('frm_t', 'date_created'); return false;" name="anchor_calendar" id="anchor_calendar"
				><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
			{/literal}
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
