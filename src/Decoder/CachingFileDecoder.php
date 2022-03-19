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

namespace Sigwin\YASSG\Decoder;

use Psr\Cache\CacheItemPoolInterface;
use Sigwin\YASSG\FileDecoder;

final class CachingFileDecoder implements FileDecoder
{
    private FileDecoder $decoder;
    private CacheItemPoolInterface $cachePoolItem;

    public function __construct(FileDecoder $decoder, CacheItemPoolInterface $cacheItemPool)
    {
        $this->decoder = $decoder;
        $this->cachePoolItem = $cacheItemPool;
    }

    public function supports(\SplFileInfo $file): bool
    {
        return $this->decoder->supports($file);
    }

    public function decode(\SplFileInfo $file): array
    {
        /** @var string $path */
        $path = $file->getRealPath();

        $key = md5($path);

        $item = $this->cachePoolItem->getItem($key);
        if ($item->isHit()) {
            /** @var array $value */
            $value = $item->get();

            return $value;
        }

        $value = $this->decoder->decode($file);
        $item->set($value);
        $this->cachePoolItem->save($item);

        return $value;
    }
}
