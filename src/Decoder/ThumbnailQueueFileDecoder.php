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

use Sigwin\YASSG\FileDecoder;
use Sigwin\YASSG\ThumbnailQueue;

/**
 * @psalm-import-type TThumbnailOptions from \Sigwin\YASSG\ThumbnailQueue
 */
final readonly class ThumbnailQueueFileDecoder implements FileDecoder
{
    public function __construct(private FileDecoder $decoder, private ThumbnailQueue $thumbnailQueue) {}

    public function supports(\SplFileInfo $file): bool
    {
        return $this->decoder->supports($file);
    }

    public function decode(\SplFileInfo $file): array
    {
        /** @var array{"@thumbnails"?: list<TThumbnailOptions>} $decoded */
        $decoded = $this->decoder->decode($file);

        if (isset($decoded['@thumbnails'])) {
            foreach ($decoded['@thumbnails'] as $thumbnail) {
                $this->thumbnailQueue->add($thumbnail);
            }
        }
        unset($decoded['@thumbnails']);

        return $decoded;
    }
}
