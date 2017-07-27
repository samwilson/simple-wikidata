<?php

namespace Samwilson\SimpleWikidata\Properties;

use DateTime;

/**
 * Literal data field for a point in time.
 * Given as a date and time with some precision and boundaries.
 * The time is saved internally in the specified calendar model.
 * time – explicit value for point in time, represented as a timestamp resembling ISO 8601, e.g. +2013-01-01T00:00:00Z. The year is always signed and padded to have between 4 and 16 digits.
 * timezone – explicit value as a signed integer. Timezone information as an offset from UTC in minutes.
 * before – explicit integer value for how many units after the given time it could be. The unit is given by the precision.
 * after – explicit integer value for how many units before the given time it could be. The unit is given by the precision.
 * precision – explicit value encoded in a shortint.
 *     The numbers have the following meaning: 0 - billion years, 1 - hundred million years, ..., 6 - millennium, 7 - century, 8 - decade, 9 - year, 10 - month, 11 - day, 12 - hour, 13 - minute, 14 - second.
 * calendarmodel – explicit value given as a URI. It identifies the calendar model of the timestamp.
 */
class Time extends Property {

    protected $data;
    public function __construct($data)
    {
//        [time] => +1948-11-14T00:00:00Z
//    [timezone] => 0
//    [before] => 0
//    [after] => 0
//    [precision] => 11
//    [calendarmodel] => http://www.wikidata.org/entity/Q1985727
        $this->data = $data;
    }
    
    public function getDateTime()
    {
        return new DateTime($this->data['time']);
    }

    public function getTimezone()
    {
        new \DateTimeZone($this->data['timezone']);
    }

    public function getBefore()
    {
        
    }
    public function getAfter() {
        
    }

    public function getPrecision()
    {
        
    }

    public function getCalendarModel()
    {
        
    }
}
