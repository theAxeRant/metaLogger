<?php /** @noinspection PhpUnused */

namespace Theaxerant\Metalogger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildConfigurationCommand extends Command {

    const BASE_CONFIG = __DIR__ . '/../resources/default.yaml';

    protected function configure(){
        $this->setName('config:build')
            ->setDescription('Build configuration file')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration file name')
            ->setHelp(
            <<<'HELP'

The <info>%command.name%</info> command will prompt the user for the required configuration to build a configuration file.

<error>Required information to have ready!</error>

<info>auth_key:</info> The Authorization Key for the MetaLogger API
<info>api_base:</info> The URL for the MetaLogger API
<info>api_single:</info> The endpoint for single log requests
<info>api_ip:</info> The API endpoint for IP requests
<info>Ip Version:</info> The version of the ifconfig scraper to use, defaults to 3.
<info>internal IP mask:</info> The IP mask to restrict internal IP addresses to, no mask will allow all internal IPs to log.

HELP
        )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $config = $input->getArgument('config');

        $base_file = file(self::BASE_CONFIG);

        $required = [
            'auth_key' => ['default' => null, 'help' => 'The Authorization Key for the MetaLogger API'],
            'endpoint' => ['default' => null, 'help' => 'The Base URL for the MetaLogger API'],
            'single' => ['default' => null, 'help' => 'The Single Value Endpoint for the MetaLogger API'],
            'ip'=> ['default' => null, 'help' => 'The IP Logging Endpoint for the MetaLogger API'],
            'version' => ['default' => 3, 'help' => 'The version of the ifconfig scraper to use, defaults to 3.'],
            'mask' => ['default' => '0.0.0.0/0', 'help' => 'The IP mask to restrict internal IP addresses to, no mask will allow all internal IPs to log.'],

        ];

        foreach($required as $key => $options) {
            $response = $io->ask($options['help'] . ' (default: ' . $options['default'] . ')' , $options['default']);
            $base_file = $this->updateLine($base_file, $key . ': ', $response);
        }

        file_put_contents($config, $base_file);

        return Command::SUCCESS;
    }

    /**
     * Find a key line in the file.
     *
     * @param array  $file The file to search
     * @param string $key The key value to search for
     *
     * @return int the line number of the key, or -1 if not found.
     */
    protected function findLine(array $file, string $key): int {
        foreach ($file as $index => $line) {
            if(strpos($line, $key) !== false) return $index;
        }
        return -1;
    }

    /**
     * Updates a specific line in a file array by replacing a value associated with a given key.
     *
     * @param array  $file The array representing the file contents, where each line is an element.
     * @param string $key The key used to identify the line to be updated.
     * @param string $value The new value to be inserted into the identified line.
     *
     * @return array The updated file array with the modified line, or the original array if the key is not found.
     */
    protected function updateLine(array $file, string $key, string $value): array {
        $line = $this->findLine($file, $key);
        if($line === -1) return $file;
        $output = explode('#', $file[$line]);

        $file[$line] = $output[0] . $value . " #" . $output[1];
        return $file;
    }
}