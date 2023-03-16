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

final class UnexpectedAttributeException extends \RuntimeException
{
    public static function newSelf(string $id, string $message): self
    {
        return new self(sprintf('Unexpected attribute for "%1$s", %2$s', $id, $message));
    }
}
