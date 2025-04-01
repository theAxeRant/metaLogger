<?php

namespace Theaxerant\Metalogger;

use Exception;

class Logger
{

    private $securityToken;
    private $hostname;
    private $ip_check_path;
    private $base_url;
    private $single_path;

    protected function __construct() {}

    public static function create(array $config): Logger
    {
        $logger = new self();
        $logger->hostname = $logger->parseHostname();

        $logger->securityToken = $config['security_token'];
        $logger->ip_check_path = $config['ip_check_path'];
        $logger->base_url = $config['base_url'];
        $logger->single_path = $config['single_path'];
        return $logger;
    }

    public function ipCheck(string $internal_ip): Logger
    {
        $url = $this->generateIpCheckUrl();
        $postOptions = $this->postOptions($internal_ip);
        $context = stream_context_create($postOptions);
        $post = file_get_contents($url, false, $context);
        if ($post === false) {
            throw new Exception("data post was unsuccessful");
        }
        return $this;
    }

    public function single(string $key, string $value): Logger
    {
        $url = $this->generateSingleMetaUrl($key);
        $postOptions = $this->postOptions($value);
        $context = stream_context_create($postOptions);
        $post = file_get_contents($url, false, $context);
        if ($post === false) {
            throw new Exception("data post was unsuccessful");
        }
        return $this;
    }

    private static function parseHostname()
    {
        $hostname = php_uname('n');
        $parts = explode('.', $hostname);
        return $parts[0];
    }

    private function generateIpCheckUrl(): string
    {
        return vsprintf($this->base_url . $this->ip_check_path, [$this->hostname]);
    }

    private function generateSingleMetaUrl(string $key): string
    {
        return vsprintf($this->base_url . $this->single_path, [$this->hostname, urlencode($key)]);
    }

    /**
     * @param string $content
     * @return array[]
     */
    private function postOptions(string $content): array
    {
        return [
            'http' => [
                'header' => "Content-type: text/plain\r\nX-AUTH-TOKEN: " . $this->securityToken . "\r\n",
                'method' => 'POST',
                'content' => $content,
            ],
        ];
    }
}