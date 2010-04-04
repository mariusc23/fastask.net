<?php
if (strlen($user->name) > 0) {
    $message_name = $user->name;
} else {
    $message_name = $user->username;
}
$message = 'Hi ' . $message_name . ',

Someone, presumably you, requested a password reset for your account on Tasklist. If you do not wish to reset your password, ignore this email, and your password will remain unchanged.

Click on the link below, or copy and paste it in your browser to change your password on ' . SITE_NAME . '.

' . URL::site('user/reset', 'https')
         . '?code=' . $notification->code . '

---

Regards,
The Tasklist Team
';

$additional_headers = "From: Tasklist Team <tasklist@craciunoiu.net>\r\n" .
    "Reply-To: Tasklist Team <tasklist@craciunoiu.net>\r\n";

Kohana::$log->add('password_reset_email',
    "Reset password for {$user->username}. Email sent to: {$user->email}\n");
?>
