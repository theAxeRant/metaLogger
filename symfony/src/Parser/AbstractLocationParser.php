<?php

namespace Theaxerant\Metalogger\Parser;

use Theaxerant\Metalogger\Entity\NamedLocationCollection;
use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Parser\ParserInterface;

abstract class AbstractLocationParser implements ParserInterface {

    /** @var Logger  */
    protected $logger;
    /** @var NamedLocationCollection  */
    protected $drives;

    public function __construct(Logger $logger, NamedLocationCollection $drives) {
        $this->drives = $drives;
        $this->logger = $logger;
    }

    abstract protected function collectDrives(): array;

    public function log(): void {
        foreach ($this->collectDrives() as $label => $data) {
            $this->logger->single($label, $data);
        }
    }

    public function debug(): array {
        $formatted = [];
        foreach ($this->collectDrives() as $label => $data) {
            $formatted[] = "[$label] : $data";
        }
        return $formatted;
    }
}
