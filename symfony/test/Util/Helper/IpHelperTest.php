<?php

namespace test\MetaLogger\Util\Helper;

use Theaxerant\Metalogger\Util\Helper\IpHelper;
use PHPUnit\Framework\TestCase;

class IpHelperTest extends TestCase
{

    /**
     * @test
     * @dataProvider FilterFromMaskDataProvider
     * @param string $ip
     * @param string $netMask
     * @param bool $expected
     * @return void
     */
    public function testFilterFromMask(string $ip, string $netMask, bool $expected): void {

        $actual = IpHelper::filterFromMask($ip, $netMask);
        $this->assertEquals($expected, $actual);
    }

    public static function FilterFromMaskDataProvider(): array {
        return [
            'No filter 24 mask' => [ '10.12.12.2', '10.12.12.0/24', false ],
            'No filter 16 mask' => [ '10.12.14.2', '10.12.0.0/16', false ],
            'No filter 8 mask' => [ '10.14.12.2', '10.0.0.0/8', false ],
            'filter 24 mask' => [ '10.12.11.2', '10.12.12.0/24', true ],
            'filter 16 mask' => [ '10.1.12.2', '10.12.0.0/16', true ],
            'filter 8 mask' => [ '9.12.12.2', '10.0.0.0/8', true ],
            'docker address' => ['172.20.0.2', '172.0.0.0/8', false ],
        ];
    }
}
