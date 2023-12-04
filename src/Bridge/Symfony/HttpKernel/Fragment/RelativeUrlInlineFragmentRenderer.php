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

namespace Sigwin\YASSG\Bridge\Symfony\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class RelativeUrlInlineFragmentRenderer implements FragmentRendererInterface
{
    public function __construct(private InlineFragmentRenderer $fragmentRenderer, private UrlGeneratorInterface $urlGenerator) {}

    /**
     * @param array<array-key, mixed> $options
     */
    public function render(string|\Symfony\Component\HttpKernel\Controller\ControllerReference $uri, Request $request, array $options = []): \Symfony\Component\HttpFoundation\Response
    {
        if (\is_string($uri)) {
            $uri = rtrim(str_replace($this->urlGenerator->getContext()->getBaseUrl(), '', $uri), '/');
        }

        return $this->fragmentRenderer->render($uri, $request, $options);
    }

    public function getName(): string
    {
        return $this->fragmentRenderer->getName();
    }
}
