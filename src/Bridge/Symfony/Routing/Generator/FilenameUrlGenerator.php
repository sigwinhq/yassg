<?php

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class FilenameUrlGenerator implements UrlGeneratorInterface
{
    private string $baseUrl;
    
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {}

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $url = $this->urlGenerator->generate($name, $parameters + ['_filename' => 'index.html'], $referenceType);
        
        if ($referenceType === self::ABSOLUTE_URL) {
            // TODO: hack around a Symfony Router bug (?)
            $url = str_replace('http://localhost', $this->baseUrl, $url);
        }

        return $url;
    }
    
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }
    
    public function setContext(RequestContext $context): void
    {
        $this->urlGenerator->setContext($context);
    }
    
    public function getContext(): RequestContext
    {
        return $this->urlGenerator->getContext();
    }
}
