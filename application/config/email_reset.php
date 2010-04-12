<?php
if (strlen($user->name) > 0) {
    $message_name = $user->name;
} else {
    $message_name = $user->username;
}

$subject = SITE_NAME . SITE_SEPARATOR
    . $user->username . SITE_SEPARATOR
    . 'password reset code';

$message = 'Hi ' . $message_name . ',

Someone, presumably you, requested a password reset for your account on ' . SITE_NAME . '. If you do not wish to reset your password, ignore this email, and your password will remain unchanged.

Click on the link below, or copy and paste it in your browser to change your password on ' . SITE_NAME . '.

' . URL::site('user/reset', TRUE)
         . '?code=' . $notification->code . '

---

Regards,
The ' . SITE_NAME . ' Team
';

$additional_headers = "From: " . SITE_NAME . " Team <fastask@craciunoiu.net>\r\n" .
    "Reply-To: " . SITE_NAME . " Team <fastask@craciunoiu.net>\r\n";

Kohana::$log->add('password_reset_email',
    "Reset password for {$user->username}. Email sent to: {$user->email}");
?>
