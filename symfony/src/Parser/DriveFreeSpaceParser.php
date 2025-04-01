<?php

namespace Theaxerant\Metalogger\Parser;

class DriveFreeSpaceParser extends AbstractLocationParser {

    protected function collectDrives(): array{
        $result = [];
        foreach ($this->drives as $drive) {
            $result[$drive->getName()] = (string)ceil(disk_free_space($drive->getPath()) / 1024 / 1024);;
        }
        return $result;
    }
}