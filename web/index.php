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

use Sigwin\YASSG\Bridge\Symfony\Kernel;

require_once __DIR__.'/../bootstrap.php';

return static function (array $context) {
    return new Kernel($GLOBALS['YASSG_BASEDIR'], $context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
