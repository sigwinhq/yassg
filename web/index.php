<?php

use Symfony\Component\Console\Input\ArgvInput;
use function Sigwin\YASSG\bootstrap;
use function Sigwin\YASSG\createRenderer;

require __DIR__ .'/../bootstrap.php';

$baseDir = bootstrap([
    __DIR__.'/../../../../autoload.php',
]);

$baseUrl = Request::createFromGlobals()->getBaseUrl();

$renderer = yassg_create_renderer($baseDir, $baseUrl);

echo $renderer->render();
