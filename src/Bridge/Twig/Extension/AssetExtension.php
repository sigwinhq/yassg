<?php

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Routing\RequestContext;

class AssetExtension extends AbstractExtension
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
            new TwigFunction('asset', function(string $path) {
                return $this->requestContext->getScheme() .'://'. $this->requestContext->getHost() .$this->requestContext->getBaseUrl() . $this->packages->getUrl($path);
            }),
        ];
    }
}
