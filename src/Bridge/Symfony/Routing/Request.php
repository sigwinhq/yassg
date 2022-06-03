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

namespace Sigwin\YASSG\Bridge\Symfony\Routing;

final class Request extends \Symfony\Component\HttpFoundation\Request
{
    public function withBaseUrl(string $baseUrl): self
    {
        $request = clone $this;
        $request->baseUrl = $baseUrl;

        return $request;
    }
}
