<?php
class Model_Task extends ORM {
    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
        'group' => array('model' => 'group', 'foreign_key' => 'group_id'),
    );
    protected $_has_many = array(
        'followers' => array('model' => 'user', 'through' => 'follow_task'),
    );

    public function __construct($id = NULL) {
        parent::__construct($id);
        $this->_object['due_out'] = $this->_object['due'];
    }

    public static function format_task(&$task, $user) {
        $task->due_out = self::format_due_out($task);
        $task->text = self::format_text_out($task, $user);
    }

    public static function format_text_out($task, $user) {
        $data = $task->text;
        // nofollow links
        $data = mb_ereg_replace('[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]',
            '<a href="\\0" rel="nofollow">\\0</a>', $data);

        $data = mb_ereg_replace("\\\'", "'", $data);
        return $data;
    }

    public static function format_due_out($task) {
        if ($task->planned) {
            return 'plan';
        }
        $t_unix_date = $task->due;
        $t_now = time();
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
            // output: {years}yr{months}mo
            $difference .= "{$span2[$i]}yr{$span2[$i-1]}mo";
        } elseif ($i > 4) {
            // output: {months}mo
            $difference .= "{$span2[$i]}mo";
        } elseif ($i > 3) {
            // weeks
            $days = $span2[$i] * 7 + $span2[$i-1];
            // output: {days}:{hours}
            $difference .= "{$days}:{$span2[$i-2]}";
        } elseif ($i > 2) {
            // day of the week
            if ($t_unix_date < $t_now) {
                $difference = 'last ';
            }
            // [last] {DOTW}
            $difference .= date('D', $t_unix_date);
        } elseif ($i > 1) {
            // output: {hours}
            $difference .= "{$span2[$i]}h";
        } elseif ($i > 0) {
            // output: mins [ago]
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

        return $date;
    }
}
