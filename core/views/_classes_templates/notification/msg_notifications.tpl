Dear {$recipient->fname} {$recipient->lname},

Below you have a summary of your notifications. To view all your current
notifications, go to:
https://{$base_url}/?cl=home&op=notifications


[New notifications]
------------------------------
{foreach from=$new_notifications item=notification}

ID: {$notification->id}
Subject: {$notification->text}
Level: {assign var="level" value=$notification->level}{$ALERT_NAMES.$level}
{if $notification->object_id}Linked to: {$notification->object_name} {/if} 
{if $notification->object_url}URL: https://{$notification->object_url}{/if}
{if $notification->others}

Related notifications:
{foreach from=$notification->others item=sub_notif}
  - [ID: {$sub_notif->id}] {$sub_notif->text}
{/foreach}
{/if}

{foreachelse}
(No new notifications)
{/foreach}

[Repeated notifications]
------------------------------
{foreach from=$old_notifications item=notification}

ID: {$notification->id}
Subject: {$notification->text}
Level: {assign var="level" value=$notification->level}{$ALERT_NAMES.$level}
{if $notification->object_id}Linked to: {$notification->object_name} {/if} 
{if $notification->object_url}URL: https://{$notification->object_url}{/if}
{if $notification->others}

Related notifications:
{foreach from=$notification->others item=sub_notif}
  - [ID: {$sub_notif->id}] {$sub_notif->text}
{/foreach}
{/if}
 
{foreachelse}
(No old notifications)
{/foreach}


Best regards,
The KeyOS System
