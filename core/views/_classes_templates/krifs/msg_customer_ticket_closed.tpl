{if $recipient!=null}Dear {$recipient->fname} {$recipient->lname},{/if}

{if $is_reminder}
This is a reminder that the following ticket has been closed:
{else}
The following ticket has been closed:
{/if}

ID: {$notification->linked_object->id}
Subject: {$notification->linked_object->subject}

URL: https://{$base_url}/?cl=customer_krifs&op=ticket_edit&id={$notification->linked_object->id}

{assign var="last_entry_index" value=$notification->linked_object->last_entry_index}
{assign var="last_entry" value=$notification->linked_object->details.$last_entry_index}
{if $last_entry->comments}
Comments: 
{$last_entry->comments}
{/if}


Best regards,
The KeyOS System
