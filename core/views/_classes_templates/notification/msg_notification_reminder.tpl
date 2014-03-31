Dear {$recipient->fname} {$recipient->lname},

This is a reminder for the following notifications:

Subject: {$notification->text}
Level: {assign var="level" value=$notification->level}{$ALERT_NAMES.$level}
Linked to: {if $notification->object_id}{$notification->object_name}{else}n/a{/if} 
{if $notification->object_url}URL: {$notification->object_url}{/if}



Best regards,
The KeyOS System
