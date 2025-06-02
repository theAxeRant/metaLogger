<?php /** @noinspection PhpUnused */

namespace Theaxerant\Metalogger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaxerant\Metalogger\Style\MetaLoggerStyle;
use Theaxerant\Metalogger\Util\Helper\CommandHelper;

class ValidateConfigurationCommand extends Command {

    protected function configure(){
        $this->setName('config:validate')
            ->setDescription('Validate a configuration file')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration file')
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

        $validation = CommandHelper::validateConfig($io, $config);

        if(Command::SUCCESS !== $validation) return $validation;

        $io->success("'{$config}' file is valid");
        return Command::SUCCESS;
    }
}