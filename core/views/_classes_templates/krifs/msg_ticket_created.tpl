{if $recipient!=null}
	{assign var="is_usr_recip" value=1}
{else}
	{assign var="is_usr_recip" value=0}
{/if}
{if $is_usr_recip==1}Dear {$recipient->fname} {$recipient->lname},{/if}

{if $is_reminder}
This is a reminder that the following ticket has been created:
{else}
A new ticket has been created:
{/if}

ID: {$notification->linked_object->id}
Subject: {$notification->linked_object->subject}
Customer: {$notification->linked_object->customer->name}
Status: {$notification->linked_object->status}
URL: https://{$notification->object_url}

{assign var="last_entry_index" value=$notification->linked_object->last_entry_index}
{assign var="last_entry" value=$notification->linked_object->details.$last_entry_index}
{if $is_usr_recip==1}
{if !$recipient->is_customer_user()}
Created by: {assign var="user_id" value=$last_entry->user_id}{$users_list.$user_id}
Assigned to: {assign var="assigned_id" value=$last_entry->assigned_id}{$users_list.$assigned_id}

{/if}
{/if}
{if $last_entry->comments}
Comments: 
{$last_entry->comments}
{/if}

Best regards,
The KeyOS System
