<?php

declare(strict_types=1);

/*
 * This file is part of the yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG;

$autoloaders = [
    // when installed in the project
    __DIR__.'/../../autoload_runtime.php',

    // in own repo
    __DIR__.'/vendor/autoload_runtime.php',
];

$baseDir = null;
foreach ($autoloaders as $autoloader) {
    if (true === file_exists($autoloader)) {
        $GLOBALS['YASSG_BASEDIR'] = $baseDir = getcwd();

        require_once $autoloader;

        break;
    }
}

if ($baseDir === null) {
    fwrite(\STDERR, 'You must set up the project dependencies using `composer install`'.\PHP_EOL);

    exit(1);
}
