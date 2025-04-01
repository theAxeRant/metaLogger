<?php

namespace Theaxerant\Metalogger\Parser;

use DateTime;
use DateTimeZone;

class LastFileAccessParser extends AbstractLocationParser {

    protected function collectDrives(): array{
        $result = [];
        foreach ($this->drives as $drive) {
            $access = stat($drive->getPath());
            $dateString = 'File Not Found';
            if (is_array($access)) {
                $date = new DateTime("@" . $access['mtime']);
                $date->setTimezone(new DateTimeZone(self::DEFAULT_TIMEZONE));
                $dateString = $date->format(self::FORMAT_DATE_STRING);
            }
            $result[$drive->getName()] = $dateString;
        }
        return $result;
    }
}