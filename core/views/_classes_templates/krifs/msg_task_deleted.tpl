Dear {$recipient->fname} {$recipient->lname},

A that was scheduled for you has been deleted:

Date:		{$task->date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}
Ticket:	#{$task->ticket_id}: {$task->ticket_subject|escape}
Customer:	#{$customer->id}: {$customer->name|escape}
Location:	{assign var="location_id" value=$task->location_id}{$locations_list.$location_id}
{if $task->comments}
Comments:
{$task->comments|escape}
{/if}

Created by:	{assign var="created_by" value=$task->created_by_id}{$users_list.$created_by}
{if $modified_by_id}
Deleted by:	{$users_list.$modified_by_id}
{/if}


Best regards,
The KeyOS System