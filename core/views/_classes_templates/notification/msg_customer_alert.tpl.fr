Dear {$recipient->fname} {$recipient->lname},

{if !$recipient->is_customer_user()}
---------------------------------------------------------------------
WARNING! This message should have been sent to a customer user, but
there are no recipients available for this customer.
---------------------------------------------------------------------
{/if}

{if $notification->object_class==$smarty.const.NOTIF_OBJ_CLASS_COMPUTER}
This notification is regarding the computer:
{$notification->object_name}
{/if}

{$alert->message}

---------------------------------------------------------------------
This is an automated message, please do not reply.

Best regards,
The KeyOS System
