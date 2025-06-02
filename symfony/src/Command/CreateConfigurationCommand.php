<?php /** @noinspection PhpUnused */

namespace Theaxerant\Metalogger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateConfigurationCommand extends Command {

    const BASE_CONFIG = __DIR__ . '/../resources/default.yaml';

    protected function configure(){
        $this->setName('config:create')
            ->setDescription('Create configuration file')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file')
            ->setHelp(
            <<<'HELP'
The <info>%command.name%</info> command creates a configuration file.
HELP
        )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $config = $input->getOption('config');

        $base = file_get_contents(self::BASE_CONFIG);

        $output = fopen($config, 'w');

        fwrite($output, $base);

        fclose($output);

        return Command::SUCCESS;
    }
}