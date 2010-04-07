<?php
if (strlen($user->name) > 0) {
    $message_name = $user->name;
} else {
    $message_name = $user->username;
}

$subject = 'Invitation to ' . SITE_NAME . ' from ' . $user->username;

$message = 'Hi,

' . $message_name . ' has invited you to ' . SITE_NAME . '!

Click on the link below, or copy and paste it in your browser to register an account on ' . SITE_NAME . '.

' . URL::site('user/register', 'https')
         . '?code=' . $invitation->code . '

Note: This invitation expires in a week.
---

Regards,
The Tasklist Team
';


$additional_headers = "From: Tasklist Team <tasklist@craciunoiu.net>\r\n" .
    "Reply-To: Tasklist Team <tasklist@craciunoiu.net>\r\n";

$email = $_POST['email'];
Kohana::$log->add('user_invite',
    "Invitation from {$user->username} ({$user->id}). Email sent to: {$email}");
