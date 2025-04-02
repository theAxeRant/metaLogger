<?php

// better set to php.ini phar.readonly = 0
ini_set("phar.readonly", 0);

try {
    $pharFile = "logger.phar";

    if (file_exists($pharFile)) {
        unlink($pharFile);
    }

    if (file_exists($pharFile . '.gz')) {
        unlink($pharFile . '.gz');
    }

    $phar = new Phar("logger.phar", FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
    $phar->startBuffering();

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
    $phar->buildFromIterator($iterator, __DIR__);
    $default = $phar->createDefaultStub("console");
    $phar->setStub("#!/usr/bin/env php \n" . $default);
    $phar->stopBuffering();
    $phar->compressFiles(Phar::GZ);
    chmod(__DIR__ . "/{$pharFile}", 0770);

    echo "$pharFile successfully created" . PHP_EOL;

} catch (PharException $e) {
    echo $e->getMessage();
}