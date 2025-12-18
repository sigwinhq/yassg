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
    public function __construct(private readonly UrlGeneratorInterface $generator)
    {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_url', fn (Linkable $linkable, array $parameters = []) => $this->generator->generate($linkable->getLinkRouteName(), array_replace($linkable->getLinkRouteParameters(), $parameters))),
            new TwigFunction('yassg_svg_url', fn (Linkable $linkable, array $parameters = []) => $this->generator->generate($linkable->getLinkRouteName(), array_replace($linkable->getLinkRouteParameters(), $parameters, ['_filename' => 'index.svg']))),
        ];
    }
}
