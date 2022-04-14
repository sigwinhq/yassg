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

namespace Sigwin\YASSG\Database;

use Sigwin\YASSG\Collection;
use Sigwin\YASSG\Exception\MoreThanOneResultException;
use Sigwin\YASSG\Exception\NoResultException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @internal
 *
 * @template T of object
 */
trait DatabaseTrait
{
    private ExpressionLanguage $expressionLanguage;
    private array $names;

    public function countBy(array $condition): int
    {
        return $this->count($this->conditionArrayToString($condition));
    }

    /**
     * @return Collection<string, T>
     */
    public function findAllBy(array $condition, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection
    {
        return $this->findAll($this->conditionArrayToString($condition), $sort, $limit, $offset, $select);
    }

    public function findOne(?string $condition = null, ?array $sort = null, ?string $select = null): object
    {
        $result = $this->findOneOrNull($condition, $sort, $select);

        if ($result === null) {
            throw new NoResultException($condition);
        }

        return $result;
    }

    public function findOneOrNull(?string $condition = null, ?array $sort = null, ?string $select = null): ?object
    {
        return $this->oneOrFail($this->findAll($condition, $sort, null, 0, $select));
    }

    public function findOneBy(array $condition, ?array $sort = null, ?string $select = null): object
    {
        return $this->findOne($this->conditionArrayToString($condition), $sort, $select);
    }

    public function findOneByOrNull(array $condition, ?array $sort = null, ?string $select = null): ?object
    {
        return $this->findOneOrNull($this->conditionArrayToString($condition), $sort, $select);
    }

    private function oneOrFail(Collection $list): ?object
    {
        $count = \count($list);
        if ($count <= 0) {
            return null;
        }
        if ($count > 1) {
            throw MoreThanOneResultException::newSelf($count);
        }

        $item = current(iterator_to_array($list));

        return $item !== false ? $item : null;
    }

    private function conditionArrayToString(array $condition): ?string
    {
        if ($condition === []) {
            return null;
        }

        array_walk($condition, static function (mixed &$value, string $key): void {
            $value = sprintf('%1$s == "%2$s"', $key, $value);
        });

        return implode(' AND ', $condition);
    }

    private function createCollection(array $storage): Collection
    {
        return new Collection\ReadOnlyCollection($this->expressionLanguage, $this->names, $storage);
    }
}
