<?php
/*
 * This file is part of Mocha package.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\System\Library;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provide most used Carbon scenario for the app
 * Carbon lib made public for further advanced usages
 */
class Date
{
    /**
     * @var \Carbon\Carbon
     * @see https://carbon.nesbot.com/docs
     */
    public $carbon;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $param;

    public function __construct(Carbon $carbon, ParameterBag $bag)
    {
        $this->carbon = $carbon;
        $this->param  = $bag;

        $this->carbon->setLocale('en');
        $this->carbon->setTimezone('UTC');

        $this->param->add([
            'tz_user'           => 'Asia/Jakarta', // User displayed timezone
            'date_format'       => 'M d, Y',
            'time_format'       => 'g:i A',
            'datetime_format'   => 'M d, Y g:i A',
            'datetime_sql'      => 'Y-m-d H:i:s',
            'localize_datetime' => [ // Default of language file
                'time'          => 'Time',
                'hour'          => 'Hour',
                'minute'        => 'Minute',
                'simple_date'   => ['Today at', 'Yesterday at', 'Tomorrow at'],
                'full_date'     => [
                                    'year'      => ['1 year', '{{count}} years'],
                                    'month'     => ['1 month', '{{count}} months'],
                                    'week'      => ['1 week', '{{count}} weeks'],
                                    'day'       => ['1 day', '{{count}} days'],
                                    'hour'      => ['1 hour', '{{count}} hours'],
                                    'minute'    => ['1 minute', '{{count}} minutes'],
                                    'ago'       => '{{time}} ago',
                                    'from_now'  => '{{time}} from now',
                                ],
                'day_short'     => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'day_long'      => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'month_short'   => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'month_long'    => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            ]
        ]);

        // Shortcut
        $this->param->add([
            'format'    => [
                'df'    => $this->param->get('date_format'),
                'tf'    => $this->param->get('time_format'),
                'dtf'   => $this->param->get('datetime_format'),
                'dts'   => $this->param->get('datetime_sql'),
            ]
        ]);
    }

    /**
     * @param  string      $format
     * @param  string|null $timezone
     * @param  string|null $modify
     *
     * @return string
     */
    public function now(string $format = 'dtf', string $timezone = null, string $modify = null)
    {
        $timezone = $timezone ?: 'user';

        $dtObject = $this->carbon->now($this->param->get('tz_' . $timezone, $timezone));

        if ($modify) {
            $dtObject->modify($modify);
        }

        return $this->translate($dtObject, $this->param->get('format.' . $format, $format));
    }

    /**
     * Shift from a formatted timezone to another
     * By default it's shift from UTC format dts to user timezone format dtf
     *
     * @param  string     $datetime
     * @param  array|null $options
     *
     * @return string
     */
    public function shift(string $datetime, array $options = null)
    {
        $param = array_merge(
            [
                'from_tz'     => 'UTC',
                'from_format' => 'dts',
                'to_tz'       => 'user',
                'to_format'   => 'dtf',
                'diffHuman'   => false
            ],
            (array)$options
        );

        $datetime = $this->translate(
            $datetime,
            $this->param->get('format.' . $param['from_format'], $param['from_format']),
            true
        );

        $dtObject = $this->carbon
            ->createFromFormat(
                $this->param->get('format.' . $param['from_format'], $param['from_format']),
                $datetime,
                $this->param->get('tz_' . $param['from_tz'], $param['from_tz'])
            )
            ->setTimezone($this->param->get('tz_' . $param['to_tz'], $param['to_tz']));

        if ($param['diffHuman']) {
            return $this->diffHuman(
                $dtObject,
                $this->param->get('format.' . $param['to_format'], $param['to_format'])
            );
        }

        return $this->translate(
            $dtObject,
            $this->param->get('format.' . $param['to_format'], $param['to_format'])
        );
    }

    /**
     * Reverse options of shift() method
     * By default it's shift from user timezone format dtf to UTC format dts
     *
     * @param  string     $datetime
     * @param  array|null $options
     *
     * @return string
     */
    public function shiftToUTC(string $datetime, array $options = null)
    {
        $param = array_merge(
            [
                'from_tz'     => 'user',
                'from_format' => 'dtf',
                'to_tz'       => 'UTC',
                'to_format'   => 'dts',
                'diffHuman'   => false
            ],
            (array)$options
        );

        return $this->shift($datetime, $param);
    }

