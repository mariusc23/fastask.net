<?php
/**
 * Model for task, central piece of fastask.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 */
class Model_Task extends ORM
{
    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
        'group' => array('model' => 'group', 'foreign_key' => 'group_id'),
    );
    protected $_has_many = array(
        'followers' => array('model' => 'user', 'through' => 'follow_task'),
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->_object['due_out'] = $this->_object['due'];
    }

    /**
     * Wrapper for format_due_out and format_text_out.
     * Calls both and modifies input.
     *
     * @param object &$task ORM task object
     * @param object $user ORM user object
     */
    public static function format_task(&$task, $user)
    {
        $task->due_out = self::format_due_out($task);
        $task->text = self::format_text_out($task, $user);
    }

    /**
     * Formats the text of a task.
     * Among other things, it linkifies URLs.
     *
     * @param object $task ORM task object
     * @param object $user ORM user object
     *
     * @return string formatted text
     */
    public static function format_text_out($task, $user)
    {
        $data = $task->text;
        // nofollow links
        $data = mb_ereg_replace('[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]',
            '<a href="\\0" rel="nofollow">\\0</a>', $data);

        $data = mb_ereg_replace("\\\'", "'", $data);
        return $data;
    }

    /**
     * Formats the due date of a task.
     *
     * @param object $task ORM task object
     *
     * @return string formatted due date
     */
    public static function format_due_out($task)
    {
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

        for ($i = 6; $i >= 0; $i--) {
            if ($span2[$i] > 0) {
                break;
            }
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

    /**
     * Parse a raw due date and turn it into a timestamp. Uses a bunch of
     * regexes to parse special formats that strtotime can't handle.
     * Returns false if it fails.
     *
     * @param string $date a string representing a date
     *
     * @return int timestamp on success, false on failure
     */
    public static function format_due_in($date)
    {
        global $PERIODS_NAMES, $PERIODS;
        $date = trim($date);

        foreach ($PERIODS_NAMES as $i => $v) {
            $date = mb_eregi_replace('([0-9]+)([ ]+)?' .
                    $PERIODS[$i] . '$', '\\1 ' . $v . ' ', $date);
            $date = mb_eregi_replace('([0-9]+)([ ]+)?' .
                    $PERIODS[$i] . '([ 0-9])', '\\1 ' . $v . ' \\3', $date);
        }
        $date = mb_eregi_replace('^(-?[0-9]{1,2}): # years
                                   ([0-9]{1,2}):   # months
                                   ([ 0-9]{1,2}):  # days
                                   ([ 0-9]{1,2})   # hours
                                   $',
                                 ' \\1 years \\2 months \\3 days \\4 hours ',
                                 $date, 'x');
        $date = mb_eregi_replace('^(-?[0-9]{1,2}): # months
                                   ([ 0-9]{1,2}):  # days
                                   ([ 0-9]{1,2})   # hours
                                   $',
                                 ' \\1 months \\2 days \\3 hours ',
                                 $date, 'x');
        $date = mb_eregi_replace('^(-?[0-9]{1,2}): # days
                                   ([ 0-9]{1,2})   # hours
                                   $', ' \\1 days \\2 hours ', $date, 'x');
        $date = mb_eregi_replace('^(-?[0-9]{1,2})$', ' \\1 days ', $date);

        if (false === ($date = strtotime($date))) {
            return false;
        }
        return $date;
    }
}
