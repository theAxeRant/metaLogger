<?php

namespace Tests\MetaLogger;

use PHPUnit\Framework\TestCase;
use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Util\Application;

class LoggerTest extends TestCase {

    protected function setUp(): void {
        LoggerRequestStream::$requests = [];
        stream_wrapper_register('metalogger-test', LoggerRequestStream::class);
    }

    protected function tearDown(): void {
        stream_wrapper_unregister('metalogger-test');
    }

    /** @dataProvider logRequestProvider */
    public function testLogRequestsIdentifyTheApplication(string $method, array $arguments, string $path, string $body): void {
        $logger = Logger::create([
            'logger' => [
                'auth_key' => 'test-key',
                'endpoint' => 'metalogger-test://logger',
                'ip' => '/ip/%s',
                'single' => '/single/%s/%s',
                'series' => '/series/%s/%s',
            ],
        ]);

        $this->assertSame($logger, call_user_func_array([$logger, $method], $arguments));
        $this->assertCount(1, LoggerRequestStream::$requests);
        $request = LoggerRequestStream::$requests[0];
        $hostname = explode('.', php_uname('n'))[0];
        $this->assertSame('metalogger-test://logger' . sprintf($path, $hostname), $request['url']);
        $this->assertSame('POST', $request['options']['method']);
        $this->assertSame($body, $request['options']['content']);
        $headers = explode("\r\n", trim($request['options']['header']));
        $this->assertSame([
            'Content-type: text/plain',
            'X-AUTH-TOKEN: test-key',
            'User-Agent: metaLogger/' . Application::VERSION,
        ], $headers);
    }

    public static function logRequestProvider(): array {
        return [
            'IP check' => ['ipCheck', ["192.168.1.12\n192.168.1.13"], '/ip/%s', "192.168.1.12\n192.168.1.13"],
            'single metric' => ['single', ['disk space', '1024'], '/single/%s/disk+space', '1024'],
            'rolling series' => ['series', [false, 'disk space', '512'], '/series/%s/disk+space', '512'],
            'distinct series' => ['series', [true, 'IPV4', '203.0.113.5'], '/series/%s/IPV4?distinct=1', '203.0.113.5'],
        ];
    }
}

/** Capture requests locally so logger tests never contact a remote service. */
class LoggerRequestStream {

    public $context;
    public static $requests = [];
    private $position = 0;

    public function stream_open($path, $mode, $options, &$openedPath): bool {
        self::$requests[] = [
            'url' => $path,
            'options' => stream_context_get_options($this->context)['http'],
        ];
        return true;
    }

    public function stream_read($count): string {
        $result = substr('ok', $this->position, $count);
        $this->position += strlen($result);
        return $result;
    }

    public function stream_eof(): bool {
        return $this->position >= 2;
    }

    public function stream_stat(): array {
        return [];
    }
}
