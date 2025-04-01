<?php

namespace Theaxerant\Metalogger\Util;

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
            ->rule('url', ['logger.endpoint'])
            ->rule('array', ['logger', 'log', 'log.drive', 'log.file_access', '.log.file_count'])->message('{field} must be array')
            ->rule('required', ['log.ip.version', 'log.ip.ip_whitelist'])
            ->rule('integer', ['log.ip.version'])
            ->rule('optional', ['log.drive', 'log.file_access', '.log.file_count'])
        ;
    }
}
