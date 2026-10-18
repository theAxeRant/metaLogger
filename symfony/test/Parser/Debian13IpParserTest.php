<?php

namespace Tests\MetaLogger\Parser;

use PHPUnit\Framework\TestCase;
use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Parser\IpParser;

class Debian13IpParserTest extends TestCase {

    public function testVersion4WithRealDebian13NetworkInterfaces(): void {
        if (getenv('METALOGGER_TEST_DEBIAN13') !== '1') {
            $this->markTestSkipped('Run in the Debian 13 test container.');
        }

        $os = parse_ini_file('/etc/os-release');
        $this->assertSame('debian', $os['ID']);
        $this->assertSame('13', $os['VERSION_ID']);
        $this->assertSame('', trim((string) shell_exec('command -v ifconfig')));

        // Independently obtain expected addresses using iproute2's JSON output.
        $interfaces = json_decode(shell_exec('ip -j -4 address show'), true);
        $this->assertIsArray($interfaces);
        $expected = [];
        foreach ($interfaces as $interface) {
            foreach ($interface['addr_info'] as $address) {
                if (strpos($address['local'], '127.') !== 0) {
                    $expected[] = $address['local'];
                }
            }
        }
        $this->assertNotEmpty($expected, 'The container must have a non-loopback IPv4 address.');

        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('ipCheck')->with(implode("\n", $expected))->willReturnSelf();
        $parser = new IpParser($logger, IpParser::VERSION_4, '0.0.0.0/0');
        $this->assertSame('Internal IP Check Output ' . implode("\n", $expected), $parser->debug()[2]);
        $parser->log();

        $hostParser = new IpParser($logger, IpParser::VERSION_4, $expected[0] . '/32');
        $this->assertSame('Internal IP Check Output ' . $expected[0], $hostParser->debug()[2]);
    }
}
