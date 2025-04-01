<?php

namespace Theaxerant\Metalogger\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'logger:validate:config', description: 'Validate a configuration file')]
class ValidateConfigurationCommand extends Command {

    protected function configure(){
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file')
        ->setHelp(
            <<<'HELP'
The <info>%command.name%</info> command creates configuration file.
HELP
        )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        return Command::SUCCESS;
    }
}