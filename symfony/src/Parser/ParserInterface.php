<?php

namespace Theaxerant\Metalogger\Parser;

interface ParserInterface {
    /**
     * Perform the logging operation
     *
     * @return void
     */
    public function log(): void;

    /**
     * Return the logging data
     *
     * @return array<string>
     */
    public function debug(): array;
}