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

namespace Sigwin\YASSG;

interface DataSource
{
    public function count(): int;

    public function get(string $id): array;

    public static function resolveOptions(array $options): array;

    public static function getType(): string;
}
