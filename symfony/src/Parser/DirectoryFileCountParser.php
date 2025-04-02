<?php

namespace Theaxerant\Metalogger\Parser;

class DirectoryFileCountParser extends AbstractLocationParser {

    protected function collectDrives(): array{
        $result = [];
        foreach ($this->drives as $drive) {
            $files = glob($drive->getPath(), GLOB_ONLYDIR);
            $count = (false === $files) ? -1 : count($files);
            $result[$drive->getName()] = $count;
        }
        return $result;
    }
}