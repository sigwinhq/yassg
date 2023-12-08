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

namespace Sigwin\YASSG\Decoder;

use Psr\Cache\CacheItemPoolInterface;
use Sigwin\YASSG\FileDecoder;

final readonly class CachingFileDecoder implements FileDecoder
{
    public function __construct(private FileDecoder $decoder, private CacheItemPoolInterface $cachePoolItem) {}

    public function supports(\SplFileInfo $file): bool
    {
        return $this->decoder->supports($file);
    }

    public function decode(\SplFileInfo $file): array
    {
        /** @var string $path */
        $path = $file->getRealPath();

        /** @var string $content */
        $content = file_get_contents($path);

        $key = md5($content);

        $item = $this->cachePoolItem->getItem($key);
        if ($item->isHit()) {
            /** @var array<string, string> $value */
            $value = $item->get();

            return $value;
        }

        $value = $this->decoder->decode($file);
        $item->set($value);
        $this->cachePoolItem->save($item);

        return $value;
    }
}
