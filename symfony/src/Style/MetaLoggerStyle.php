<?php

namespace Theaxerant\Metalogger\Style;

use DateTime;
use Symfony\Component\Console\Style\SymfonyStyle;

class MetaLoggerStyle extends SymfonyStyle {

    /**
     * @param     $messages
     * @param int $type
     *
     * @return void
     */
    public function log($messages, int $type = self::OUTPUT_NORMAL): void {
        if(!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::writeln(sprintf("[%s] - %s", (new DateTime())->format(\DateTimeInterface::ATOM) , $message), $type);
        }
    }
}
