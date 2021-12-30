<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;
use function Sigwin\YASSG\bootstrap;
use function Sigwin\YASSG\createRenderer;

require __DIR__.'/../bootstrap.php';
$baseDir = bootstrap([
    __DIR__.'/../../../autoload.php',
]);

$baseUrl = Request::createFromGlobals()->getBaseUrl();

$renderer = createRenderer($baseDir, $baseUrl);

echo $renderer;
