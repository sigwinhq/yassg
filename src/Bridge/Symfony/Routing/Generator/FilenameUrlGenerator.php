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

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class FilenameUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

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
