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

use Sigwin\YASSG\Asset\AssetFetch;
use Sigwin\YASSG\AssetQueue;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SocialImageExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $imgproxyUrl,
        private readonly Packages $packages,
        private readonly AssetQueue $thumbnailQueue,
        private readonly Environment $twig,
        private readonly Filesystem $filesystem,
        private readonly string $buildDir,
        private readonly string $baseDir,
        private readonly ?string $defaultSocialImageTemplate,
        private readonly array $routes
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
                 * @param array<string, mixed> $options
                 */
                function (array $context, ?string $template = null, array $options = []): string {
                    return $this->generateSocialImage($context, $template, $options);
                },
                ['needs_context' => true]
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $options
     */
    private function generateSocialImage(array $context, ?string $template, array $options): string
    {
        // Determine which template to use
        $templatePath = $this->resolveTemplate($template, $context, $options);
        
        if ($templatePath === null) {
            throw new \RuntimeException('No social image template configured. Set sigwin_yassg.social_image_template or pass a template path.');
        }

        // Render the SVG template with the current context
        $svgContent = $this->twig->render($templatePath, $context);

        // Create a hash for the SVG content
        $hash = md5($svgContent);
        $svgRelativePath = '/social-images/'.$hash.'.svg';
        
        // During build, write SVG to build directory
        // During dev, write to base directory for ImgProxy to access
        $svgAbsolutePath = $this->isBuild() ? $this->buildDir.$svgRelativePath : $this->baseDir.$svgRelativePath;
        $this->filesystem->dumpFile($svgAbsolutePath, $svgContent);

        // Generate ImgProxy URL for the SVG
        $format = $options['format'] ?? 'webp';
        $width = $options['width'] ?? 1200;
        $height = $options['height'] ?? 630;
        
        $filters = $this->buildImgproxyFilter([
            'width' => (string) $width,
            'height' => (string) $height,
            'format' => $format,
        ]);

        $url = $this->buildImgproxyUrl($svgRelativePath, $filters);

        if (! $this->isBuild()) {
            return $url;
        }

        // Schedule the image for fetching during build
        $destination = \sprintf('/social-images/%1$s.%2$s', $hash, $format);
        $this->thumbnailQueue->add(new AssetFetch($url, $destination));

        return $this->packages->getUrl(mb_ltrim($destination, '/'));
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $options
     */
    private function resolveTemplate(?string $template, array $context, array $options): ?string
    {
        // Priority 1: Explicit template parameter
        if ($template !== null) {
            return $template;
        }

        // Priority 2: Template from route options (if we can determine the current route)
        $request = $this->requestStack->getCurrentRequest();
        if ($request !== null) {
            $routeName = $request->attributes->get('_route');
            if ($routeName !== null && isset($this->routes[$routeName]['options']['social_image_template'])) {
                $routeTemplate = $this->routes[$routeName]['options']['social_image_template'];
                if ($routeTemplate !== null) {
                    return $routeTemplate;
                }
            }
        }

        // Priority 3: Template from context item metadata (if available)
        if (isset($options['self']) && \is_object($options['self'])) {
            $self = $options['self'];
            if (property_exists($self, 'socialImageTemplate') && $self->socialImageTemplate !== null) {
                return $self->socialImageTemplate;
            }
        }

        // Priority 4: Default template from configuration
        return $this->defaultSocialImageTemplate;
    }

    private function isBuild(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return true;
        }

        return (bool) $request->attributes->get('yassg_build', false);
    }

    /**
     * @param array<string, string> $options
     */
    private function buildImgproxyFilter(array $options): string
    {
        $filter = '';
        
        if ($options !== []) {
            $filters = [];

            $filter .= 'rs:fill';
            if (isset($options['width'])) {
                if (! is_numeric($options['width'])) {
                    throw new \RuntimeException('Invalid thumbnail width');
                }
                $filter .= ':'.$options['width'];
                unset($options['width']);

                if (isset($options['height'])) {
                    if (! is_numeric($options['height'])) {
                        throw new \RuntimeException('Invalid thumbnail height');
                    }
                    $filter .= ':'.$options['height'];
                    unset($options['height']);
                }
                $filters[] = $filter;
            }

            foreach ($options as $name => $value) {
                if (! \is_string($value)) {
                    throw new \RuntimeException('Invalid thumbnail option '.$name);
                }
                $filters[] = $name.':'.$value;
            }
            $filter = implode('/', $filters).'/';
        }

        return $filter;
    }

    /**
     * @psalm-pure
     */
    private function encode(string $payload): string
    {
        return mb_rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private function buildImgproxyUrl(string $path, string $filters): string
    {
        return \sprintf('%1$s/insecure/%2$s%3$s', $this->imgproxyUrl, $filters, $this->encode('local:///'.mb_ltrim($path, '/')));
    }
}
