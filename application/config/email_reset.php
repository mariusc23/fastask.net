<?php
$message = 'Hi ' . $user->username . ',

Click on the link below, or copy and paste it in your browser to change your password on ' . SITE_NAME . '.

' . URL::site('user/reset', 'https')
         . '?code=' . $notification->code . '

---

Regards,
the Tasklist team
';

?>
