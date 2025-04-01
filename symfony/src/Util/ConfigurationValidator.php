<?php

namespace Theaxerant\Metalogger\Util;

use Theaxerant\Metalogger\Parser\IpParser;
use Valitron\Validator;

class ConfigurationValidator {

    /**
     * @var Validator
     */
    private $validator;

    protected function __construct(Validator $validator = null) {
        $this->validator = $validator;
        $this->buildRules();
    }

    public static function create(array $data, Validator $validator = null): ConfigurationValidator {
        $validator = $validator ?? new Validator($data);
        return new self($validator);
    }

    public function validate(): bool {
        return $this->validator->validate();
    }

    /**
     * @return array|bool
     */
    public function errors() {
        return $this->validator->errors();
    }

    /**
     * Build the ruleset into the Valitron/Validator instance for the Configuration.
     *
     * @return void
     */
    protected function buildRules(){
        $this->validator
            ->rule('required', ['logger', 'log', 'logger.auth_key', 'logger.endpoint', 'logger.single', 'logger.ip'])
            ->rule('regex', 'logger.single', '/(?i)^.*%s.*/')->message('{field} must contain %%s placeholder for key')
            ->rule('regex', 'logger.ip', '/(?i)^.*%s.*/')->message('{field} must contain %%s placeholder for key')
            ->rule('url', ['logger.endpoint'])
            ->rule('array', ['logger', 'log', 'log.drive', 'log.file_access', '.log.file_count'])->message('{field} must be array')
            ->rule('required', ['log.ip.version', 'log.ip.mask'])
            ->rule('integer', ['log.ip.version'])
            ->rule('in', 'log.ip.version', [ IpParser::VERSION_1, IpParser::VERSION_2, IpParser::VERSION_3 ])
            ->rule('optional', ['log.drive', 'log.file_access', '.log.file_count']);
    }
}
