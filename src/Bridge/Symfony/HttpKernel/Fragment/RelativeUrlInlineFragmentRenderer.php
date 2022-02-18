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
use Symfony\Component\Routing\RequestContext;

if (\Composer\InstalledVersions::getVersion('symfony/http-kernel') < 6.0) {
    final class RelativeUrlInlineFragmentRenderer implements FragmentRendererInterface
    {
        private InlineFragmentRenderer $fragmentRenderer;
        private RequestContext $requestContext;

        public function __construct(InlineFragmentRenderer $fragmentRenderer, RequestContext $requestContext)
        {
            $this->fragmentRenderer = $fragmentRenderer;
            $this->requestContext = $requestContext;
        }

        /**
         * @psalm-param string|\Symfony\Component\HttpKernel\Controller\ControllerReference $uri
         */
        public function render($uri, Request $request, array $options = []): \Symfony\Component\HttpFoundation\Response
        {
            if (\is_string($uri)) {
                $uri = str_replace($this->requestContext->getBaseUrl(), '', $uri);
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
        private RequestContext $requestContext;

        public function __construct(InlineFragmentRenderer $fragmentRenderer, RequestContext $requestContext)
        {
            $this->fragmentRenderer = $fragmentRenderer;
            $this->requestContext = $requestContext;
        }

        public function render(\Symfony\Component\HttpKernel\Controller\ControllerReference|string $uri, Request $request, array $options = []): \Symfony\Component\HttpFoundation\Response
        {
            if (\is_string($uri)) {
                $uri = str_replace($this->requestContext->getBaseUrl(), '', $uri);
            }

            return $this->fragmentRenderer->render($uri, $request, $options);
        }

        public function getName(): string
        {
            return $this->fragmentRenderer->getName();
        }
    }
}
