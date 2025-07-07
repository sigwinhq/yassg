<?php

declare(strict_types=1);

/*
 * This file is part of the Sigwin Yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG;

$skipBundles = [];
if (getenv('YASSG_SKIP_BUNDLES') !== false) {
    $skipBundles = array_map(static fn (string $name): string => mb_trim($name), explode(',', getenv('YASSG_SKIP_BUNDLES')));
}
$GLOBALS['YASSG_SKIP_BUNDLES'] = $skipBundles;

$autoloaders = [
    // when installed in the project
    __DIR__.'/../../autoload_runtime.php',

    // in own repo
    __DIR__.'/vendor/autoload_runtime.php',
];

$baseDir = null;
foreach ($autoloaders as $autoloader) {
    if (true === file_exists($autoloader)) {
        $baseDir = getcwd();
        if ($baseDir === false) {
            throw new \RuntimeException('Could not get current working directory');
        }
        $GLOBALS['YASSG_BASEDIR'] = $baseDir;

        require_once $autoloader;

        break;
    }
}

if ($baseDir === null) {
    fwrite(\STDERR, 'You must set up the project dependencies using `composer install`'.\PHP_EOL);

    exit(1);
}
