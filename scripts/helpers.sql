-- This file contains some helper queries to run _in case of emergency_ :-)
-- i.e. when the database is corrupt or inconsistent, these may help.

-- Compare actual with correct number of tasks per group
SELECT
    group_id, groups.num_tasks AS num_tasks, COUNT(*) as correct_num_tasks
FROM
    tasks, groups
WHERE
    tasks.group_id = groups.id GROUP BY group_id;

-- Fix if there is a difference in the above
UPDATE
    groups
SET
    num_tasks = (SELECT
                    COUNT(*) AS correct_num_tasks
                FROM
                    tasks
                WHERE tasks.group_id = groups.id);
