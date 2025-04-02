<?php

namespace Theaxerant\Metalogger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Theaxerant\Metalogger\Logger;
use Theaxerant\Metalogger\Parser\DirectoryFileCountParser;
use Theaxerant\Metalogger\Parser\DriveFreeSpaceParser;
use Theaxerant\Metalogger\Parser\IpParser;
use Theaxerant\Metalogger\Parser\LastFileAccessParser;
use Theaxerant\Metalogger\Parser\ParserInterface;
use Theaxerant\Metalogger\Style\MetaLoggerStyle;
use Theaxerant\Metalogger\Util\Helper\CommandHelper;
use Theaxerant\Metalogger\Entity\NamedLocationCollection;

class MetaLoggerCommand extends Command {

    protected function configure(){
        $this->setName('config:run')
            ->setDescription('Run the MetaLogger command')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration file')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Run in debug configuration')
            ->setHelp(
            <<<'HELP'
The <info>%command.name%</info> use the required configuration file to run the logging operations.

HELP
        )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new MetaLoggerStyle($input, $output);
        $debug = $input->getOption('debug');
        $config = $input->getArgument('config');

        $validation = CommandHelper::validateConfig($io, $config);

        if(Command::SUCCESS !== $validation) return $validation;

        $configData = Yaml::parseFile($config);

        // ok now what?  We have a valid config file
        $logEvents = [];

        $logger = Logger::create($configData);
        $ipParser = new IpParser(
            $logger,
            dotGet($configData, 'log.ip.version'),
            dotGet($configData, 'log.ip.mask')
        );
        $logEvents[] = $ipParser;

        // look through the directory parser classes and if the configurations exist add them to the stack fifo
        foreach([
                    'log.drive' => DriveFreeSpaceParser::class,
                    'log.file_access' => LastFileAccessParser::class,
                    'log.file_count' => DirectoryFileCountParser::class,
                ] as $configLocation => $parserClass) {
            // if there is a configuration set then it is an array of NamedLocations
            if(dotHas($configData, $configLocation)) {
                $driveParser = new $parserClass(
                    $logger,
                    NamedLocationCollection::create(dotGet($configData, $configLocation))
                );
                $logEvents[] = $driveParser;
            }
        }

        $debugOutput = [];
        /** @var ParserInterface $logEvent */
        foreach ($logEvents as $logEvent) {
            if($debug) {
                $debugOutput = array_merge($debugOutput, $logEvent->debug());
            } else {
                $logEvent->log();
            }
        }
        if($debug) {
            $io->log($debugOutput);
        }

        return Command::SUCCESS;
    }
}