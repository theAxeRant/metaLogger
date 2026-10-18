<?php

namespace Tests\MetaLogger\Util;

use PHPUnit\Framework\TestCase;
use Theaxerant\Metalogger\Util\ConfigurationValidator;
use Valitron\Validator;

class ConfigurationValidatorTest extends TestCase {

    /** @dataProvider versionProvider */
    public function testIpParserVersionValidation(int $version, bool $expected): void {
        $validator = ConfigurationValidator::create(new Validator([
            'logger' => [
                'auth_key' => 'test-key',
                'endpoint' => 'https://example.com',
                'single' => '/single/%s',
                'series' => '/series/%s',
                'ip' => '/ip/%s',
            ],
            'log' => ['ip' => ['version' => $version, 'mask' => '192.168.1.0/24']],
        ]));

        $this->assertSame($expected, $validator->validate());
    }

    public static function versionProvider(): array {
        return [
            'legacy version 1' => [1, true],
            'legacy version 2' => [2, true],
            'legacy version 3' => [3, true],
            'iproute2 version 4' => [4, true],
            'unsupported version' => [5, false],
        ];
    }
}
