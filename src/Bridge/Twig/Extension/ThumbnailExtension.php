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
    public function __construct(private RequestStack $requestStack, private ?string $imgproxyUrl, private readonly Packages $packages, private readonly AssetQueue $thumbnailQueue) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_thumbnail', function (array $context, string $path, array $options = []): string {
                if (str_starts_with($path, './')) {
                    if (! isset($context['_path'])) {
                        if (isset($options['self'])) {
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

                    $newPath = realpath(\dirname($context['_path']).'/'.$path);
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

                    $filter .= 'rs:fill';
                    if (isset($options['width'])) {
                        $filter .= ':'.(string) $options['width'];

                        if (isset($options['height'])) {
                            $filter .= ':'.(string) $options['height'];
                        }
                    }
                    $filter .= '/';
                }
                $relative = str_replace($GLOBALS['YASSG_BASEDIR'], '', $path);

                $request = $this->requestStack->getCurrentRequest();
                if ($request === null) {
                    throw new \RuntimeException('Cannot use yassg_thumbnail without a request');
                }
                $build = (bool) $request->attributes->get('yassg_build', false);
                $url = sprintf('%1$s/insecure/%2$s%3$s', $this->imgproxyUrl, $filter, $this->encode('local:///'.ltrim($relative, '/')));
                if (! $build) {
                    return $url;
                }

                $path = \dirname($relative).'/'.md5(md5_file($path).$filter);
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