    /**
     * Human readable datetime format with Multi Language support
     */
    public function translate($dtObject = '', string $format = 'dtf', bool $reverse = false)
    {
        $datetime = is_string($dtObject) ? $dtObject : $dtObject->format($format);
        $format   = $this->param->get('format.' . $format, $format);
        $lang     = $this->param->get('localize_datetime');

        $day_short      = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $day_long       = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $month_short    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $month_long     = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        if ($reverse) { // Turn back to default en
            if (strpos($format, 'D') !== false) {
                $datetime = str_replace($lang['day_short'], $day_short, $datetime);
            } elseif (strpos($format, 'l') !== false) {
                $datetime = str_replace($lang['day_long'], $day_long, $datetime);
            }

            if (strpos($format, 'M') !== false) {
                $datetime = str_replace($lang['month_short'], $month_short, $datetime);
            } elseif (strpos($format, 'F') !== false) {
                $datetime = str_replace($lang['month_long'], $month_long, $datetime);
            }
        } else { // Translate to locale language
            if (strpos($format, 'D') !== false) {
                $datetime = str_replace($day_short, $lang['day_short'], $datetime);
            } elseif (strpos($format, 'l') !== false) {
                $datetime = str_replace($day_long, $lang['day_long'], $datetime);
            }

            if (strpos($format, 'M') !== false) {
                $datetime = str_replace($month_short, $lang['month_short'], $datetime);
            } elseif (strpos($format, 'F') !== false) {
                $datetime = str_replace($month_long, $lang['month_long'], $datetime);
            }
        }

        return $datetime;
    }

    /**
     * Readable date format:
     *     - Today at, Yesterday at and Tomorrow at
     *     - 1 Week ago or 2 Month ago || or "from now"
     *     - date > 3 month return $this->param->get('datetime_format')
     *
     * @param   obj     $dtObject  Date object
     * @param   string  $format       Datetime format
     *
     * @return  string
     */
    public function diffHuman(\DateTime $dtObject, string $format)
    {
        $output = '';
        $lang   = $this->param->get('localize_datetime');
        $year   = $month = $week = $day = $hour = $minute = 0;

        $diffInterval   = $dtObject->diff($this->carbon->now());
        $isFuture       = $diffInterval->invert === 1;

        switch (true) {
            case ($diffInterval->y > 0):
                $year = $diffInterval->y;
                // continue next

            case ($diffInterval->m > 0):
                $month = $diffInterval->m;
                // continue next

            case ($diffInterval->d > 0):
                $day = $diffInterval->d;
                if ($day >= $dtObject::DAYS_PER_WEEK) {
                    $week   = (int) ($day / $dtObject::DAYS_PER_WEEK);
                    $day    = ($day - ($dtObject::DAYS_PER_WEEK * $week));
                }
                // continue next

            case ($diffInterval->h > 0):
                $hour = $diffInterval->h;
                // continue next

            default:
                $minute = $diffInterval->i ?: 1;
                break;
        }

        $_year = $_month = $_week = $_day = $_hour = $_minute = '';
        switch (true) {
            case (!$year && $month >= 1 && $month <= 3): // 2 months 2 weeks ago | 1 months 5 days ago
                $_month = ($month === 1) ? $lang['full_date']['month'][0] : str_replace('{{count}}', $month, $lang['full_date']['month'][1]);
                if ($week) {
                    $_week = ($week === 1) ? $lang['full_date']['week'][0] : str_replace('{{count}}', $week, $lang['full_date']['week'][1]);
                } elseif ($day) {
                    $_day = ($day === 1) ? $lang['full_date']['day'][0] : str_replace('{{count}}', $day, $lang['full_date']['day'][1]);
                }

                $output = $_month . ' ' . $_week . ' ' . $_day;
                $output = $isFuture ? str_replace('{{time}}', $output, $lang['full_date']['from_now']) : str_replace('{{time}}', $output, $lang['full_date']['ago']);
                break;

            case (!$year && !$month && $week): // 2 Weeks 5 days ago | 1 week 1 day ago
                $_week = ($_week === 1) ? $lang['full_date']['week'][0] : str_replace('{{count}}', $week, $lang['full_date']['week'][1]);

                $day = (!$day && $hour) ? 1 : $day; // round under 24 hours as 1 day
                if ($day) {
                    $_day = ($day === 1) ? $lang['full_date']['day'][0] : str_replace('{{count}}', $day, $lang['full_date']['day'][1]);
                }

                $output = $_week . ' ' . $_day;
                $output = $isFuture ? str_replace('{{time}}', $output, $lang['full_date']['from_now']) : str_replace('{{time}}', $output, $lang['full_date']['ago']);
                break;

            case (!$year && !$month && !$week && $day && $day <= 3): // 2 days 5 hours ago | 1 day 1 hour ago
                $_day = ($_day === 1) ? $lang['full_date']['day'][0] : str_replace('{{count}}', $day, $lang['full_date']['day'][1]);

                $hour = (!$hour && $minute) ? 1 : $hour; // round under 60 minutes as 1 hour
                if ($hour) {
                    $_hour = ($hour === 1) ? $lang['full_date']['hour'][0] : str_replace('{{count}}', $hour, $lang['full_date']['hour'][1]);
                }

                $output = $_day . ' ' . $_hour;
                $output = $isFuture ? str_replace('{{time}}', $output, $lang['full_date']['from_now']) : str_replace('{{time}}', $output, $lang['full_date']['ago']);
                break;

            case (!$year && !$month && !$week && !$day && ($hour || $minute)): // 2 hours 15 minutes ago | 1 hour 1 minute ago
                if ($hour) {
                    $_hour = ($hour === 1) ? $lang['full_date']['hour'][0] : str_replace('{{count}}', $hour, $lang['full_date']['hour'][1]);
                }
                $_minute = ($minute === 1) ? $lang['full_date']['minute'][0] : str_replace('{{count}}', $minute, $lang['full_date']['minute'][1]);

                $output = $_hour . ' ' . $_minute;
                $output = $isFuture ? str_replace('{{time}}', $output, $lang['full_date']['from_now']) : str_replace('{{time}}', $output, $lang['full_date']['ago']);
                break;

            default:
                $output = $this->translate($dtObject, $format);
                break;
        }

        return $output;
    }

