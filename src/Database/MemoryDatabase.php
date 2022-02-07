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

use Sigwin\YASSG\Database;
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

    public function find(?string $condition = null, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): array
    {
        $storage = [];
        $this->load($condition, static function (string $id, array|object $item) use (&$storage): void {
            $storage[$id] = $item;
        });

        // sort files here
        if ($sort !== null) {
            $sortExpressions = [];
            foreach (array_keys($sort) as $key) {
                $sortExpressions[$key] = $this->expressionLanguage->parse($key, $this->names);
            }

            uasort($storage, function (array|object $itemA, array|object $itemB) use ($sort, $sortExpressions): int {
                foreach ($sort as $key => $direction) {
                    // TODO: hack to cast to array
                    $itemAValue = $this->expressionLanguage->evaluate($sortExpressions[$key], (array) $itemA);
                    $itemBValue = $this->expressionLanguage->evaluate($sortExpressions[$key], (array) $itemB);

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

        return $storage;
    }

    private function load(?string $condition, callable $callable): void
    {
        $conditionExpression = null;
        if ($condition !== null) {
            $conditionExpression = $this->expressionLanguage->parse($condition, $this->names);
        }

        foreach ($this->storage->load() as $id => $item) {
            if ($item === null) {
                continue;
            }
            if ($conditionExpression === null || $this->expressionLanguage->evaluate($conditionExpression, $item) !== false) {
                $callable($id, $item);
            }
        }
    }
}
