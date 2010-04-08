<?php
chdir(dirname(__FILE__));

require_once('../application/config/config.php');
require_once('config.php');

try {
if (defined('CRON_REPEAT_INTERVAL')) {
    $interval = CRON_REPEAT_INTERVAL;
} else {
    $interval = 'INTERVAL 30 DAY';
}

$invite_interval = 'INTERVAL 7 DAY';

$db_link = new PDO("mysql:host=" . DB_KOHANA_HOST . ";dbname=" . DB_KOHANA_NAME, DB_KOHANA_USER, DB_KOHANA_PASS);

$q = "
    DELETE FROM
        tasks
    WHERE
        trash = 1
    AND lastmodified < DATE_SUB(CURRENT_DATE(), $interval)
    ;";
$statement = $db_link->prepare($q);
$count = $statement->execute();

$tasks = $statement->rowCount();

$q = "
    DELETE FROM
        invitations
    WHERE
        lastmodified < DATE_SUB(CURRENT_DATE(), $invite_interval)
    ;";
$statement = $db_link->prepare($q);
$count = $statement->execute();
$invites = $statement->rowCount();


$q = "
    DELETE FROM
        notifications
    WHERE
        lastmodified < DATE_SUB(CURRENT_DATE(), $invite_interval)
    AND type = " . NOTIFICATION_USER_SHARE . "
    ;";
$statement = $db_link->prepare($q);
$count = $statement->execute();
$notifications = $statement->rowCount();

if ($count) {
    echo "Cron ran successfully.\n" .
        "$tasks tasks erased.\n" .
        "$invites invitations erased.\n" .
        "$invites notifications to share erased.\n"
    ;
} else {
    echo "No tasks erased.\n";
}
} catch (PDOException $e) {
    die($e->getMessage());
}
