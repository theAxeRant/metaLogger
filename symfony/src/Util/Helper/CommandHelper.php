<?php

namespace Theaxerant\Metalogger\Util\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Theaxerant\Metalogger\Style\MetaLoggerStyle;
use Theaxerant\Metalogger\Util\ConfigurationValidator;

class CommandHelper{

    public static function validateConfig(MetaLoggerStyle $io, string $configFile): int {
        if(!file_exists($configFile)) {
            $io->error("Config file '{$configFile}' not found");
            return Command::INVALID;
        }

        $validator = ConfigurationValidator::create(Yaml::parseFile($configFile));

        if(!$validator->validate()){
            foreach ($validator->errors() as $errors){
                $io->error($errors);
            }
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}