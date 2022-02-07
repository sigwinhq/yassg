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

/**
 * @internal
 */
trait FileDecoderTrait
{
    public function supports(\SplFileInfo $file): bool
    {
        return \in_array(mb_strtolower($file->getExtension()), self::EXTENSIONS, true);
    }
}
