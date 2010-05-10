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
    SELECT id FROM
        tasks
    WHERE
        trash = 1
    AND lastmodified < DATE_SUB(CURRENT_DATE(), $interval)
    ";
$statement = $db_link->prepare($q);
$statement->execute();
$rows = $statement->fetchAll();

$task_ids = array();
foreach ($rows as $row) {
    $task_ids[] = $row['id'];
}

$tasks = count($task_ids);
$follow_tasks = 0;
if ($task_ids) {
    $ids_string = implode(',', $task_ids);

    $q = "
        DELETE FROM
            tasks
        WHERE
            id IN ($ids_string)
        ;";
    $statement = $db_link->prepare($q);
    $count = $statement->execute();
    $tasks = $statement->rowCount();

    $q = "
        DELETE FROM
            follow_task
        WHERE
            id IN ($ids_string)
        ;";

    $statement = $db_link->prepare($q);
    $count = $statement->execute();
    $follow_tasks = $statement->rowCount();
}

$q = "
    UPDATE
        groups
    SET
        num_tasks = (SELECT
                        COUNT(*) AS correct_num_tasks
                    FROM
                        tasks
                    WHERE tasks.group_id = groups.id);";
$statement = $db_link->prepare($q);
$count = $statement->execute();
$groups_updated = $statement->rowCount();

$q = "DELETE FROM
        groups
        WHERE
        num_tasks = 0;";
$statement = $db_link->prepare($q);
$count = $statement->execute();
$groups_erased = $statement->rowCount();

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
        "$follow_tasks follow_task erased.\n" .
        "$groups_updated groups updated.\n" .
        "$groups_erased groups erased.\n" .
        "$invites invitations erased.\n" .
        "$invites notifications to share erased.\n"
    ;
} else {
    echo "No tasks erased.\n";
}
} catch (PDOException $e) {
    die($e->getMessage());
}
