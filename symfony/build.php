<?php

// better set to php.ini phar.readonly = 0
ini_set("phar.readonly", 0);

try {
    $phar = new Phar("logger.phar", FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
    $phar->buildFromIterator($iterator, __DIR__);
    $default = $phar->createDefaultStub("console.php");
    $phar->setStub("#!/usr/bin/env php \n" . $default);

    $phar->compressFiles(Phar::GZ);
} catch (PharException $e) {
    echo $e->getMessage();
}