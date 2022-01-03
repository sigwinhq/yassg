<?php

namespace Sigwin\YASSG;

use Sigwin\YASSG\Database;
use Sigwin\YASSG\Renderer;
use Sigwin\YASSG\Router;
use Symfony\Component\Yaml\Yaml;

$autoloaders = [
    __DIR__.'/../../autoload_runtime.php',
];

$baseDir = null;
foreach ($autoloaders as $autoloader) {
    if (true === file_exists($autoloader)) {
        $GLOBALS['YASSG_BASEDIR'] = $baseDir = realpath(dirname($autoloader).'/..');
        
        require_once $autoloader;
        
        break;
    }
}

if ($baseDir === null) {
    fwrite(STDERR, 'You must set up the project dependencies using `composer install`'.PHP_EOL);

    exit(1);
}
