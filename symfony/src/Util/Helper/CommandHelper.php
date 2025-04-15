<?php

namespace Theaxerant\Metalogger\Util\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Theaxerant\Metalogger\Style\MetaLoggerStyle;
use Theaxerant\Metalogger\Util\ConfigurationValidator;
use Theaxerant\Metalogger\Util\validator\Validator;

class CommandHelper{

    public static function validateConfig(MetaLoggerStyle $io, string $configFile): int {
        if(!file_exists($configFile)) {
            $io->error("Config file '{$configFile}' not found");
            return Command::INVALID;
        }

        $valitron = new Validator(Yaml::parseFile($configFile));
        $validator = ConfigurationValidator::create($valitron);

        if(!$validator->validate()){
            foreach ($validator->errors() as $errors){
                $io->error($errors);
            }
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}