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

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Generator;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class GlobalVariableUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator, private readonly RequestStack $requestStack, private array $routes)
    {
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $request = $this->requestStack->getMainRequest();
        if (! isset($this->routes[$name]) || $request === null) {
            return $this->urlGenerator->generate($name, $parameters, $referenceType);
        }

        foreach ($this->routes[$name]['variables'] ?? [] as $variable) {
            if (! isset($parameters[$variable])) {
                $value = $request->attributes->get($variable);
                if ($value !== null) {
                    $parameters[$variable] = $value;
                }
            }
        }

        return $this->urlGenerator->generate($name, $parameters, $referenceType);
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
