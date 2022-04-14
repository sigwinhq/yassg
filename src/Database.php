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

/**
 * @template T of object
 */
interface Database
{
    public function count(?string $condition = null): int;

    public function countBy(array $condition): int;

    /**
     * @return Collection<string, T>
     */
    public function findAll(?string $condition = null, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection;

    /**
     * @return Collection<string, T>
     */
    public function findAllBy(array $condition, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection;

    /**
     * @return T
     */
    public function findOne(?string $condition = null, ?array $sort = null, ?string $select = null): object;

    /**
     * @return T
     */
    public function findOneBy(array $condition, ?array $sort = null, ?string $select = null): object;

    /**
     * @return null|T
     */
    public function findOneOrNull(?string $condition = null, ?array $sort = null, ?string $select = null): ?object;

    /**
     * @return null|T
     */
    public function findOneByOrNull(array $condition, ?array $sort = null, ?string $select = null): ?object;

    /**
     * @return T
     */
    public function get(string $id): object;

    public function has(string $id): bool;
}
