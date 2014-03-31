Dear {$recipient->fname} {$recipient->lname},

This is an expiration notification for the following Internet Contract:

Customer: #{$notification->linked_object->customer_id}: {$notification->linked_object->get_customer_name()}
Provider: {$notification->linked_object->get_provider_name()}
Contract: {$notification->linked_object->get_provider_contract_name()}
End date: {$notification->linked_object->end_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
Status: {$notification->linked_object->get_expiration_string()}

{if $notification->linked_object->is_keysource_managed}
NOTE: Since the contract is managed by Keysource, the customer
was NOT notified.
{else} 
{if $notification->linked_object->has_customer_recipient()}
NOTE: The customer has been notified as well about the expiration.
{else}
WARNING: No customer user was found for sending the notification.
{/if}
{/if}

To view the contract details, please click here:
{$base_url}/?cl=klara&op=customer_internet_contract_edit&id={$notification->linked_object->id}


Best regards,
The KeyOS System
