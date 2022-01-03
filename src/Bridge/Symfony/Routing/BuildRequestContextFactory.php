<?php

namespace Sigwin\YASSG\Bridge\Symfony\Routing;

use Symfony\Component\Routing\RequestContext;

class BuildRequestContextFactory
{
    private $buildUrl;
    
    public function setBuildUrl(string $buildUrl)
    {
        $this->buildUrl = $buildUrl;
    }
    
    public function create(): \Symfony\Component\Routing\RequestContext
    {
        return RequestContext::fromUri($this->buildUrl ?? 'http://localhost/');
    }
}
