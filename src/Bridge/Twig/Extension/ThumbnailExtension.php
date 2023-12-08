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

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ThumbnailExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_thumbnail', static function (array $context, string $path): string {
                if (str_starts_with($path, './')) {
                    $newPath = realpath(\dirname($context['_path']).'/'.$path);
                    if ($newPath === false) {
                        throw new \RuntimeException(sprintf('Invalid thumbnail path '.$path));
                    }
                    $path = $newPath;
                }

                return str_replace($GLOBALS['YASSG_BASEDIR'], '', $path);
            }, ['needs_context' => true]),
        ];
    }
}
