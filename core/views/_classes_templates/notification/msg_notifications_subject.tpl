{strip}
{if $new_notifications_count>=1}
	{assign var="first_notification" value=$new_notifications.0}
{else}
	{assign var="first_notification" value=$old_notifications.0}
{/if}

{assign var="prefix" value=" "}
{if $new_notifications_count>1}
	{if $old_notifications_count > 0} {assign var="prefix" value=" ($new_notifications_count new/$old_notifications_count rep) "}
	{else} {assign var="prefix" value=" ($new_notifications_count new) "}
	{/if}
{else}
	{if $old_notifications_count > 0} {assign var="prefix" value=" ($new_notifications_count new/$old_notifications_count rep) "} {/if}
{/if}

[Keyos]{$prefix}{$first_notification->text}{if $first_notification->object_id}: {$first_notification->object_name}{/if}
{/strip}