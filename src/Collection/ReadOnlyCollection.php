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

namespace Sigwin\YASSG\Collection;

use Sigwin\YASSG\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Traversable;

/**
 * @template-implements Collection<string, object>
 */
final class ReadOnlyCollection implements Collection
{
    private ExpressionLanguage $expressionLanguage;
    private array $names;
    private array $data;
    private int $total;

    public function __construct(ExpressionLanguage $expressionLanguage, array $names, array $data, ?int $total = null)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->names = $names;
        $this->data = $data;
        $this->total = $total ?? \count($data);
    }

    public function __get(string $name): object
    {
        return $this->data[$name];
    }

    public function total(): int
    {
        return $this->total;
    }

    public function column(string $name): array
    {
        $expression = $this->expressionLanguage->parse($name, $this->names);

        $values = [];
        foreach ($this->data as $id => $item) {
            $values[$id] = $this->expressionLanguage->evaluate($expression, (array) $item);
        }

        return $values;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): object
    {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Read-only collection');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('Read-only collection');
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->data);
    }
}
