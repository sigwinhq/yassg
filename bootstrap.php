<?php

namespace Sigwin\YASSG;

use Sigwin\YASSG\Database;
use Sigwin\YASSG\Renderer;
use Sigwin\YASSG\Router;
use Symfony\Component\Yaml\Yaml;

ini_set('display_errors', 1);

function bootstrap(array $autoLoaders): string
{
    $found = false;
    $baseDir = null;
    foreach ($autoLoaders as $autoLoader) {
        if (true === file_exists($autoLoader)) {
            /* @noinspection PhpIncludeInspection */
            include $autoLoader;

            // strips "vendor/"
            $baseDir = realpath(dirname($autoLoader).'/..');

            $found = true;
        }
    }
    if ($found === false) {
        fwrite(STDERR, 'You must set up the project dependencies using `composer install`'.PHP_EOL);

        exit(1);
    }
    
    return $baseDir;
}

function createRenderer(string $baseDir, string $baseUrl): \Sigwin\YASSG\Renderer
{
    $database = new Database(Yaml::parseFile($baseDir.'/database.yaml'));
    $router = new Router(Yaml::parseFile($baseDir.'/routes.yaml'), $baseUrl);
    $renderer = new Renderer(
        $database,
        $router,
        [
            'templates' => $baseDir.'/templates',
            'base_url' => $baseUrl,
            'cache' => $baseDir.'/cache',
            'auto_reload' => true,
        ]
    );
    
    return $renderer;
}
