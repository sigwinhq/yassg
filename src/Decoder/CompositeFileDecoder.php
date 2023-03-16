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

final class CompositeFileDecoder implements FileDecoder
{
    /** @var iterable<FileDecoder> */
    private iterable $decoders;

    public function __construct(iterable $decoders)
    {
        $this->decoders = $decoders;
    }

    public function supports(\SplFileInfo $file): bool
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supports($file)) {
                return true;
            }
        }

        return false;
    }

    public function decode(\SplFileInfo $file): array
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->supports($file)) {
                return $decoder->decode($file);
            }
        }

        throw new \LogicException('No decoder supports this file');
    }
}
