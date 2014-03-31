Dear {$recipient->fname} {$recipient->lname},

A scheduled task has been modified.
{if $not_involved}NOTE: You are not involved in this task anymore.
{/if}

Date:           {$task->date_start|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY} - {$task->date_end|date_format:$smarty.const.HOUR_FORMAT_SELECTOR} {if $task->date_start!=$old_task->date_start or $task->date_end!=$old_task->date_end}
   (was: {$old_task->date_start|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY} - {$old_task->date_end|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}){/if} 
Ticket:         #{$task->ticket_id}: {$task->ticket_subject|escape}
Customer:       #{$customer->id}: {$customer->name|escape}
Location:       {assign var="location_id" value=$task->location_id}{$locations_list.$location_id}{if $task->location_id != $old_task->location_id}
   (was: {assign var="location_id" value=$old_task->location_id}{$locations_list.$location_id}){/if} 
Organizer:      {assign var="user_id" value=$task->user_id}{$users_list.$user_id}{if $task->user_id != $old_task->user_id}
   (was: {assign var="user_id" value=$old_task->user_id}{$users_list.$user_id}){/if} 
{if $task->attendees_ids}
Attendees:      {foreach from=$task->attendees_ids item=attendee_id}{$users_list.$attendee_id}
                {/foreach}
{/if}
{if $task->comments}
Comments:
{$task->comments|escape}
{/if}

Created by:	{assign var="created_by" value=$task->created_by_id}{$users_list.$created_by}
{if $modified_by_id}
Modified by:	{$users_list.$modified_by_id}
{/if}


Best regards,
The KeyOS System