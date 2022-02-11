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

    public function countBy(array $condition): int;

    public function findAll(?string $condition = null, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection;

    public function findAllBy(array $condition, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection;

    public function findOne(?string $condition = null, ?array $sort = null, ?string $select = null): object;

    public function findOneBy(array $condition, ?array $sort = null, ?string $select = null): object;

    public function findOneOrNull(?string $condition = null, ?array $sort = null, ?string $select = null): ?object;

    public function findOneByOrNull(array $condition, ?array $sort = null, ?string $select = null): ?object;
}
