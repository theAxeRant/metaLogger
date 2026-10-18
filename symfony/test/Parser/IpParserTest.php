<?php

namespace Tests\MetaLogger\Parser;

use PHPUnit\Framework\TestCase;
use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Parser\IpParser;

class IpParserTest extends TestCase {

    private $originalPath;
    private $commandDirectory;

    protected function setUp(): void {
        $this->originalPath = getenv('PATH');
        $this->commandDirectory = sys_get_temp_dir() . '/metalogger-ip-' . bin2hex(random_bytes(8));
        mkdir($this->commandDirectory);
        $this->setCommandOutput(file_get_contents(__DIR__ . '/fixtures/ip-address.txt'));
        putenv('PATH=' . $this->commandDirectory . PATH_SEPARATOR . $this->originalPath);
    }

    protected function tearDown(): void {
        putenv('PATH=' . $this->originalPath);
        unlink($this->commandDirectory . '/ip');
        rmdir($this->commandDirectory);
    }

    private function setCommandOutput(string $output): void {
        // Require the actual command to invoke `ip a`, then supply representative output.
        $script = "#!/bin/sh\n[ \"\$#\" -eq 1 ] && [ \"\$1\" = a ] || exit 1\n";
        $script .= "cat <<'METALOGGER_IP_OUTPUT'\n" . $output . "\nMETALOGGER_IP_OUTPUT\n";
        file_put_contents($this->commandDirectory . '/ip', $script);
        chmod($this->commandDirectory . '/ip', 0700);
    }

    /** @dataProvider networkMaskProvider */
    public function testVersion4ReturnsOnlyMatchingIpv4Addresses(string $mask, string $expected): void {
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('ipCheck')->with($expected)->willReturnSelf();
        $parser = new IpParser($logger, IpParser::VERSION_4, $mask);

        $this->assertSame([
            'Internal IP Check Version 4',
            'Internal IP Check Netmask ' . $mask,
            'Internal IP Check Output ' . $expected,
        ], $parser->debug());
        $parser->log();
    }

    public static function networkMaskProvider(): array {
        return [
            'all non-loopback IPv4 addresses' => ['0.0.0.0/0', "192.168.1.12\n192.168.1.13\n172.17.0.1\n10.0.0.5"],
            'local network excludes Docker and other interfaces' => ['192.168.1.0/24', "192.168.1.12\n192.168.1.13"],
            'single host' => ['192.168.1.12/32', '192.168.1.12'],
            'no matching addresses' => ['203.0.113.0/24', ''],
        ];
    }

    /** @dataProvider noIpv4Provider */
    public function testVersion4HandlesNoLocalIpv4Addresses(string $output): void {
        $this->setCommandOutput($output);
        $parser = new IpParser($this->createMock(Logger::class), IpParser::VERSION_4, '0.0.0.0/0');

        $this->assertSame('Internal IP Check Output ', $parser->debug()[2]);
    }

    public static function noIpv4Provider(): array {
        return [
            'empty output' => [''],
            'loopback and IPv6 only' => ["    inet 127.0.0.1/8 scope host lo\n    inet6 2001:db8::1/64 scope global eth0\n"],
        ];
    }
}
