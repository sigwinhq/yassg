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

use Sigwin\YASSG\Metadata;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SocialImageExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'yassg_social_image',
                /**
                 * @param array<string, mixed> $context
                 */
                function (array $context, ?object $entity = null): string {
                    return $this->generateSocialImageUrl($context, $entity);
                },
                ['needs_context' => true]
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function generateSocialImageUrl(array $context, ?object $entity): string
    {
        // If no entity is provided, try to find one in the context
        if ($entity === null) {
            foreach ($context as $item) {
                if (\is_object($item) && property_exists($item, '__metadata') && $item->__metadata instanceof Metadata) {
                    $entity = $item;
                    break;
                }
            }
        }

        if ($entity === null) {
            throw new \RuntimeException('Cannot generate social image URL without an entity. Pass an entity object or ensure one exists in the template context.');
        }

        // Get the current request to build the .svg URL
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new \RuntimeException('Cannot generate social image URL without a request context');
        }

        $routeName = $request->attributes->get('_route');
        if (! \is_string($routeName)) {
            throw new \RuntimeException('Cannot determine route name for social image generation');
        }

        // Get route parameters from the entity or request
        $routeParams = $request->attributes->get('_route_params', []);
        
        // Generate the URL with .svg appended
        // Try to use the route with _format parameter first
        try {
            $svgUrl = $this->urlGenerator->generate($routeName, array_merge($routeParams, ['_format' => 'svg']), UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (\Exception $e) {
            // Fallback: append .svg to the current path
            $htmlUrl = $this->urlGenerator->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_PATH);
            $svgUrl = rtrim($htmlUrl, '/').'.svg';
        }

        return $svgUrl;
    }
}
