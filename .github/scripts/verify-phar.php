<?php

// Compare the package with the checkout without executing the PHAR entry point.
$projectDirectory = dirname(__DIR__, 2);
require $projectDirectory . '/symfony/vendor/autoload.php';

try {
    if (count($argv) !== 2 || !is_file($argv[1])) {
        throw new RuntimeException('Usage: php verify-phar.php /path/to/metaLogger.phar');
    }

    $archive = new Phar(realpath($argv[1]));
    $expected = ['console' => $projectDirectory . '/symfony/console'];
    $sourceDirectory = $projectDirectory . '/symfony/src';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $sourceDirectory,
        FilesystemIterator::SKIP_DOTS
    ));
    foreach ($files as $file) {
        if ($file->isFile()) {
            $relativePath = substr($file->getPathname(), strlen($sourceDirectory) + 1);
            $expected['src/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath)] = $file->getPathname();
        }
    }

    foreach ($expected as $path => $sourcePath) {
        $content = file_get_contents($sourcePath);
        // Box removes the executable shebang from the CLI entry point.
        if ($path === 'console') {
            $content = preg_replace('/^#![^\r\n]*\r?\n/', '', $content);
        }
        if (!isset($archive[$path]) || $archive[$path]->getContent() !== $content) {
            throw new RuntimeException('Packaged file differs from source: ' . $path);
        }
    }

    $archivePrefix = 'phar://' . $archive->getPath() . '/';
    foreach (new RecursiveIteratorIterator($archive) as $file) {
        $path = substr($file->getPathname(), strlen($archivePrefix));
        if (strpos($path, 'src/') === 0 && !isset($expected[$path])) {
            throw new RuntimeException('Unexpected packaged source file: ' . $path);
        }
    }

    $version = \Theaxerant\Metalogger\Util\Application::VERSION;
    if (!is_string($version) || $version === '' || strpbrk($version, "\r\n") !== false) {
        throw new RuntimeException('Application::VERSION must be a nonempty, single-line string.');
    }
    echo $version, PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
