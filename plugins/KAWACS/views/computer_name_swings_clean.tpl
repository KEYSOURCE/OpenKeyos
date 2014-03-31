{assign var="paging_titles" value="KAWACS, Computers Reporting Issues, Clean Name Swings"}
{assign var="paging_urls" value="/kawacs, /kawacs/reporting_issues"}
{include file="paging.html"}

<h1>Clear Name Swings</h1>

<p class="error">{$error_msg}</p>

<p>
Here you can clear from the logs the invalid names for a "name swinger" computer.<br/>
Select below the name which you want to keep. All other names will be removed.
</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="120">Computer:</td>
		<td class="post_highlight">
			#{$computer->id}: {$computer->netbios_name|escape}
		</td>
	</tr>
	</thead>
	<tr>
		<td class="highlight">Name to keep:</td>
		<td class="post_highlight">
			{foreach from=$names item=name}
				<input type="radio" name="keep_name" value="{$name|escape}"
				{if $name==$computer->netbios_name}checked{/if}
				/> {$name|escape}<br/>
			{/foreach}
		</td>
	</tr>

</table>
<p/>

<input type="submit" name="save" value="Clear logs" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>