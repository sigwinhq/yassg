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
use Sigwin\YASSG\Database;
use Sigwin\YASSG\Exception\MoreThanOneResultException;
use Sigwin\YASSG\Exception\NoResultException;
use Sigwin\YASSG\Storage;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class MemoryDatabase implements Database
{
    private Storage $storage;
    private ExpressionLanguage $expressionLanguage;
    private array $names;

    /**
     * @param array<string> $names
     */
    public function __construct(Storage $storage, ExpressionLanguage $expressionLanguage, array $names)
    {
        $this->storage = $storage;
        $this->expressionLanguage = $expressionLanguage;
        $this->names = $names;
    }

    public function count(?string $condition = null): int
    {
        $total = 0;
        $this->load($condition, static function () use (&$total): void {
            ++$total;
        });

        return $total;
    }

    public function countBy(array $condition): int
    {
        return $this->count($this->conditionArrayToString($condition));
    }

    public function findAll(?string $condition = null, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection
    {
        $storage = [];
        $this->load($condition, static function (string $id, array|object $item) use (&$storage): void {
            $storage[$id] = $item;
        });

        // sort files here
        if ($sort !== null) {
            $sortExpressions = [];
            foreach (array_keys($sort) as $key) {
                $sortExpressions[$key] = $this->expressionLanguage->parse($key, ['item']);
            }

            uasort($storage, function (array|object $itemA, array|object $itemB) use ($sort, $sortExpressions): int {
                foreach ($sort as $key => $direction) {
                    $itemAValue = $this->expressionLanguage->evaluate($sortExpressions[$key], ['item' => $itemA]);
                    $itemBValue = $this->expressionLanguage->evaluate($sortExpressions[$key], ['item' => $itemB]);

                    // TODO: compare values not just like this
                    // maybe strings, locale, etc?
                    $itemValuesComparison = $itemAValue <=> $itemBValue;
                    if ($itemValuesComparison !== 0) {
                        return $direction === 'asc' ? $itemValuesComparison : -$itemValuesComparison;
                    }
                }

                return 0;
            });
        }

        $storage = \array_slice($storage, $offset, $limit, true);
        if ($select !== null) {
            $storage = array_combine(array_keys($storage), array_column($storage, $select));
        }

        return new Collection\ReadOnlyCollection($this->expressionLanguage, $this->names, $storage);
    }

    public function findAllBy(array $condition, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection
    {
        return $this->findAll($this->conditionArrayToString($condition), $sort, $limit, $offset, $select);
    }

    public function findOne(?string $condition = null, ?array $sort = null, ?string $select = null): object
    {
        $result = $this->findOneOrNull($condition, $sort, $select);

        if ($result === null) {
            throw new NoResultException();
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

    private function load(?string $condition, callable $callable): void
    {
        $conditionExpression = null;
        if ($condition !== null) {
            $conditionExpression = $this->expressionLanguage->parse($condition, ['item']);
        }

        foreach ($this->storage->load() as $id => $item) {
            if ($item === null) {
                continue;
            }
            if ($conditionExpression === null || $this->expressionLanguage->evaluate($conditionExpression, ['item' => $item]) !== false) {
                $callable($id, $item);
            }
        }
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
}
