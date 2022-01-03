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

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\RequestContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetExtension extends AbstractExtension
{
    private RequestContext $requestContext;
    private Packages $packages;

    public function __construct(RequestContext $requestContext, Packages $packages)
    {
        $this->requestContext = $requestContext;
        $this->packages = $packages;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', function (string $path) {
                return $this->requestContext->getScheme().'://'.$this->requestContext->getHost().$this->requestContext->getBaseUrl().$this->packages->getUrl(ltrim($path, '/'));
            }),
        ];
    }
}
