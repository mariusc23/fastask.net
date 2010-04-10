<?php
if (strlen($this->user->name) > 0) {
    $message_from = $this->user->name;
} else {
    $message_from = $this->user->username;
}
if (strlen($user->name) > 0) {
    $message_to = $user->name;
} else {
    $message_to = $user->username;
}


$subject = 'Request to share from ' . $message_from;

$message = 'Hi ' . $message_to . ',

' . $message_from . ' has invited you to share tasks with him!

If you accept their request, you will be able to assign tasks to them, and they will be able to assign tasks to you. The assignments (2nd tab) and command center (3rd tab) will keep tasks created by them or assigned to them separate from your own.

Accept their request: ' . URL::site('user/share')
         . '?code=' . $notification->code . '&accept=1

Block future requests from this user: ' . URL::site('user/share')
         . '?code=' . $notification->code . '&block=1

You can always unblock the user by going to the accept URL.


Note: This invitation expires in a week.
---

Regards,
The ' . SITE_NAME . ' Team
';


$additional_headers = "From: " . SITE_NAME . " Team <fastask@craciunoiu.net>\r\n" .
    "Reply-To: " . SITE_NAME . " Team <fastask@craciunoiu.net>\r\n";

Kohana::$log->add('user_share',
    "Request to share from {$this->user->username} ({$this->user->id}), sent to: {$user->username} ({$user->email})");
