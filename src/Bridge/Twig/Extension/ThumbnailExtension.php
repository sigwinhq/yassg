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
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ThumbnailExtension extends AbstractExtension
{
    public function __construct(private readonly RequestStack $requestStack, private readonly ?string $imgproxyUrl, private readonly Packages $packages, private readonly AssetQueue $thumbnailQueue)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_thumbnail', /** @param array<string, string>|array{self: object} $options */ function (array $context, string $path, array $options = []): string {
                if (str_starts_with($path, './')) {
                    if (! isset($context['_path'])) {
                        if (isset($options['self'])) {
                            if (! \is_object($options['self']) || ! property_exists($options['self'], '__path')) {
                                throw new \RuntimeException('Cannot use yassg_thumbnail() with {self: object} as the second argument, pass {self: object} as the second argument');
                            }
                            $context['_path'] = $options['self']->__path;
                        } else {
                            $candidates = [];
                            foreach ($context as $item) {
                                if (\is_object($item) && property_exists($item, '__path')) {
                                    $candidates[] = $item;
                                }
                            }
                            if (\count($candidates) !== 1) {
                                throw new \RuntimeException('Cannot use yassg_thumbnail() without a single Locatable object in context, pass {self: object} as the second argument');
                            }
                            $context['_path'] = $candidates[0]->__path;
                        }
                    }

                    $newPath = realpath(\dirname((string) $context['_path']).'/'.$path);
                    if ($newPath === false) {
                        throw new \RuntimeException('Invalid thumbnail path '.$path);
                    }
                    $path = $newPath;
                }
                unset($options['self']);

                $filter = '';
                if ($options !== []) {
                    if ($this->imgproxyUrl === null) {
                        throw new \RuntimeException('Cannot use yassg_thumbnail options without sigwin_yassg.imgproxy_url configured');
                    }

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
                $relative = str_replace($GLOBALS['YASSG_BASEDIR'], '', $path);

                $request = $this->requestStack->getCurrentRequest();
                if ($request === null) {
                    $build = true;
                } else {
                    $build = (bool) $request->attributes->get('yassg_build', false);
                }
                $url = \sprintf('%1$s/insecure/%2$s%3$s', $this->imgproxyUrl, $filter, $this->encode('local:///'.ltrim($relative, '/')));
                if (! $build) {
                    return $url;
                }

                $path = \sprintf('%1$s/%2$s.%3$s.webp', \dirname($relative), pathinfo($relative, \PATHINFO_FILENAME), mb_substr(md5(md5_file($path).$filter), 0, 8));
                $this->thumbnailQueue->add(new AssetFetch($url, $path));

                return $this->packages->getUrl(ltrim($path, '/'));
            }, ['needs_context' => true]),
        ];
    }

    /**
     * @psalm-pure
     */
    private function encode(string $payload): string
    {
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }
}
