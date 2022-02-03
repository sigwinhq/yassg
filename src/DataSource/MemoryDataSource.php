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

namespace Sigwin\YASSG\DataSource;

use Sigwin\YASSG\DataSource;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MemoryDataSource implements DataSource
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function count(): int
    {
        return \count($this->values);
    }

    public function get(string $id): array
    {
        if ( ! isset($this->values[$id])) {
            // TODO: better error messages
            throw new \RuntimeException(sprintf('Unable to find item by ID "%1$s"', $id));
        }

        return $this->values[$id];
    }

    public static function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['values']);
        $resolver->setAllowedTypes('values', ['array']);

        return $resolver->resolve($options);
    }

    public static function getType(): string
    {
        return 'memory';
    }
}