    /**
     * Offset time to UTC
     *
     * @param  string $timezone
     *
     * @return string
     */
    public function gmtOffset($timezone = 'user')
    {
        $result   = $this->carbon->now($this->param->get('tz_' . $timezone, $timezone))->offsetHours;

        return (string)($result > 0 ? '+' . $result : $result);
    }

    /*
     * PHP datetime to SQL format
     *
     * - http://php.net/manual/en/function.date.php
     * - http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
     */
    public function toSqlFormat($format = 'dtf')
    {
        $symbols = [
            // Day
            'd' => '%d',
            'D' => '%a',
            'j' => '%e',
            'l' => '%W',
            'w' => '%w',
            'z' => '%j',
            // Month
            'F' => '%M',
            'M' => '%b',
            'm' => '%m',
            'n' => '%c',
            // Year
            'Y' => '%Y',
            'y' => '%y',

            // Time
            'a' => '%p',
            'A' => '%p',
            'g' => '%l',
            'G' => '%k',
            'h' => '%h',
            'H' => '%H',
            'i' => '%i',
            's' => '%s',
        ];

        return strtr($this->param->get('format.' . $format, $format), $symbols);
    }

    /*
     * PHP datetime to jQuery UI format
     *
     * - http://php.net/manual/en/function.date.php
     * - http://api.jqueryui.com/datepicker/#utility-formatDate
     */
    public function tojQueryUIFormat($format = 'dtf')
    {
        $symbols = [
            // Day
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'z' => 'o',
            // Month
            'F' => 'MM',
            'M' => 'M',
            'm' => 'mm',
            'n' => 'm',
            // Year
            'Y' => 'yy',
            'y' => 'y',

            // http://trentrichardson.com/examples/timepicker/#tp-formatting
            // Time
            'a' => 'tt',
            'A' => 'TT',
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'c' => 'Z'
        ];

        return strtr($this->param->get('format.' . $format, $format), $symbols);
    }
}
