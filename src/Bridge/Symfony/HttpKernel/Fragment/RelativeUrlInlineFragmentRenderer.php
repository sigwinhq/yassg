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

namespace Sigwin\YASSG\Bridge\Symfony\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (\Composer\InstalledVersions::getVersion('symfony/http-kernel') < 6.0) {
    final class RelativeUrlInlineFragmentRenderer implements FragmentRendererInterface
    {
        private InlineFragmentRenderer $fragmentRenderer;
        private UrlGeneratorInterface $urlGenerator;

        public function __construct(InlineFragmentRenderer $fragmentRenderer, UrlGeneratorInterface $urlGenerator)
        {
            $this->fragmentRenderer = $fragmentRenderer;
            $this->urlGenerator = $urlGenerator;
        }

        /**
         * @psalm-param string|\Symfony\Component\HttpKernel\Controller\ControllerReference $uri
         */
        public function render($uri, Request $request, array $options = []): \Symfony\Component\HttpFoundation\Response
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
} else {
    final class RelativeUrlInlineFragmentRenderer implements FragmentRendererInterface
    {
        private InlineFragmentRenderer $fragmentRenderer;
        private UrlGeneratorInterface $urlGenerator;

        public function __construct(InlineFragmentRenderer $fragmentRenderer, UrlGeneratorInterface $urlGenerator)
        {
            $this->fragmentRenderer = $fragmentRenderer;
            $this->urlGenerator = $urlGenerator;
        }

        public function render(\Symfony\Component\HttpKernel\Controller\ControllerReference|string $uri, Request $request, array $options = []): \Symfony\Component\HttpFoundation\Response
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
}
