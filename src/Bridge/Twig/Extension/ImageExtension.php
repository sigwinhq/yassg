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
use Sigwin\YASSG\Metadata;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ImageExtension extends AbstractExtension
{
    public function __construct(private readonly RequestStack $requestStack, private readonly string $imgproxyUrl, private readonly Packages $packages, private readonly AssetQueue $thumbnailQueue)
    {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'yassg_thumbnail',
                /**
                 * @param array{__path?: string}                    $context
                 * @param array<string, string>|array{self: object} $options
                 */
                function (array $context, string $path, array $options = []): string {
                    return $this->scheduleThumbnail($path, $options['format'] ?? 'webp', $context, $options);
                }, ['needs_context' => true]
            ),
            new TwigFunction(
                'yassg_picture',
                /**
                 * @param array{__path?: string}                    $context
                 * @param array<string, string>|array{self: object} $options
                 */
                function (array $context, string $path, array $options = []): string {
                    $ext = mb_strtolower(pathinfo($this->buildOriginAbsolutePath($path, $context, $options), \PATHINFO_EXTENSION));
                    $fallbackFormat = match ($ext) {
                        'webp', 'png' => 'png',
                        default => 'jpeg',
                    };

                    // Prepare options for 1x and 2x
                    $width = isset($options['width']) ? (int) $options['width'] : null;
                    $height = isset($options['height']) ? (int) $options['height'] : null;

                    $opts1x = $options;
                    $opts2x = $options;
                    if ($width !== null) {
                        $opts2x['width'] = $width * 2;
                    }
                    if ($height !== null) {
                        $opts2x['height'] = $height * 2;
                    }

                    // AVIF
                    $srcAvif1x = $this->scheduleThumbnail($path, 'avif', $context, $opts1x);
                    $srcAvif2x = ($width !== null || $height !== null) ? $this->scheduleThumbnail($path, 'avif', $context, $opts2x) : null;
                    $srcsetAvif = $srcAvif1x.' 1x'.($srcAvif2x ? ', '.$srcAvif2x.' 2x' : '');

                    // WebP
                    $srcWebp1x = $this->scheduleThumbnail($path, 'webp', $context, $opts1x);
                    $srcWebp2x = ($width !== null || $height !== null) ? $this->scheduleThumbnail($path, 'webp', $context, $opts2x) : null;
                    $srcsetWebp = $srcWebp1x.' 1x'.($srcWebp2x ? ', '.$srcWebp2x.' 2x' : '');

                    // Fallback
                    $srcFallback1x = $this->scheduleThumbnail($path, $fallbackFormat, $context, $opts1x);
                    $srcFallback2x = ($width !== null || $height !== null) ? $this->scheduleThumbnail($path, $fallbackFormat, $context, $opts2x) : null;
                    $srcsetFallback = $srcFallback1x.' 1x'.($srcFallback2x ? ', '.$srcFallback2x.' 2x' : '');

                    // Build <picture> element
                    $attributes = array_filter([
                        'class' => $options['attributes']['class'] ?? null,
                        'style' => $options['attributes']['style'] ?? null,
                    ]);

                    $imgAttributes = array_filter([
                        'src' => $srcFallback1x,
                        'srcset' => $srcsetFallback,
                        'width' => $width !== null ? (string) $width : null,
                        'height' => $height !== null ? (string) $height : null,
                        'alt' => $options['attributes']['alt'] ?? '',
                        'loading' => $options['attributes']['loading'] ?? 'lazy',
                        'decoding' => $options['attributes']['decoding'] ?? 'async',
                    ]);

                    $html = '<picture '.implode(' ', array_map(
                        static fn ($key, $value) => htmlspecialchars($key, \ENT_QUOTES).'="'.htmlspecialchars($value, \ENT_QUOTES).'"',
                        array_keys($attributes),
                        $attributes
                    )).'>';
                    $html .= '<source type="image/avif" srcset="'.htmlspecialchars($srcsetAvif, \ENT_QUOTES).'"/>';
                    $html .= '<source type="image/webp" srcset="'.htmlspecialchars($srcsetWebp, \ENT_QUOTES).'"/>';
                    if ($fallbackFormat === 'png') {
                        $html .= '<source type="image/png" srcset="'.htmlspecialchars($srcsetFallback, \ENT_QUOTES).'"/>';
                    } else {
                        $html .= '<source type="image/jpeg" srcset="'.htmlspecialchars($srcsetFallback, \ENT_QUOTES).'"/>';
                    }
                    $html .= '<img '
                        .implode(' ', array_map(
                            static fn ($key, $value) => htmlspecialchars($key, \ENT_QUOTES).'="'.htmlspecialchars($value, \ENT_QUOTES).'"',
                            array_keys($imgAttributes),
                            $imgAttributes
                        )).' />';
                    $html .= '</picture>';

                    return $html;
                }, ['needs_context' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @psalm-pure
     */
    private function encode(string $payload): string
    {
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private function scheduleThumbnail(string $path, string $format, array $context, array $options): string
    {
        $options['format'] = $format;
        $origin = $this->buildOriginAbsolutePath($path, $context, $options);
        $filters = $this->buildImgproxyFilter($options);
        $relativeOrigin = str_replace($GLOBALS['YASSG_BASEDIR'], '', $origin);
        $url = $this->buildImgproxyUrl($relativeOrigin, $filters);
        if (! $this->isBuild()) {
            return $url;
        }

        $destination = \sprintf('%1$s/%2$s.%3$s.%4$s', \dirname($relativeOrigin), pathinfo($relativeOrigin, \PATHINFO_FILENAME), mb_substr(md5(md5_file($origin).$filters), 0, 8), $format);
        $this->thumbnailQueue->add(new AssetFetch($url, $destination));

        return $this->packages->getUrl(ltrim($destination, '/'));
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
     * @param array{__path?: string}                    $context
     * @param array<string, string>|array{self: object} $options
     */
    private function buildOriginAbsolutePath(string $path, array $context, array $options): string
    {
        if (str_starts_with($path, './')) {
            $rootPath = $context['__path'] ?? '';
            if (! isset($context['__path'])) {
                if (isset($options['self'])) {
                    if (! \is_object($options['self']) || ! property_exists($options['self'], '__metadata')
                        || ! \is_object($options['self']->__metadata) || ! $options['self']->__metadata instanceof Metadata) {
                        throw new \RuntimeException('Cannot resolve path without knowing the current object, pass {self: object} as the second argument');
                    }
                    $rootPath = $options['self']->__metadata->path;
                } else {
                    $candidates = [];
                    foreach ($context as $item) {
                        if (\is_object($item) && property_exists($item, '__metadata') && $item->__metadata instanceof Metadata) {
                            $candidates[] = $item->__metadata;
                        }
                    }
                    if (\count($candidates) !== 1) {
                        throw new \RuntimeException('Cannot resolve path without a single Locatable object in context, pass {self: object} as the second argument');
                    }
                    $rootPath = $candidates[0]->path;
                }
            }

            $newPath = realpath(\dirname((string) $rootPath).'/'.$path);
            if ($newPath === false) {
                throw new \RuntimeException('Invalid thumbnail path '.$path);
            }
            $path = $newPath;
        }

        return $path;
    }

    /**
     * @param array<string, string> $options
     */
    private function buildImgproxyFilter(array $options): string
    {
        $filter = '';
        unset($options['self'], $options['attributes']);

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

    private function buildImgproxyUrl(string $path, string $filters): string
    {
        return \sprintf('%1$s/insecure/%2$s%3$s', $this->imgproxyUrl, $filters, $this->encode('local:///'.ltrim(str_replace($GLOBALS['YASSG_BASEDIR'], '', $path), '/')));
    }
}
