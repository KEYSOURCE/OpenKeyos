Dear {$recipient->fname} {$recipient->lname},

A new task has been scheduled for you:

Date:           {$task->date_start|date_format:$smarty.const.DATE_TIME_FORMAT_SELECTOR} - {$task->date_end|date_format:$smarty.const.HOUR_FORMAT_SELECTOR}
Ticket:         #{$task->ticket_id}: {$task->ticket_subject|escape}
Customer:       #{$customer->id}: {$customer->name|escape}
Location:       {assign var="location_id" value=$task->location_id}{$locations_list.$location_id}
Organizer:      {assign var="user_id" value=$task->user_id}{$users_list.$user_id}
{if $task->attendees_ids}
Attendees:      {foreach from=$task->attendees_ids item=attendee_id}{$users_list.$attendee_id}
                {/foreach}
{/if}
{if $task->comments}
Comments:
{$task->comments|escape}
{/if}

Created by:     {assign var="created_by" value=$task->created_by_id}{$users_list.$created_by}


Best regards,
The KeyOS System