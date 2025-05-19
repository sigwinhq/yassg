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

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Sigwin\YASSG\Linkable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LinkableExtension extends AbstractExtension
{
    public function __construct(private UrlGeneratorInterface $generator)
    {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_url', fn (Linkable $linkable) => $this->generator->generate($linkable->getLinkRouteName(), $linkable->getLinkRouteParameters())),
        ];
    }
}
