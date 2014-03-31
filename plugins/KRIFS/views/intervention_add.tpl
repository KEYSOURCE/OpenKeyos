{assign var="paging_titles" value="KRIFS, Add Intervention Report"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js"></script>

<h1>Add Intervention Report</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="4">Intervention Report</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%">Subject:</td>
		<td width="85%">
			<input type="text" name="intervention[subject]" value="{$intervention->subject|escape}" size="80" />
		</td>
	</tr>
	<tr>
		<td>Customer:</td>
		<td>
			<select name="intervention[customer_id]">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$intervention->customer_id}
				
			</select>
		</td>
	</tr>
</table>
<p/>

<!-- IE workaround -->
<input type="text" name="workaround" value="" style="display:none;" />

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />
</form>