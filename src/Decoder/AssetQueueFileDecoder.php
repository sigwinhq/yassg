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

use Sigwin\YASSG\Asset\AssetCopy;
use Sigwin\YASSG\Asset\AssetFetch;
use Sigwin\YASSG\AssetQueue;
use Sigwin\YASSG\FileDecoder;

final readonly class AssetQueueFileDecoder implements FileDecoder
{
    public function __construct(private FileDecoder $decoder, private AssetQueue $queue)
    {
    }

    public function supports(\SplFileInfo $file): bool
    {
        return $this->decoder->supports($file);
    }

    public function decode(\SplFileInfo $file): array
    {
        /** @var array{"__assets"?: list<AssetCopy|AssetFetch>} $decoded */
        $decoded = $this->decoder->decode($file);

        if (isset($decoded['__assets'])) {
            foreach ($decoded['__assets'] as $asset) {
                $this->queue->add($asset);
            }
        }

        return $decoded;
    }
}
