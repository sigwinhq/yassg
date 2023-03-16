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

final class BuildOptions
{
    public function __construct(private ?array $requestHeaders)
    {
    }

    public function getRequestHeaders(): ?array
    {
        return $this->requestHeaders;
    }
}
