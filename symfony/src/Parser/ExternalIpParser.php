<?php

namespace Theaxerant\Metalogger\Parser;

use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Parser\ParserInterface;

class ExternalIpParser implements ParserInterface {

    protected const SINGLE_LOGGER_KEY = 'IPV4';
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    private function getExternalIp(): string {
        $ipv4 = file_get_contents('https://ipv4.icanhazip.com/');
        return trim($ipv4);
    }

    /**
     * @inheritDoc
     */
    public function log(): void {
        $externalIp = $this->getExternalIp();
        $this->logger->series(true,self::SINGLE_LOGGER_KEY, $externalIp);
    }

    /**
     * @inheritDoc
     */
    public function debug(): array {
        $externalIp = $this->getExternalIp();
        return [
            "External IPv4 {$externalIp}",
        ];
    }
}