<?php

namespace Theaxerant\Metalogger\Parser;

interface ParserInterface {
    public function log(): void;

    public function debug(): array;
}