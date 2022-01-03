<?php

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class FilenameUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {}

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->urlGenerator->generate($name, $parameters + ['_filename' => 'index.html'], $referenceType);
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
