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

namespace Sigwin\YASSG\Exception;

final class NoResultException extends \RuntimeException
{
    public function __construct(?string $condition)
    {
        if ($condition === null) {
            parent::__construct('No result found');
        } else {
            parent::__construct(\sprintf('No result found for condition "%s"', $condition));
        }
    }
}
