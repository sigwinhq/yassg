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

use League\CommonMark\ConverterInterface;
use Sigwin\YASSG\FileDecoder;

final class MarkdownFileDecoder implements FileDecoder
{
    use FileDecoderTrait;

    private const EXTENSIONS = ['md', 'markdown'];

    private ConverterInterface $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function decode(\SplFileInfo $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new \RuntimeException('Invalid file path');
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('Failed to read file');
        }

        return [
            'slug' => 'abc',
            'body' => $this->converter->convert($content)->getContent(),
        ];
    }
}
