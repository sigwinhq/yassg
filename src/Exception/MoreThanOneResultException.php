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

final class MoreThanOneResultException extends \RuntimeException
{
    public static function newSelf(int $count): self
    {
        return new self(\sprintf('One result expected, %1$d found', $count));
    }
}
