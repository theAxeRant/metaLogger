<?php

namespace Theaxerant\Metalogger\Parser;

use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Parser\ParserInterface;

class DriveParser implements ParserInterface
{

    protected $logger;
    protected $drives;

    public function __construct(Logger $logger, array $drives) {
        $this->drives = $drives;
        $this->logger = $logger;
    }

    protected function collectDrives(): array{
        $result = [];
        foreach ($this->drives as $label => $drive) {
            $result[$label] = (string)ceil(disk_free_space($drive) / 1024 / 1024);;
        }
        return $result;
    }
    public function log(): void {
       foreach ($this->collectDrives() as $label => $drive) {
           $this->logger->single($label, $drive);
       }
    }

    public function debug(): array {
        $formatted = [];
        foreach ($this->collectDrives() as $label => $drive) {
            $formatted[] = "[$label] : $drive";
        }
        return $formatted;
    }
}