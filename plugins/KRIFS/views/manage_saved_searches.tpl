{assign var="paging_titles" value="Krifs, Saved Searches"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Saved Searches</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="20%">Name</td>
		<td>Owner</td>
		<td>Priv.</td>
		<td>Edit</td>
		<td colspan="5">Criteria</td>
	</tr>
	<tr>
		<td colspan="4"> </td>
		<td>Keywords</td>
		<td>User</td>
		<td>Customer</td>
		<td>Status</td>
		<td>Type</td>
	</tr>
	</thead>
	
	{foreach from=$searches item=searches_list key=name}
	<tr>
		<td colspan="9"><h3>[ {$name} ]</h3></td>
	</tr>
	{foreach from=$searches_list item=search}
	<tr>
		<td>
            {assign var="p" value="load_search:"|cat:"1"|cat:",search_id:"|cat:$search->id}
			<a href="{'krifs'|get_link:'manage_tickets_submit':$p:'template'}"><b>{$search->name}</b></a><br>
			
			{if $name=='Favorites'}
                {assign var="p" value="remove_id:"|cat:$search->id}
				[<a href="{'krifs'|get_link:'manage_saved_searches_submit':$p:'template'}">Remove from Favorites</a>]
			{else}
                {assign var="p" value="add_id:"|cat:$search->id}
				[<a href="{'krifs'|get_link:'manage_saved_searches_submit':$p:'template'}">Add to Favorites</a>]
			{/if}
		</td>
		<td>{$search->user->login}</td>
		<td>
			{if $search->private}Y{else}N{/if}
		</td>
		<td nowrap>
            {assign var="p" value="search_id:"|cat:$search->id}
			<a href="{'krifs'|get_link:'saved_search_edit':$p:'template'}">Details &#0187;</a><br>
            {assign var="p" value="edit_search:"|cat:"1"|cat:",search_id:"|cat:$search->id}
			<a href="{'krifs'|get_link:'manage_tickets_submit':$p:'template'}">Criteria &#0187;</a><br>
		</td>
					
					<td>
						{if $search->filter.keywords}
							<b>
							{if $search->filter.keywords_phrase}By phrase,
							{else}
								By word ({if $search->filter.keywords_and}AND{else}OR{/if})
							{/if}
							<br>
							In:
							{if $search->filter.in_subject}subject, {/if}
							{if $search->filter.in_comments}comments{/if}
							</b><br>
							{$search->filter.keywords}
						{else}
							[None]
						{/if}
					</td>
					
					<td>
						{if $search->filter.user_id}
							<b>
							{if $search->filter.view==2}Assigned to
							{elseif $search->filter.view==3}Owned by
							{elseif $search->filter.view==4}Created by
							{else}[Any involvment]
							{/if}
							:</b><br>
							
							{foreach from=$search->filter.user_id item=user_id name="list"}
								{$users_list.$user_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
					
					<td>
						{if $search->filter.customer_id}
							{foreach from=$search->filter.customer_id item=customer_id name="list"}
								{$customers_list.$customer_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
					
					<td>
						{if $search->filter.status}
							{foreach from=$search->filter.status item=status_id name="list"}
								{$TICKET_STATUSES.$status_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
					
					<td>
						{if $search->filter.type}
							{foreach from=$search->filter.type item=type_id name="list"}
								{$TICKET_TYPES.$type_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
			
			<!--
			<table class="no_borders">
				<tr>
					<td>Keywords:</td>
					<td>
						{if $search->filter.keywords}
							<b>
							{if $search->filter.keywords_phrase}By phrase,
							{else}
								By word ({if $search->filter.keywords_and}AND{else}OR{/if})
							{/if}
							<br>
							In:
							{if $search->filter.in_subject}subject, {/if}
							{if $search->filter.in_comments}comments{/if}
							</b><br>
							{$search->filter.keywords}
						{else}
							[None]
						{/if}
					</td>
				</tr>
				<tr>
					<td>User:</td>
					<td>
						{if $search->filter.user_id}
							<b>
							{if $search->filter.view==2}Assigned to
							{elseif $search->filter.view==3}Owned by
							{elseif $search->filter.view==4}Created by
							{else}[Any involvment]
							{/if}
							:</b><br>
							
							{foreach from=$search->filter.user_id item=user_id name="list"}
								{$users_list.$user_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
				</tr>
				<tr>
					<td>Customer:</td>
					<td>
						{if $search->filter.customer_id}
							{foreach from=$search->filter.customer_id item=customer_id name="list"}
								{$customers_list.$customer_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
				</tr>
				<tr>
					<td>Status:</td>
					<td>
						{if $search->filter.status}
							{foreach from=$search->filter.status item=status_id name="list"}
								{$TICKET_STATUSES.$status_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
				</tr>
				<tr>
					<td>Type:</td>
					<td>
						{if $search->filter.type}
							{foreach from=$search->filter.type item=type_id name="list"}
								{$TICKET_TYPES.$type_id}{if !$smarty.foreach.list.last},{/if}
							{/foreach}
						{else}
							[All]
						{/if}
					</td>
				</tr>
			</table>
			
		</td>-->
	</tr>
	{foreachelse}
	<tr>
		<td colspan="9">
			[No saved searches]
		</td>
	</tr>
	{/foreach}
	{/foreach}
</table>
<p>
