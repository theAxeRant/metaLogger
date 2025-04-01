<?php

namespace Theaxerant\Metalogger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Theaxerant\Metalogger\Style\MetaLoggerStyle;
use Theaxerant\Metalogger\Util\ConfigurationValidator;

class ValidateConfigurationCommand extends Command {

    protected function configure(){
        $this->setName('logger:validate:config')
            ->setDescription('Validate a configuration file')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration file')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Run in debug configuration')
            ->setHelp(
            <<<'HELP'
The <info>%command.name%</info> will validate the provided.

HELP
        )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new MetaLoggerStyle($input, $output);

        $config = $input->getArgument('config');

        if(!file_exists($config)) {
            $io->error("Config file '${config}' not found");
            return Command::INVALID;
        }

        $configData = Yaml::parseFile($config);

        $validator = ConfigurationValidator::create($configData);

        if(!$validator->validate()){
            foreach ($validator->errors() as $errors){
                $io->error($errors);
            }
            return Command::FAILURE;
        }

        $io->success("'${config}' file is valid");
        return Command::SUCCESS;
    }
}