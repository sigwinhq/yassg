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

    public function __construct(ExpressionLanguage $expressionLanguage, array $names, array $data)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->names = $names;
        $this->data = $data;
    }

    public function __get(string $name): array
    {
        return $this->map($name);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->names[$offset]);
    }

    /** @phpstan-ignore-next-line */
    public function offsetGet(mixed $offset): array
    {
        return $this->map($offset);
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

    private function map(string $name): array
    {
        $expression = $this->expressionLanguage->parse($name, $this->names);

        $values = [];
        foreach ($this->data as $id => $item) {
            $values[$id] = $this->expressionLanguage->evaluate($expression, (array) $item);
        }

        return $values;
    }
}
