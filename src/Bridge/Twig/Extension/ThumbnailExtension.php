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

use Sigwin\YASSG\ThumbnailQueue;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ThumbnailExtension extends AbstractExtension
{
    public function __construct(private ?string $imgproxyUrl, private readonly Packages $packages, private readonly ThumbnailQueue $thumbnailQueue) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_thumbnail', function (array $context, string $path, array $options = []): string {
                if (str_starts_with($path, './')) {
                    $newPath = realpath(\dirname($context['_path']).'/'.$path);
                    if ($newPath === false) {
                        throw new \RuntimeException('Invalid thumbnail path '.$path);
                    }
                    $path = $newPath;
                }

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
                $url = sprintf('%1$s/insecure/%2$s%3$s', $this->imgproxyUrl, $filter, $this->encode('local:///'.ltrim($relative, '/')));

                /*
                $this->thumbnailQueue->add([
                    'source' => $url,
                    'destination' => $this->packages->getUrl(ltrim($relative, '/')),
                ]);
                */

                return $url;

                return $this->packages->getUrl(ltrim($relative, '/'));
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
