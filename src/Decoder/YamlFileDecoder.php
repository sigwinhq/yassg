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

use Sigwin\YASSG\FileDecoder;
use Symfony\Component\Yaml\Yaml;

final class YamlFileDecoder implements FileDecoder
{
    use FileDecoderTrait;

    private const EXTENSIONS = ['yml', 'yaml'];

    public function decode(\SplFileInfo $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new \RuntimeException('Invalid file path');
        }

        $data = Yaml::parseFile($path);
        if (\is_array($data) === false) {
            throw new \RuntimeException('Invalid data decoded');
        }

        return $data;
    }
}
