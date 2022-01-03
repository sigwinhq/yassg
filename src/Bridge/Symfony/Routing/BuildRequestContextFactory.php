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

use Symfony\Component\Routing\RequestContext;

final class BuildRequestContextFactory
{
    private ?string $buildUrl = null;

    public function setBuildUrl(string $buildUrl): void
    {
        $this->buildUrl = $buildUrl;
    }

    public function create(): RequestContext
    {
        return RequestContext::fromUri($this->buildUrl ?? 'http://localhost/');
    }
}
