<?php

namespace Theaxerant\Metalogger\Parser;

use Theaxerant\Metalogger\Logger;

class UptimeParser implements ParserInterface
{

    private $logger;

    /**
     * @param Logger $logger Configured Logger object to log the Internal IP address
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function log(): void {
        // TODO: Implement log() method.
    }

    /**
     * @inheritDoc
     */
    public function debug(): array {
        // TODO: Implement debug() method.
    }
}