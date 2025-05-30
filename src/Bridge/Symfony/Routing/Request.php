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

namespace Sigwin\YASSG\Bridge\Symfony\Routing;

use Symfony\Component\Routing\RequestContext;

final class Request extends \Symfony\Component\HttpFoundation\Request
{
    public function withContext(RequestContext $context): self
    {
        $request = clone $this;
        $request->baseUrl = $context->getBaseUrl();
        $request->server->set('HTTPS', $context->getScheme() === 'https' ? 'on' : 'off');
        $request->headers->set('HOST', $context->getHost());

        return $request;
    }
}
