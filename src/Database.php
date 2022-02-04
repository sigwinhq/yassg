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

interface Database
{
    public function count(?string $condition = null): int;

    public function find(?string $condition = null, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): array;
}
