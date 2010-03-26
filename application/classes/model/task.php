<?php
class Model_Task extends ORM {
    protected $_belongs_to = array('user' => array('model' => 'user', 'foreign_key' => 'user_id'));
    protected $_has_many = array('followers' => array('model' => 'user', 'through' => 'follow_task')
                               , 'groups' => array('model' => 'group', 'through' => 'task_group'));

    public static function format_task(&$task, $user) {
        $task->due = self::format_due_out($task->due);
        $task->text = self::format_text_out($task, $user);
    }

    public static function format_text_out($task, $user) {
        $data = $task->text;
        // nofollow links
        $data = mb_ereg_replace('[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]',
            '<a href="\\0" rel="nofollow">\\0</a>', $data);

        // unescape single quotes
        $data = mb_ereg_replace("\\\'", "'", $data);
        return $data;
    }

    public static function get_group($task, $user) {
        $groups = $task->groups
            ->where('user_id', '=', $user->id)
            ->find_all()
            ;
        // assume only one group
        foreach ($groups as $group) {
            return array(
                'id' => $group->id,
                'name' => $group->name,
            );
        }
    }

    public static function format_due_out($date) {
        if(!$date) {
            $date = date(DATE_MYSQL_FORMAT);
        }
        $t_now               = time();
        $t_unix_date         = strtotime($date);

        $span = Date::span($t_unix_date, $t_now);

        $i = 6;
        $span2 = array();
        foreach ($span as $k => $v) {
            $span2[$i] = $v;
            $i--;
        }
        $difference = '';
        if ($t_unix_date < $t_now) {
            $difference = '-';
        }

        for($i = 6; $i >= 0; $i--) {
            if ($span2[$i] > 0) break;
        }
        // $highest = $i;
        if ($i > 5) {
            $difference = 'plan';
        } elseif ($i > 4) {
            $difference = 'mos';
            if ($t_unix_date < $t_now) {
                $difference .= ' ago';
            }
        } elseif ($i > 3) {
            // weeks to days
            $days = $span2[$i] * 7 + $span2[$i-1];
            $difference .= "{$days}:{$span2[$i-2]}";
        } elseif ($i > 2) {
            // days
            //$difference .= $span2[$i] . ':' . $span2[$i-1];
            if ($t_unix_date < $t_now) {
                $difference = 'last ';
            }
            $difference .= date('D', $t_unix_date);
        } elseif ($i > 1) {
            $difference .= $span2[$i];
        } elseif ($i > 1) {
            $difference = 'mins';
            if ($t_unix_date < $t_now) {
                $difference .= ' ago';
            }
        } else {
            $difference = 'now';
        }
        return "{$difference}";
    }

    public static function format_due_in($date) {
        global $PERIODS_NAMES, $PERIODS;
        $date = trim($date);

        foreach ($PERIODS_NAMES as $i => $v) {
            $date = mb_eregi_replace('([0-9]+)([ ]+)?' . $PERIODS[$i] . '$', '\\1 ' . $v . ' ', $date);
            $date = mb_eregi_replace('([0-9]+)([ ]+)?' . $PERIODS[$i] . '([ 0-9])', '\\1 ' . $v . ' \\3', $date);
        }
        $date = mb_eregi_replace('^(-?[0-9]{1,2}):([0-9]{1,2}):([ 0-9]{1,2}):([ 0-9]{1,2})$', ' \\1 years \\2 months \\3 days \\4 hours ', $date);
        $date = mb_eregi_replace('^(-?[0-9]{1,2}):([ 0-9]{1,2}):([ 0-9]{1,2})$', ' \\1 months \\2 days \\3 hours ', $date);
        $date = mb_eregi_replace('^(-?[0-9]{1,2}):([ 0-9]{1,2})$', ' \\1 days \\2 hours ', $date);
        $date = mb_eregi_replace('^(-?[0-9]{1,2})$', '\\1 hours ', $date);
        if (false === ($date = strtotime($date))) {
            return false;
        }

        return date(DATE_MYSQL_FORMAT, $date);
    }
}
