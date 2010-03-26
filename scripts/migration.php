<?php
chdir(dirname(__FILE__));

require_once('config.php');

try {
$db_1_link = new PDO("mysql:host=" . DB_OLD_HOST . ";dbname=" . DB_OLD_NAME, DB_OLD_USER, DB_OLD_PASS);
$db_2_link = new PDO("mysql:host=" . DB_KOHANA_HOST . ";dbname=" . DB_KOHANA_NAME, DB_KOHANA_USER, DB_KOHANA_PASS);

/* get tasks */
$query = '
    SELECT task_id, description, due, status, priority, user_id, created_by, trash, lastmodified
    FROM task
';
$statement = $db_1_link->prepare($query);
$statement->execute();
$rows = $statement->fetchAll();

/* migrate tasks */
$statement = $db_2_link->prepare("INSERT IGNORE INTO tasks
    (id, text, user_id, due, priority, status, trash, created, lastmodified)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
$count = 0;
foreach ($rows as $row) {
    $count += $statement->execute(array(
        $row['task_id'],
        $row['description'],
        $row['created_by'],
        $row['due'],
        $row['priority'],
        $row['status'],
        $row['trash'],
        $row['lastmodified'],
        $row['lastmodified'],
    ));
}
echo $count . " tasks migrated \n";

/* share tasks */
$statement = $db_2_link->prepare("INSERT IGNORE INTO follow_task
    (user_id, task_id)
    VALUES (?, ?);");
$count = 0;
foreach ($rows as $row) {
    $count += $statement->execute(array(
        $row['user_id'],
        $row['task_id'],
    ));
}
echo $count . " tasks shared \n";

/* get quotes */
$query = '
    SELECT user_id, login, pass, name_f, name_l
    FROM user
';
$statement = $db_1_link->prepare($query);
$statement->execute();
$rows = $statement->fetchAll();

/* migrate users */
$statement = $db_2_link->prepare("INSERT IGNORE INTO users(id, username, pass, name_f, name_l, email, created, lastmodified)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

$count = 0;
foreach ($rows as $row) {
    $count += $statement->execute(array(
        $row['user_id'],
        $row['login'],
        $row['pass'],
        $row['name_f'],
        $row['name_l'],
        'paul.craciunoiu@gmail.com',
        date('Y-m-d H:i:s', time()),
        date('Y-m-d H:i:s', time()),
    ));
}
echo $count . " users migrated \n";

echo "Done.\n";
} catch (PDOException $e) {
    die($e->getMessage());
}