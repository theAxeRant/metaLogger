<?php

namespace Theaxerant\Metalogger\Util;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;

class Application extends \Symfony\Component\Console\Application {

    private bool $commandsRegistered = false;

    const LOGO = "                   __        __
   ____ ___  ___  / /_____ _/ /   ____  ____ _____ ____  _____
  / __ `__ \/ _ \/ __/ __ `/ /   / __ \/ __ `/ __ `/ _ \/ ___/
 / / / / / /  __/ /_/ /_/ / /___/ /_/ / /_/ / /_/ /  __/ /
/_/ /_/ /_/\___/\__/\__,_/_____/\____/\__, /\__, /\___/_/
                                     /____//____/";

    const VERSION = '1.0.0';

    const TAG_LINE = 'Log, or die!';
    public function __construct() {
        parent::__construct('MetaLogger', self::VERSION);
        $this->registerCommands();
    }

    public function getLongVersion(): string {
        return sprintf('<fg=red>%s</>

  - <fg=red>%s</>
  
<fg=yellow>%s</>
version: <info>%s</info>', self::LOGO, self::TAG_LINE, $this->getName(), $this->getVersion());
    }

    /**
     * Dynamically register all commands in the Command folder
     *
     * @return void
     * @throws ReflectionException
     */
    protected function registerCommands()
    {
        if($this->commandsRegistered) return;

        $this->commandsRegistered = true;

        if (!is_dir($dir = __DIR__ . '/../Command')) return;

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = 'Theaxerant\\Metalogger\\Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns = $prefix . '\\'.strtr($relativePath, '/', '\\');
            }
            $r = new ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf(Command::class) && !$r->isAbstract()) {
                $this->add($r->newInstance());
            }
        }
    }
}