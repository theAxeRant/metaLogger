<?php

// Keep Debian's PHPUnit and its dependencies ahead of the project's PHPUnit 8.
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->unregister();
$loader->register(false);
