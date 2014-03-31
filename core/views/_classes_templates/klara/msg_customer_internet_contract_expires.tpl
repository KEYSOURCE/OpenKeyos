Dear {$recipient->fname} {$recipient->lname},

This is an expiration notification for the following Internet Contract:

Provider: {$notification->linked_object->get_provider_name()}
Contract: {$notification->linked_object->get_provider_contract_name()}
End date: {$notification->linked_object->end_date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}
Status: {$notification->linked_object->get_expiration_string()}

For details on how to proceed, please contact Keysource.


Best regards,
The KeyOS System
