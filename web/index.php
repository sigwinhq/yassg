<?php

use Sigwin\YASSG\Bridge\Symfony\Kernel;

require_once __DIR__ .'/../bootstrap.php';

return static function (array $context) {
    return new Kernel($GLOBALS['YASSG_BASEDIR'], $context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
