<?php

namespace Theaxerant\Metalogger\Parser;

use Theaxerant\Metalogger\Logger;

class IpParser implements ParserInterface {

    const string VERSION_1 = '1';
    const string VERSION_2 = '2';
    const string VERSION_3 = '3';

    /**
     * @param Logger $logger Configured Logger object to log the Internal IP address
     * @param string $version Command Version to use to parse the IP address
     * @param string|null $netmask Netmask to allow through to the log event
     */
    public function __construct(private Logger $logger, private string $version, private ?string $netmask) {}

    private function getInternalIp(): string {
        $internal_ip_check = [
            self::VERSION_1 => "/sbin/ifconfig | grep 'inet addr:' | grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'",
            self::VERSION_2 => "/sbin/ifconfig | grep 'inet ' | grep -v '127.0.0.1' | cut -d\  -f10 | awk '{ print $1}'",
            self::VERSION_3 => "/sbin/ifconfig | grep 'inet ' | grep -v '127.0.0.1' | grep -v '172.17.0.1' | cut -d\  -f10 | awk '{ print $1}'",
        ];

        return trim(shell_exec($internal_ip_check[$this->version]));
    }

    public function log(): void {
        $internal_ip = $this->getInternalIp();
        $this->logger->ipCheck($internal_ip);
    }

    public function debug(): array {
        $internal_ip = $this->getInternalIp();
        return [
            "Internal IP Check Version {$this->version}",
            "Internal IP Check Netmask {$this->netmask}",
            "Internal IP Check Netmask {$internal_ip}",
        ];
    }
}